<?php

namespace App\Service\Chat\Ai;

use App\Entity\ChatConversation;
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
                    'max_output_tokens' => 420,
                ],
                'timeout' => 20,
            ]);

            $payload = $response->toArray();
            $output = $this->extractOutputText($payload);
            $decoded = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
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
            $this->getName()
        );
    }

    private function developerPrompt(): string
    {
        return <<<TEXT
Return strict JSON only.
You are the OLING commercial qualification assistant for a B2B consulting website.
Rules:
- Stay strictly within public OLING website scope.
- Never invent offers, clients, guarantees, deadlines, budgets, certifications, or legal conclusions.
- Keep the reply premium, sober, short, and useful.
- Use a consultant tone and briefly reformulate the need when useful.
- If information is missing, ask exactly one focused qualification question.
- Never ask for lead details in the body of the answer.
- If the visitor explicitly asks to be contacted, to receive a proposal, or asks for support/accompagnement, set request_lead to true and do not ask another question.
- Do not push contact in every answer.
- Mention at most 1 or 2 useful public resources, only if they are truly relevant.
- Use only the allowed taxonomy values.
TEXT;
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

        return <<<TEXT
Produce a valid json object that matches the schema.

Current page: {$conversation->getSourceUrl()}
Current source path: {$conversation->getSourcePath()}

Known deterministic qualification:
{$this->jsonEncode($qualification)}

Recent conversation:
{$this->joinOrPlaceholder($history)}

Latest visitor message:
{$visitorMessage}

Relevant public OLING snippets:
{$this->joinOrPlaceholder($snippets)}

Allowed taxonomy:
- primary_need: transformation_si, amoa_erp, organisation_gouvernance, conformite, rgpd, cybersecurite, ia_data_automatisation, autre
- urgency_level: immediate, short_term, planned, exploratory
- maturity_level: flou, cadre, consultation, en_cours, bloque
- organization_type: pme, pmi, eti, public, association, autre
- organization_size: 1_49, 50_249, 250_999, 1000_plus, unknown
- commercial_intent: diagnostic, cadrage, assistance_projet, audit, mise_en_conformite, expertise_ponctuelle, orientation
- potential_value: low, medium, high
TEXT;
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
                        'primary_need' => ['type' => 'string'],
                        'urgency_level' => ['type' => 'string'],
                        'maturity_level' => ['type' => 'string'],
                        'organization_type' => ['type' => 'string'],
                        'organization_size' => ['type' => 'string'],
                        'commercial_intent' => ['type' => 'string'],
                        'potential_value' => ['type' => 'string'],
                    ],
                ],
                'missing_fields' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'confidence' => ['type' => 'number'],
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
}
