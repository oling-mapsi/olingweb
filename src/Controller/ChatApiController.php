<?php

namespace App\Controller;

use App\Entity\ChatConversation;
use App\Service\Chat\ChatConversationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/chat', name: 'api_chat_')]
class ChatApiController extends AbstractController
{
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
    public function postMessage(string $token, Request $request, ChatConversationManager $conversationManager): JsonResponse
    {
        $this->assertCsrf($request);
        $conversation = $this->requireConversation($token, $conversationManager);
        $payload = $this->decodePayload($request);
        $content = trim((string) ($payload['content'] ?? ''));

        if ($content === '') {
            return $this->json(['success' => false, 'message' => 'Le message est vide.'], Response::HTTP_BAD_REQUEST);
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
    public function submitLead(string $token, Request $request, ChatConversationManager $conversationManager): JsonResponse
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
            'message' => 'Merci, votre demande a bien été envoyée. Un consultant OLING reviendra vers vous rapidement. Vous pouvez aussi continuer ici et poser une autre question.',
            'qualification' => $qualification,
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
            throw $this->createNotFoundException('Conversation introuvable.');
        }

        return $conversation;
    }

    private function assertCsrf(Request $request): void
    {
        $token = (string) $request->headers->get('X-CSRF-TOKEN', '');
        if (!$this->isCsrfTokenValid('chat_widget', $token)) {
            throw $this->createAccessDeniedException('Jeton invalide.');
        }
    }
}
