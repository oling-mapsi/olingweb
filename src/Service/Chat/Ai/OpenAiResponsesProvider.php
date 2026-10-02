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

class OpenAiResponsesProvider implements AiProviderInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ChatQualificationService $qualificationService,
        private readonly LoggerInterface $logger,
        private readonly AiConsultantContentProvider $contentProvider,
        private readonly string $mode,
        private readonly ?string $apiKey,
        private readonly string $baseUrl,
        private readonly string $model,
    ) {
    }

    public function getName(): string
    {
        return 'openai';
    }

    public function isAvailable(): bool
    {
        if ($this->mode === 'heuristic') {
            return false;
        }

        return trim((string) $this->apiKey) !== '';
    }

    public function generateDecision(
        ChatConversation $conversation,
        string $visitorMessage,
        array $documents,
        array $qualification
    ): AiDecision {
        if (!$this->isAvailable()) {
            throw new \RuntimeException('OpenAI provider unavailable.');
        }

        try {
            $response = $this->httpClient->request('POST', rtrim($this->baseUrl, '/').'/responses', [
                'headers' => [
                    'Authorization' => 'Bearer '.(string) $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'input' => [
                        [
                            'role' => 'developer',
                            'content' => [[
                                'type' => 'input_text',
                                'text' => $this->developerPrompt(),
                            ]],
                        ],
                        [
                            'role' => 'user',
                            'content' => [[
                                'type' => 'input_text',
                                'text' => $this->userPrompt($conversation, $visitorMessage, $documents, $qualification),
                            ]],
                        ],
                    ],
                    'text' => [
                        'format' => [
                            'type' => 'json_schema',
                            'name' => 'chat_response',
                            'strict' => true,
                            'schema' => $this->responseSchema(),
                        ],
                    ],
                    'max_output_tokens' => 800,
                ],
                'timeout' => 20,
            ]);

            $headers = $response->getHeaders(false);
            $payload = $response->toArray();
            $output = $this->extractOutputText($payload);
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
            $this->logger->warning('OpenAI chat provider failed, falling back to heuristic provider.', [
                'error' => $exception->getMessage(),
            ]);

            throw new \RuntimeException('OpenAI provider failed.', 0, $exception);
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
            isset($payload['usage']['input_tokens']) ? (int) $payload['usage']['input_tokens'] : null,
            isset($payload['usage']['output_tokens']) ? (int) $payload['usage']['output_tokens'] : null,
            $headers['x-request-id'][0] ?? null
        );
    }

    private function developerPrompt(): string
    {
        return $this->contentProvider->prompt('chat.system');
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     * @param array<string, string|null> $qualification
     */
    private function userPrompt(
        ChatConversation $conversation,
        string $visitorMessage,
        array $documents,
        array $qualification
    ): string {
        $history = [];
        foreach (array_slice($conversation->getMessages()->toArray(), -8) as $message) {
            $history[] = sprintf('%s: %s', $message->getRole(), $message->getContent());
        }

        $snippets = [];
        foreach ($documents as $document) {
            $snippets[] = sprintf(
                '- [%s] %s | %s | %s',
                $document['type'],
                $document['title'],
                $document['url'],
                mb_substr($document['text'], 0, 320)
            );
        }

        return $this->contentProvider->renderPrompt('chat.user', [
            'sourceUrl' => (string) $conversation->getSourceUrl(),
            'sourcePath' => (string) $conversation->getSourcePath(),
            'qualification' => $this->jsonEncode($qualification),
            'history' => $this->joinOrPlaceholder($history),
            'visitorMessage' => $visitorMessage,
            'snippets' => $this->joinOrPlaceholder($snippets),
        ], $conversation->getLocale() ?: AiConsultantContentProvider::LOCALE);
    }

    /**
     * @return array<string, mixed>
     */
    private function responseSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['reply', 'request_lead', 'qualification', 'missing_fields', 'confidence'],
            'properties' => [
                'reply' => ['type' => 'string'],
                'request_lead' => ['type' => 'boolean'],
                'qualification' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => [
                        'primary_need',
                        'urgency_level',
                        'maturity_level',
                        'organization_type',
                        'organization_size',
                        'commercial_intent',
                        'potential_value',
                    ],
                    'properties' => [
                        'primary_need' => ['type' => ['string', 'null']],
                        'urgency_level' => ['type' => ['string', 'null']],
                        'maturity_level' => ['type' => ['string', 'null']],
                        'organization_type' => ['type' => ['string', 'null']],
                        'organization_size' => ['type' => ['string', 'null']],
                        'commercial_intent' => ['type' => ['string', 'null']],
                        'potential_value' => ['type' => ['string', 'null']],
                    ],
                ],
                'missing_fields' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'confidence' => ['type' => ['number', 'null']],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractOutputText(array $payload): string
    {
        if (!empty($payload['output_text']) && is_string($payload['output_text'])) {
            return $payload['output_text'];
        }

        if (!empty($payload['output']) && is_array($payload['output'])) {
            foreach ($payload['output'] as $item) {
                if (!is_array($item) || empty($item['content']) || !is_array($item['content'])) {
                    continue;
                }

                foreach ($item['content'] as $content) {
                    if (is_array($content) && isset($content['text']) && is_string($content['text'])) {
                        return $content['text'];
                    }
                }
            }
        }

        throw new \RuntimeException('No textual output returned by OpenAI Responses API.');
    }

    private function normalizedString(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * @param list<string> $lines
     */
    private function joinOrPlaceholder(array $lines): string
    {
        return $lines === [] ? '- none' : implode("\n", $lines);
    }

    /**
     * @param array<string, string|null> $payload
     */
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
