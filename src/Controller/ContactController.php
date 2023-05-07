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
use App\Middleware\XRobotsTagMiddleware;


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

        // Vérifier si les champs sont remplis
        if (!empty($firstName) && !empty($lastName) && !empty($workEmail) && !empty($details)) {

            // Vérifier si le champ workEmail est un email valide
            if (filter_var($workEmail, FILTER_VALIDATE_EMAIL)) {

                // Vérifier si le champ details fait entre 30 et 200 caractères
                if (strlen($details) >= 30 && strlen($details) <= 200) {
                    // Créer l'entité Messagerie
                    $message = new Messagerie();
                    $message->setFirstName($firstName);
                    $message->setLastName($lastName);
                    $message->setCompany($company);
                    $message->setWorkEmail($workEmail);
                    $message->setDetails($details);

                    // Vérifier si l'entité Messagerie est valide
                    $errors = $validator->validate($message);
                    if (count($errors) === 0) {

                        // Enregistrer le message dans la base de données
                        $entityManager->persist($message);
                        $entityManager->flush();

                        // Retourner une réponse JSON de succès
                        return new JsonResponse([
                            'success' => true,
                            'message' => 'Votre message a été envoyé avec succès.',
                        ]);
                    }
                }
            }
        }

        // Si une des conditions n'est pas remplie, renvoyer une réponse JSON d'échec
        return new JsonResponse([
            'success' => false,
            'message' => 'Veuillez remplir tous les champs et vérifier que les données sont valides.',
        ]);
    }
}
