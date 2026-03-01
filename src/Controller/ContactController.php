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

class ContactController extends AbstractController
{
    #[Route('/send-email', name: 'send_mail', methods: ['POST'])]
    public function sendEmail(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, MailerInterface $mailer)
    {
        // Récupérer les données du formulaire
        $firstName = $request->request->get('contactFirstName', '');
        $lastName = $request->request->get('contactLastName', '');
        $company = $request->request->get('contactCompany', '');
        $workEmail = $request->request->get('contactWorkEmail', '');
        $details = $request->request->get('contactDetails', '');
        $consent = $request->request->get('consent', '');

        // Initialisation des messages d'erreur
        $errors = [];

        // Vérification du consentement
        if (empty($consent)) {
            $errors[] = 'Votre consentement est nécessaire.';
        }

        // Vérification des champs
        if (empty($firstName) || empty($lastName) || empty($workEmail) || empty($details)) {
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
                        "Message:\n{$details}\n"
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
}
