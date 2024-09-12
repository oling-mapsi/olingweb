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

class ContactController extends AbstractController
{
    #[Route('/send-email', name: 'send_mail', methods: ['POST'])]
    public function sendEmail(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator)
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

            // Retourner une réponse JSON de succès
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

