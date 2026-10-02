<?php

namespace App\Controller;

use App\Entity\ChatConversation;
use App\Service\Chat\AiConsultantContentProvider;
use App\Service\Chat\ChatConversationManager;
use App\Service\ErpQuestionnaire\ErpQuestionnaireMailer;
use App\Service\ErpQuestionnaire\ErpQuestionnaireContentProvider as ErpQuestionnaireContentProvider;
use App\Service\ErpQuestionnaire\ErpQuestionnairePayloadMapper;
use App\Service\ErpQuestionnaire\ErpQuestionnaireRateLimitGuard;
use App\Service\ErpQuestionnaire\ErpQuestionnaireSummaryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/chat', name: 'api_chat_')]
class ChatApiController extends AbstractController
{
    public function __construct(private readonly AiConsultantContentProvider $contentProvider)
    {
    }

    #[Route('/conversations', name: 'conversation_create', methods: ['POST'])]
    public function createConversation(Request $request, ChatConversationManager $conversationManager): JsonResponse
    {
        $this->assertCsrf($request);
        $payload = $this->decodePayload($request);

        $conversation = $conversationManager->createConversation(
            $payload['sourcePath'] ?? null,
            $payload['sourceUrl'] ?? null,
            $payload['referrer'] ?? $request->headers->get('referer'),
            $payload['locale'] ?? $request->getLocale(),
            $request->getClientIp(),
            $request->headers->get('User-Agent')
        );

        return $this->json($conversationManager->serializeConversation($conversation), Response::HTTP_CREATED);
    }

    #[Route('/conversations/{token}', name: 'conversation_show', methods: ['GET'])]
    public function showConversation(string $token, ChatConversationManager $conversationManager): JsonResponse
    {
        $conversation = $this->requireConversation($token, $conversationManager);

        return $this->json($conversationManager->serializeConversation($conversation));
    }

    #[Route('/conversations/{token}/messages', name: 'conversation_message', methods: ['POST'])]
    public function postMessage(string $token, Request $request, ChatConversationManager $conversationManager, AiConsultantContentProvider $contentProvider): JsonResponse
    {
        $this->assertCsrf($request);
        $conversation = $this->requireConversation($token, $conversationManager);
        $payload = $this->decodePayload($request);
        $content = trim((string) ($payload['content'] ?? ''));

        if ($content === '') {
            return $this->json(['success' => false, 'message' => $contentProvider->text('copy.empty_message')], Response::HTTP_BAD_REQUEST);
        }

        $reply = $conversationManager->handleVisitorMessage(
            $conversation,
            $content,
            $payload['sourcePath'] ?? null,
            $payload['sourceUrl'] ?? null
        );

        return $this->json([
            'success' => true,
            'reply' => [
                'content' => $reply->content,
                'requestLead' => $reply->requestLead,
                'sources' => $reply->sources,
            ],
            'conversation' => $conversationManager->serializeConversation($conversation),
        ]);
    }

    #[Route('/conversations/{token}/lead', name: 'conversation_lead', methods: ['POST'])]
    public function submitLead(string $token, Request $request, ChatConversationManager $conversationManager, AiConsultantContentProvider $contentProvider): JsonResponse
    {
        $this->assertCsrf($request);
        $conversation = $this->requireConversation($token, $conversationManager);
        $payload = $this->decodePayload($request);

        try {
            $qualification = $conversationManager->submitLead($conversation, $payload);
        } catch (\InvalidArgumentException $exception) {
            return $this->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'success' => true,
            'message' => $contentProvider->text('copy.lead_api_confirmation', $conversation->getLocale() ?: AiConsultantContentProvider::LOCALE),
            'qualification' => $qualification,
            'conversation' => $conversationManager->serializeConversation($conversation),
        ]);
    }

    #[Route('/conversations/{token}/erp-questionnaire', name: 'conversation_erp_questionnaire', methods: ['POST'])]
    public function submitErpQuestionnaire(
        string $token,
        Request $request,
        ChatConversationManager $conversationManager,
        EntityManagerInterface $entityManager,
        ErpQuestionnairePayloadMapper $payloadMapper,
        ErpQuestionnaireSummaryService $summaryService,
        ErpQuestionnaireRateLimitGuard $rateLimitGuard,
        ErpQuestionnaireMailer $mailer,
        AiConsultantContentProvider $contentProvider,
        ErpQuestionnaireContentProvider $erpContentProvider
    ): JsonResponse {
        $this->assertCsrf($request);
        $conversation = $this->requireConversation($token, $conversationManager);
        $payload = $this->decodePayload($request);

        if (!$rateLimitGuard->isAccepted($request)) {
            return $this->json([
                'success' => false,
                'message' => $erpContentProvider->text('validation.rate_limit', $conversation->getLocale() ?: ErpQuestionnaireContentProvider::LOCALE),
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $errors = $payloadMapper->validatePromptAiPayload($payload);
        if ($errors !== []) {
            return $this->json([
                'success' => false,
                'message' => implode(' ', $errors),
                'errors' => $errors,
            ], Response::HTTP_BAD_REQUEST);
        }

        $answers = $payloadMapper->answers($payload);
        $summary = $summaryService->build($answers);
        $submission = $payloadMapper->submission($answers, $summary)
            ->setScoring($summaryService->scoring($answers, $summary));

        $entityManager->persist($submission);
        $mailer->sendProspectAndInternal($submission);
        $submission->setEmailedAt(new \DateTimeImmutable());
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => $contentProvider->text('copy.erp_api_confirmation', $conversation->getLocale() ?: AiConsultantContentProvider::LOCALE),
            'summary' => $summary,
            'pdfUrl' => $this->generateUrl('erp_questionnaire_pdf', [
                'token' => $submission->getPublicToken(),
            ], UrlGeneratorInterface::ABSOLUTE_PATH),
            'conversation' => $conversationManager->serializeConversation($conversation),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodePayload(Request $request): array
    {
        if ($request->getContentTypeFormat() === 'json') {
            try {
                return $request->toArray();
            } catch (\JsonException) {
                return [];
            }
        }

        return $request->request->all();
    }

    private function requireConversation(string $token, ChatConversationManager $conversationManager): ChatConversation
    {
        $conversation = $conversationManager->findByPublicToken($token);
        if (!$conversation) {
            throw $this->createNotFoundException($this->contentProvider->text('copy.conversation_not_found'));
        }

        return $conversation;
    }

    private function assertCsrf(Request $request): void
    {
        $token = (string) $request->headers->get('X-CSRF-TOKEN', '');
        if (!$this->isCsrfTokenValid('chat_widget', $token)) {
            throw $this->createAccessDeniedException($this->contentProvider->text('copy.invalid_csrf'));
        }
    }
}
