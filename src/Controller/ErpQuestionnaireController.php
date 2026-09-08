<?php

namespace App\Controller;

use App\Repository\ErpQuestionnaireSubmissionRepository;
use App\Service\ErpQuestionnaire\ErpQuestionnaireMailer;
use App\Service\ErpQuestionnaire\ErpQuestionnairePayloadMapper;
use App\Service\ErpQuestionnaire\ErpQuestionnairePdfGenerator;
use App\Service\ErpQuestionnaire\ErpQuestionnaireRateLimitGuard;
use App\Service\ErpQuestionnaire\ErpQuestionnaireSummaryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ErpQuestionnaireController extends AbstractController
{
    #[Route('/erp-progiciel/questionnaire', name: 'erp_questionnaire', methods: ['GET', 'POST'])]
    public function questionnaire(
        Request $request,
        EntityManagerInterface $entityManager,
        ErpQuestionnairePayloadMapper $payloadMapper,
        ErpQuestionnaireSummaryService $summaryService,
        ErpQuestionnaireRateLimitGuard $rateLimitGuard,
        ErpQuestionnaireMailer $mailer
    ): Response {
        $errors = [];
        $values = [];

        if ($request->isMethod('POST')) {
            $values = $request->request->all();
            if (!$rateLimitGuard->isAccepted($request)) {
                $errors[] = 'Trop de soumissions ont été envoyées depuis cette connexion. Merci de réessayer dans quelques minutes.';

                return $this->render('erp_questionnaire/form.html.twig', [
                    'values' => $values,
                    'errors' => $errors,
                    'functionalOptions' => $payloadMapper->functionalOptions(),
                ], new Response(status: Response::HTTP_TOO_MANY_REQUESTS));
            }

            $errors = $payloadMapper->validate($values, (string) $request->request->get('_token'));

            if ($errors === []) {
                $answers = $payloadMapper->answers($values);
                $summary = $summaryService->build($answers);
                $submission = $payloadMapper->submission($answers, $summary)
                    ->setScoring($summaryService->scoring($answers, $summary));
                $entityManager->persist($submission);
                $mailer->sendProspectAndInternal($submission);
                $submission->setEmailedAt(new \DateTimeImmutable());
                $entityManager->flush();

                return $this->render('erp_questionnaire/result.html.twig', [
                    'submission' => $submission,
                    'summary' => $submission->getSummary(),
                ]);
            }
        }

        return $this->render('erp_questionnaire/form.html.twig', [
            'values' => $values,
            'errors' => $errors,
            'functionalOptions' => $payloadMapper->functionalOptions(),
        ]);
    }

    #[Route('/erp-progiciel/questionnaire/{token}/pdf', name: 'erp_questionnaire_pdf', methods: ['GET'])]
    public function downloadPdf(
        string $token,
        ErpQuestionnaireSubmissionRepository $repository,
        ErpQuestionnairePdfGenerator $pdfGenerator
    ): Response {
        $submission = $repository->findOneByPublicToken($token);
        if (!$submission) {
            throw $this->createNotFoundException('Questionnaire introuvable.');
        }

        return new Response($pdfGenerator->generate($submission), Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$pdfGenerator->filename($submission).'"',
        ]);
    }

}
