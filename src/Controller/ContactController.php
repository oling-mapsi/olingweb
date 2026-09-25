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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class ContactController extends AbstractController
{
    #[Route('/send-email', name: 'send_mail', methods: ['POST'])]
    public function sendEmail(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        MailerInterface $mailer,
        CsrfTokenManagerInterface $csrfTokenManager,
        RateLimiterFactory $contactFormLimiter
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

        // Vérifier si l'entité Messagerie est valide
        $validationErrors = $validator->validate($message);
        if (count($validationErrors) === 0) {
            // Enregistrer le message dans la base de données
            $entityManager->persist($message);
            $entityManager->flush();

            // Envoyer un email via Office 365
            try {
                $email = (new Email())
                    ->from('florestan.rouet@oling.fr')
                    ->to('florestan.rouet@oling.fr')
                    ->replyTo($workEmail)
                    ->subject('Nouveau message — Formulaire contact OLING')
                    ->text(
                        "Prénom: {$firstName}\n" .
                        "Nom: {$lastName}\n" .
                        "Société: {$company}\n" .
                        "Email: {$workEmail}\n\n" .
                        "Message:\n{$details}\n\n" .
                        "Attribution de la demande:\n" .
                        implode("\n", array_map(
                            static fn (string $label, string $value): string => "{$label}: ".($value !== '' ? $value : 'non disponible'),
                            array_keys($demand),
                            array_values($demand)
                        )) . "\n"
                    );

                $mailer->send($email);
            } catch (TransportExceptionInterface $e) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Le message a bien été enregistré, mais l’envoi email a échoué. Merci de réessayer.',
                ]);
            }

            return new JsonResponse([
                'success' => true,
                'message' => 'Votre message a été envoyé avec succès.',
            ]);
        } else {
            // Retourner une réponse JSON avec les erreurs de validation
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur s\'est produite lors de la validation du message.',
            ]);
        }
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
