<?php

namespace App\Controller;

use App\Entity\Messagerie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;

class ContactController extends AbstractController
{
    #[Route('/send-email', name: 'send_mail', methods: ['POST'])]
    public function sendEmail(Request $request, EntityManagerInterface $entityManager)
    {
        // Récupérer les données du formulaire
        $firstName = $request->request->get('contactFirstName', '');
        $lastName = $request->request->get('contactLastName', '');
        $company = $request->request->get('contactCompany', '');
        $workEmail = $request->request->get('contactWorkEmail', '');
        $details = $request->request->get('contactDetails', '');

        // Créer l'entité Messagerie
        $message = new Messagerie();
        $message->setFirstName($firstName);
        $message->setLastName($lastName);
        $message->setCompany($company);
        $message->setWorkEmail($workEmail);
        $message->setDetails($details);

        // Enregistrer le message dans la base de données
        $entityManager->persist($message);
        $entityManager->flush();

        // Retourner une réponse JSON
        return new JsonResponse([
            'success' => true,
            'message' => 'Votre message a été envoyé avec succès.',
        ]);
    }
}
