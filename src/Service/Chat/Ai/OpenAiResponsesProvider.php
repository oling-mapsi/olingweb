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
                                'text' => $this->developerPrompt($conversation->getLocale() ?: AiConsultantContentProvider::LOCALE),
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
                'timeout' => 35,
                'max_duration' => 35,
            ]);

            $headers = $response->getHeaders(false);
            if ($response->getStatusCode() >= 400) {
                throw new \RuntimeException('OpenAI API returned HTTP '.$response->getStatusCode().'.');
            }

            $payload = $this->decodeJsonPayload($response->getContent(false), 'OpenAI API response');
            $output = $this->extractOutputText($payload);
            $decoded = $this->decodeStructuredOutput($output);
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
            $this->logger->warning('OpenAI chat provider failed.', [
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

    private function developerPrompt(string $locale): string
    {
        return $this->contentProvider->prompt('chat.system', $locale);
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
            'required' => ['reply', 'request_lead', 'qualification', 'missing_fields', 'commercial_progression', 'confidence'],
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
                'commercial_progression' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => ['main_intent', 'project_maturity', 'known_elements', 'missing_elements', 'qualification_level', 'recommended_next_action', 'rationale'],
                    'properties' => [
                        'main_intent' => ['type' => ['string', 'null']],
                        'project_maturity' => ['type' => ['string', 'null']],
                        'known_elements' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'missing_elements' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'qualification_level' => ['type' => ['string', 'null']],
                        'recommended_next_action' => ['type' => 'string', 'enum' => ['continue_conversation', 'start_diagnostic', 'generate_scoping_note', 'open_lead_form']],
                        'rationale' => ['type' => ['string', 'null']],
                    ],
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
        if (function_exists('mb_convert_encoding')) {
            $payload = mb_convert_encoding($payload, 'UTF-8', 'UTF-8');
        }

        $sanitized = '';
        $inString = false;
        $escaped = false;
        $length = strlen($payload);

        for ($index = 0; $index < $length; ++$index) {
            $char = $payload[$index];
            $ord = ord($char);

            if ($escaped) {
                $sanitized .= $char;
                $escaped = false;
                continue;
            }

            if ($char === '\\') {
                $sanitized .= $char;
                $escaped = true;
                continue;
            }

            if ($char === '"') {
                $sanitized .= $char;
                $inString = !$inString;
                continue;
            }

            if ($ord < 32 || $ord === 127) {
                if ($inString) {
                    $sanitized .= match ($char) {
                        "\n", "\r" => '\\n',
                        "\t" => '\\t',
                        default => ' ',
                    };
                } else {
                    $sanitized .= ' ';
                }
                continue;
            }

            $sanitized .= $char;
        }

        $unicodeSanitized = @preg_replace('/[\p{Cc}\p{Zl}\p{Zp}]+/u', ' ', $sanitized);

        return is_string($unicodeSanitized) ? $unicodeSanitized : $sanitized;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonPayload(string $payload, string $label): array
    {
        try {
            $decoded = json_decode($this->sanitizeJsonPayload($payload), true, 512, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\JsonException $exception) {
            throw new \RuntimeException($label.' JSON decode failed: '.$exception->getMessage(), 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new \RuntimeException($label.' JSON decode failed: unexpected payload type.');
        }

        return $decoded;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeStructuredOutput(string $payload): array
    {
        try {
            return $this->decodeJsonPayload($payload, 'OpenAI structured output');
        } catch (\RuntimeException $exception) {
            $reply = $this->extractReplyFallback($payload);
            if ($reply === null) {
                throw $exception;
            }

            return [
                'reply' => $reply,
                'request_lead' => str_contains($payload, '"request_lead":true') || str_contains($payload, '"request_lead": true'),
                'qualification' => [],
                'missing_fields' => [],
                'confidence' => null,
            ];
        }
    }

    private function extractReplyFallback(string $payload): ?string
    {
        if (function_exists('mb_convert_encoding')) {
            $payload = mb_convert_encoding($payload, 'UTF-8', 'UTF-8');
        }

        $replyKey = strpos($payload, '"reply"');
        $leadKey = strpos($payload, '"request_lead"', $replyKey === false ? 0 : $replyKey);
        if ($replyKey === false || $leadKey === false) {
            return null;
        }

        $firstQuote = strpos($payload, '"', (int) strpos($payload, ':', $replyKey) + 1);
        if ($firstQuote === false || $firstQuote >= $leadKey) {
            return null;
        }

        $raw = substr($payload, $firstQuote + 1, $leadKey - $firstQuote - 1);
        $raw = preg_replace('/",\s*$/', '', trim($raw)) ?? $raw;
        $raw = str_replace(['\\"', '\\n', '\\r', '\\t'], ['"', "\n", "\n", ' '], $raw);

        $reply = trim($raw, " \t\n\r\0\x0B\",");

        return $reply === '' ? null : $reply;
    }
}
