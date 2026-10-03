<?php

namespace App\Controller;

use App\Repository\ErpQuestionnaireSubmissionRepository;
use App\Service\ErpQuestionnaire\ErpQuestionnaireMailer;
use App\Service\ErpQuestionnaire\ErpQuestionnaireContentProvider;
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
    #[Route('/en/erp-software/questionnaire', name: 'erp_questionnaire_en', methods: ['GET', 'POST'], priority: 100)]
    #[Route('/es/software-erp/cuestionario', name: 'erp_questionnaire_es', methods: ['GET', 'POST'], priority: 100)]
    public function questionnaire(
        Request $request,
        EntityManagerInterface $entityManager,
        ErpQuestionnairePayloadMapper $payloadMapper,
        ErpQuestionnaireContentProvider $contentProvider,
        ErpQuestionnaireSummaryService $summaryService,
        ErpQuestionnaireRateLimitGuard $rateLimitGuard,
        ErpQuestionnaireMailer $mailer
    ): Response {
        $locale = $this->resolveLocale($request);
        $request->setLocale($locale);
        $errors = [];
        $values = [];

        if ($request->isMethod('POST')) {
            $values = $request->request->all();
            if (!$rateLimitGuard->isAccepted($request)) {
                $errors[] = $contentProvider->text('validation.rate_limit', $locale);

                return $this->render('erp_questionnaire/form.html.twig', [
                    'values' => $values,
                    'errors' => $errors,
                    'functionalOptions' => $payloadMapper->functionalOptions($locale),
                    'erpContent' => $contentProvider->content($locale),
                ], new Response(status: Response::HTTP_TOO_MANY_REQUESTS));
            }

            $errors = $payloadMapper->validate($values, (string) $request->request->get('_token'), $locale);

            if ($errors === []) {
                $answers = $payloadMapper->answers($values);
                $summary = $summaryService->build($answers, $locale);
                $submission = $payloadMapper->submission($answers, $summary, $locale)
                    ->setScoring($summaryService->scoring($answers, $summary, $locale));
                $entityManager->persist($submission);
                $mailer->sendProspectAndInternal($submission);
                $submission->setEmailedAt(new \DateTimeImmutable());
                $entityManager->flush();

                return $this->render('erp_questionnaire/result.html.twig', [
                    'submission' => $submission,
                    'summary' => $submission->getSummary(),
                    'erpContent' => $contentProvider->content($submission->getLocale()),
                ]);
            }
        }

        return $this->render('erp_questionnaire/form.html.twig', [
            'values' => $values,
            'errors' => $errors,
            'functionalOptions' => $payloadMapper->functionalOptions($locale),
            'erpContent' => $contentProvider->content($locale),
        ]);
    }

    #[Route('/erp-progiciel/questionnaire/{token}/pdf', name: 'erp_questionnaire_pdf', methods: ['GET'])]
    public function downloadPdf(
        string $token,
        ErpQuestionnaireSubmissionRepository $repository,
        ErpQuestionnairePdfGenerator $pdfGenerator,
        ErpQuestionnaireContentProvider $contentProvider
    ): Response {
        $submission = $repository->findOneByPublicToken($token);
        if (!$submission) {
            throw $this->createNotFoundException($contentProvider->text('validation.not_found'));
        }
        $requestLocale = $submission->getLocale();

        return new Response($pdfGenerator->generate($submission), Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$pdfGenerator->filename($submission).'"',
            'Content-Language' => $requestLocale,
        ]);
    }

    private function resolveLocale(Request $request): string
    {
        return match ($request->attributes->get('_route')) {
            'erp_questionnaire_en' => 'en',
            'erp_questionnaire_es' => 'es',
            default => 'fr',
        };
    }

}
