<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\JsonResponse;

class ContactController extends AbstractController
{
    #[Route('/send-email', name: 'send_mail', methods: ['POST'])]
    public function sendEmail(Request $request, MailerInterface $mailer)
    {
        // Récupérer le contenu de la requête sous forme de chaîne de caractères
        $content = $request->getContent();

        // Convertir la chaîne de requête en tableau associatif
        parse_str($content, $data);

        // Récupérer les données du formulaire
        $firstName = $data['contactFirstName'] ?? '';
        $lastName = $data['contactLastName'] ?? '';
        $company = $data['contactCompany'] ?? '';
        $workEmail = $data['contactWorkEmail'] ?? '';
        $details = $data['contactDetails'] ?? '';

        // Créer le message
        $email = (new Email())
            ->from('robot@oling-fr.mon.world')
            ->to('florestan.rouet@oling.fr')
            ->subject('Nouveau message de ' . $firstName . ' ' . $lastName)
            ->html('
                <p>Vous avez reçu un nouveau message :</p>
                <ul>
                    <li>Prénom : ' . $firstName . '</li>
                    <li>Nom : ' . $lastName . '</li>
                    <li>Société : ' . $company . '</li>
                    <li>Email professionnel : ' . $workEmail . '</li>
                    <li>Détails : ' . $details . '</li>
                </ul>
            ');

        // Envoyer le message
        try {
            $sent = $mailer->send($email);
            if (!$sent) {
                throw new \Exception($firstName.' Le message n\'a pas été envoyé.');
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $firstName.' Une erreur s\'est produite lors de l\'envoi du message.',
                'error' => $e->getMessage(),
            ]);
        }

        // Retourner une réponse JSON
        return new JsonResponse([
            'success' => true,
            'message' => 'Votre message a été envoyé avec succès.',
        ]);
    }
}
