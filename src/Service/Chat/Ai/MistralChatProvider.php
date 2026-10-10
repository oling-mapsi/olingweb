<?php

namespace App\Service\Chat\Ai;

use App\Entity\ChatConversation;
use App\Service\Chat\AiConsultantContentProvider;
use App\Service\Chat\ChatQualificationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\RedirectionException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MistralChatProvider implements AiProviderInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ChatQualificationService $qualificationService,
        private readonly LoggerInterface $logger,
        private readonly AiConsultantContentProvider $contentProvider,
        private readonly ?string $apiKey,
        private readonly string $baseUrl,
        private readonly string $model,
    ) {
    }

    public function getName(): string
    {
        return 'mistral';
    }

    public function isAvailable(): bool
    {
        return trim((string) $this->apiKey) !== '';
    }

    public function generateDecision(ChatConversation $conversation, string $visitorMessage, array $documents, array $qualification): AiDecision
    {
        if (!$this->isAvailable()) {
            throw new \RuntimeException('Mistral provider unavailable.');
        }

        try {
            $response = $this->httpClient->request('POST', rtrim($this->baseUrl, '/').'/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer '.(string) $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $this->contentProvider->prompt('chat.system', $conversation->getLocale() ?: AiConsultantContentProvider::LOCALE)],
                        ['role' => 'user', 'content' => $this->userPrompt($conversation, $visitorMessage, $documents, $qualification)],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'max_tokens' => 800,
                ],
                'timeout' => 12,
            ]);

            $headers = $response->getHeaders(false);
            $payload = $response->toArray();
            $output = (string) ($payload['choices'][0]['message']['content'] ?? '');
            $decoded = json_decode($this->sanitizeJsonPayload($output), true, 512, JSON_THROW_ON_ERROR);
        } catch (
            ClientException|
            DecodingExceptionInterface|
            RedirectionException|
            ServerException|
            TransportException|
            TransportExceptionInterface|
            \JsonException|
            \RuntimeException $exception
        ) {
            $this->logger->warning('Mistral chat provider failed.', [
                'error' => $exception->getMessage(),
            ]);

            throw new \RuntimeException('Mistral provider failed.', 0, $exception);
        }

        $aiQualification = $this->qualificationService->sanitizeQualification([
            'primary_need' => $this->normalizedString($decoded['qualification']['primary_need'] ?? ''),
            'urgency_level' => $this->normalizedString($decoded['qualification']['urgency_level'] ?? ''),
            'maturity_level' => $this->normalizedString($decoded['qualification']['maturity_level'] ?? ''),
            'organization_type' => $this->normalizedString($decoded['qualification']['organization_type'] ?? ''),
            'organization_size' => $this->normalizedString($decoded['qualification']['organization_size'] ?? ''),
            'commercial_intent' => $this->normalizedString($decoded['qualification']['commercial_intent'] ?? ''),
            'potential_value' => $this->normalizedString($decoded['qualification']['potential_value'] ?? ''),
        ]);

        $missingFields = array_values(array_filter(
            $decoded['missing_fields'] ?? [],
            static fn (mixed $field): bool => is_string($field) && $field !== ''
        ));

        return new AiDecision(
            trim((string) ($decoded['reply'] ?? '')),
            (bool) ($decoded['request_lead'] ?? false),
            $aiQualification,
            array_column($documents, 'url'),
            $missingFields,
            isset($decoded['confidence']) ? (float) $decoded['confidence'] : null,
            $this->getName(),
            $this->model,
            isset($payload['usage']['prompt_tokens']) ? (int) $payload['usage']['prompt_tokens'] : null,
            isset($payload['usage']['completion_tokens']) ? (int) $payload['usage']['completion_tokens'] : null,
            $headers['x-request-id'][0] ?? null
        );
    }

    private function userPrompt(ChatConversation $conversation, string $visitorMessage, array $documents, array $qualification): string
    {
        $history = [];
        foreach (array_slice($conversation->getMessages()->toArray(), -8) as $message) {
            $history[] = sprintf('%s: %s', $message->getRole(), $message->getContent());
        }

        $snippets = [];
        foreach ($documents as $document) {
            $snippets[] = sprintf('- [%s] %s | %s | %s', $document['type'], $document['title'], $document['url'], mb_substr($document['text'], 0, 320));
        }

        return $this->contentProvider->renderPrompt('chat.user', [
            'sourceUrl' => (string) $conversation->getSourceUrl(),
            'sourcePath' => (string) $conversation->getSourcePath(),
            'qualification' => $this->jsonEncode($qualification),
            'history' => $history === [] ? '- none' : implode("\n", $history),
            'visitorMessage' => $visitorMessage,
            'snippets' => $snippets === [] ? '- none' : implode("\n", $snippets),
        ], $conversation->getLocale() ?: AiConsultantContentProvider::LOCALE);
    }

    private function normalizedString(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function jsonEncode(array $payload): string
    {
        try {
            return (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return '{}';
        }
    }

    private function sanitizeJsonPayload(string $payload): string
    {
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $payload) ?? $payload;
    }
}
