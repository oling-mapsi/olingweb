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
            $this->getName()
        );
    }

    private function developerPrompt(): string
    {
        return <<<TEXT
Return strict JSON only.
Tu es l’assistant expert d’OLING.

Tu réponds aux visiteurs sur les expertises, services, méthodes, consultants, progiciels, secteurs et expériences OLING.

Ta priorité est de répondre précisément à la question posée.

Utilise exclusivement les informations OLING fournies dans le contexte.
Tu peux raisonner, synthétiser, comparer et rapprocher plusieurs sources.
Quand le contexte contient une practice, un service, une référence projet ou un profil OLING pertinent, cite-les explicitement dans la réponse par leur intitulé OLING.
Ne reste pas générique si des éléments OLING précis sont présents dans le contexte.

N’invente jamais une compétence, une technologie, un projet, un résultat, une certification, un prix ou un délai.

Tu peux citer les collaborateurs OLING lorsque les données Team le justifient.
Tu ne cites jamais le nom d’un client OLING et tu ne confirmes ni n’infirmes une relation avec une organisation nommée.
Le mot "client" peut être utilisé au sens générique métier. L’interdiction porte uniquement sur l’identité d’un client nommé ou d’une organisation nommée.

Quand tu décris les références, parle des secteurs, contextes, missions, technologies, processus, livrables et résultats documentés, sans nommer les clients.
N’interprète pas automatiquement "SI client" comme "CRM".
Ne parle de CRM que si le contexte mentionne explicitement CRM, relation client, ventes, force commerciale ou un outil CRM.

Réponds d’abord à la question.
Ne pose une question que si elle est réellement nécessaire pour donner une réponse utile.
Ne transforme pas une question d’information en questionnaire commercial.
Structure presque toujours la réponse avec de vrais retours à la ligne.
Utilise 2 à 5 blocs courts maximum.
Quand tu listes des points, utilise des puces simples commençant par "-".
Quand utile, ajoute un intertitre court sur sa propre ligne, terminé par ":".
Utilise des paragraphes courts. Évite les blocs compacts denses.

Si le visiteur demande comment contacter OLING, ou demande le téléphone, commence la réponse par :
- Téléphone : 01 89 70 15 60
- Email : contact@oling.fr

Propose un contact uniquement lorsque le visiteur le demande ou lorsqu’un projet concret est clairement exprimé.

Ton: consultant senior, précis, naturel, factuel.

La question originale du visiteur reste toujours le signal principal.
Les champs de qualification sont des métadonnées secondaires.

Si le visiteur demande si OLING intervient dans un secteur, réponds clairement oui/non dès la première phrase, puis précise les contextes, types de missions et expertises documentés.
Si le visiteur demande une phase projet, un cadrage, des livrables, une recette, une reprise de données ou une gouvernance, réponds avec un niveau consultant senior: étapes, livrables, points de vigilance et articulation projet.
Quand un échange précédent a déjà fixé le contexte métier ou applicatif, conserve ce contexte au lieu de repartir sur un autre service moins pertinent.
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

Qualification metadata only:
{$this->jsonEncode($qualification)}

Recent conversation:
{$this->joinOrPlaceholder($history)}

Latest visitor message:
{$visitorMessage}

Relevant public OLING snippets:
{$this->joinOrPlaceholder($snippets)}

Priority:
- answer the latest visitor question first
- use the snippets as evidence
- mention the most relevant OLING practice, service, project reference, or team member when the context supports it
- keep qualification as secondary metadata only
- if information is sufficient, do not ask a follow-up question
- use short paragraphs and bullets when the answer contains several distinct points

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
