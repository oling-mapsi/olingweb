<?php

namespace App\Controller;

use App\Entity\Messagerie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\ContactNotificationService;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use App\Service\Analytics\AnalyticsConversionAttributionService;

class ContactController extends AbstractController
{
    #[Route('/send-email', name: 'send_mail', methods: ['POST'])]
    public function sendEmail(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        ContactNotificationService $notifications,
        CsrfTokenManagerInterface $csrfTokenManager,
        RateLimiterFactory $contactFormLimiter,
        AnalyticsConversionAttributionService $attributionService,
        \Psr\Log\LoggerInterface $logger
    ): JsonResponse {
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('contact_form', $csrfToken))) {
            return $this->json([
                'success' => false,
                'message' => 'La demande n’a pas pu être validée. Merci de recharger la page et de réessayer.',
            ], Response::HTTP_FORBIDDEN);
        }

        $honeypot = $request->request->get('website', '');
        if (!is_scalar($honeypot) || trim((string) $honeypot) !== '') {
            return $this->json([
                'success' => false,
                'message' => 'La demande n’a pas pu être validée.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $submissionId = $request->request->get('contactSubmissionId', '');
        if (empty($request->request->get('consent', ''))) {
            return new JsonResponse(['success'=>false,'message'=>'Votre consentement est nécessaire.'],400);
        }
        if (!is_string($submissionId) || ($submissionId !== '' && !preg_match('/^[a-f0-9]{32}$/D', $submissionId))) {
            return new JsonResponse(['success' => false, 'message' => 'Identifiant de demande invalide.'], 400);
        }
        $submissionId = $submissionId !== '' ? $submissionId : bin2hex(random_bytes(16));
        $existing = $entityManager->getRepository(Messagerie::class)->findOneBy(['submissionId' => $submissionId]);
        if ($existing instanceof Messagerie) {
            foreach (['contactFirstName'=>'getFirstName', 'contactLastName'=>'getLastName', 'contactCompany'=>'getCompany', 'contactWorkEmail'=>'getWorkEmail', 'contactDetails'=>'getDetails'] as $field=>$getter) {
                $limit = in_array($field, ['contactCompany', 'contactWorkEmail'], true) ? 180 : 100;
                [$clean, $tooLong] = $field === 'contactDetails' ? $this->multiLineValue($request, $field, 200) : $this->singleLineValue($request, $field, $limit);
                if ($tooLong || $clean !== $existing->$getter()) {
                    return new JsonResponse(['success'=>false, 'message'=>'Cette demande ne correspond pas à l’envoi initial.'], 409);
                }
            }
            return $this->receipt($existing, true);
        }

        $clientKey = $request->getClientIp() ?: 'anonymous';
        if (!$contactFormLimiter->create($clientKey)->consume()->isAccepted()) {
            return $this->json([
                'success' => false,
                'message' => 'Trop de demandes ont été envoyées. Merci de réessayer dans quelques minutes.',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        [$firstName, $firstNameTooLong] = $this->singleLineValue($request, 'contactFirstName', 100);
        [$lastName, $lastNameTooLong] = $this->singleLineValue($request, 'contactLastName', 100);
        [$company, $companyTooLong] = $this->singleLineValue($request, 'contactCompany', 180);
        [$workEmail, $workEmailTooLong] = $this->singleLineValue($request, 'contactWorkEmail', 180);
        [$details, $detailsTooLong] = $this->multiLineValue($request, 'contactDetails', 200);
        $consent = $request->request->get('consent', '');
        $demand = [
            'Source' => $this->demandValue($request, 'demandSource', 100),
            'Referrer' => $this->demandValue($request, 'demandReferrer', 500),
            'Landing page' => $this->demandValue($request, 'demandLandingPage', 255),
            'First landing page' => $this->demandValue($request, 'demandFirstLandingPage', 255),
            'CTA source' => $this->demandValue($request, 'demandCtaSource', 100),
            'UTM source' => $this->demandValue($request, 'demandUtmSource', 100),
            'UTM medium' => $this->demandValue($request, 'demandUtmMedium', 100),
            'UTM campaign' => $this->demandValue($request, 'demandUtmCampaign', 150),
        ];

        // Initialisation des messages d'erreur
        $errors = [];

        // Vérification du consentement
        if (empty($consent)) {
            $errors[] = 'Votre consentement est nécessaire.';
        }

        // Vérification des champs
        if ($firstNameTooLong || $lastNameTooLong || $companyTooLong || $workEmailTooLong || $detailsTooLong) {
            $errors[] = 'Un ou plusieurs champs dépassent la longueur autorisée.';
        } elseif (empty($firstName) || empty($lastName) || empty($workEmail) || empty($details)) {
            $errors[] = 'Veuillez remplir tous les champs pour que nous puissions traiter votre demande.';
        } elseif (!filter_var($workEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'email n\'est pas valide.';
        } elseif (strlen($details) < 30 || strlen($details) > 200) {
            $errors[] = 'Le message doit comporter entre 30 et 200 caractères.';
        }

        // Si des erreurs existent, les retourner en réponse JSON
        if (count($errors) > 0) {
            return new JsonResponse([
                'success' => false,
                'message' => implode(' ', $errors),  // On combine tous les messages d'erreur
            ]);
        }

        // Créer l'entité Messagerie si tout est correct
        $message = new Messagerie();
        $message->setFirstName($firstName);
        $message->setLastName($lastName);
        $message->setCompany($company);
        $message->setWorkEmail($workEmail);
        $message->setDetails($details);
        $message->setConsentAt(new \DateTimeImmutable());
        $message->setSubmissionId($submissionId);

        // Vérifier si l'entité Messagerie est valide
        $validationErrors = $validator->validate($message);
        if (count($validationErrors) === 0) {
            // Enregistrer le message dans la base de données
            $entityManager->persist($message);
            try {
                $entityManager->flush();
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                // A concurrent retry won the unique submission key. No second email.
                $saved = $entityManager->getConnection()->fetchAssociative('SELECT submission_id, notification_status, first_name, last_name, company, work_email, details FROM messagerie WHERE submission_id = ?', [$submissionId]);
                if (!$saved) { throw $e; }
                foreach (['first_name'=>$firstName, 'last_name'=>$lastName, 'company'=>$company, 'work_email'=>$workEmail, 'details'=>$details] as $field=>$value) {
                    if ($saved[$field] !== $value) {
                        return new JsonResponse(['success'=>false, 'message'=>'Cette demande ne correspond pas à l’envoi initial.'], 409);
                    }
                }
                return new JsonResponse(['success'=>true, 'persisted'=>true, 'submission_id'=>$saved['submission_id'], 'notification_status'=>$saved['notification_status'], 'replayed'=>true, 'message'=>'Votre demande est déjà enregistrée.']);
            }

            $notifications->notify($message, $demand);

            try {
                $attributionService->attribute(
                'contact',
                (string) $message->getId(),
                $request->request->get('analyticsSessionId'),
                $request->getPathInfo(),
                new \DateTimeImmutable()
                );
            } catch (\Throwable $e) {
                $logger->warning('contact_attribution_failed', ['submission_id'=>$submissionId,'exception_type'=>get_class($e)]);
            }

            return $this->receipt($message);
        } else {
            // Retourner une réponse JSON avec les erreurs de validation
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur s\'est produite lors de la validation du message.',
            ]);
        }
    }

    private function receipt(Messagerie $message, bool $replayed = false): JsonResponse
    {
        return new JsonResponse([
            'success'=>true, 'persisted'=>true, 'submission_id'=>$message->getSubmissionId(),
            'notification_status'=>$message->getNotificationStatus(), 'replayed'=>$replayed,
            'message'=>$message->getNotificationStatus()==='sent' ? 'Votre demande est enregistrée.' : 'Votre demande est enregistrée ; la notification est à vérifier par notre équipe. Il est inutile de la renvoyer.',
        ]);
    }

    private function demandValue(Request $request, string $name, int $maxLength): string
    {
        $rawValue = $request->request->get($name, '');
        if (!is_scalar($rawValue)) {
            return '';
        }

        $value = trim(strip_tags((string) $rawValue));
        $value = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value) ?? '';

        return mb_substr($value, 0, $maxLength);
    }

    /**
     * @return array{0: string, 1: bool}
     */
    private function singleLineValue(Request $request, string $name, int $maxLength): array
    {
        $value = $this->scalarValue($request, $name);
        $value = preg_replace('/[\r\n\t]+/u', ' ', $value) ?? '';
        $value = preg_replace('/\s{2,}/u', ' ', $value) ?? '';
        $value = trim($value);

        return [$value, mb_strlen($value) > $maxLength];
    }

    /**
     * @return array{0: string, 1: bool}
     */
    private function multiLineValue(Request $request, string $name, int $maxLength): array
    {
        $value = trim($this->scalarValue($request, $name));

        return [$value, mb_strlen($value) > $maxLength];
    }

    private function scalarValue(Request $request, string $name): string
    {
        $value = $request->request->get($name, '');
        if (!is_scalar($value)) {
            return '';
        }

        $value = strip_tags((string) $value);

        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
    }
}
