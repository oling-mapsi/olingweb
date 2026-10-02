<?php

namespace App\Service\I18n;

use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\RedirectionException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OpenAiContentTranslationProvider implements AiTranslationProviderInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ?string $apiKey,
        private readonly string $baseUrl,
        private readonly string $model,
    ) {
    }

    public function translate(array $sourcePayload, string $targetLocale, array $glossary): AiTranslationResult
    {
        if (trim((string) $this->apiKey) === '') {
            throw new \RuntimeException('OpenAI API key is not configured.');
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
                                'text' => json_encode([
                                    'targetLocale' => $targetLocale,
                                    'sourcePayload' => $sourcePayload,
                                    'glossary' => $glossary,
                                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                            ]],
                        ],
                    ],
                    'text' => [
                        'format' => [
                            'type' => 'json_object',
                        ],
                    ],
                    'max_output_tokens' => 4000,
                ],
                'timeout' => 60,
            ]);

            $decoded = json_decode($this->extractOutputText($response->toArray()), true, 512, JSON_THROW_ON_ERROR);
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
            throw new \RuntimeException('AI translation provider failed: '.$this->formatProviderError($exception), 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new \RuntimeException('AI translation provider returned an invalid payload.');
        }

        return new AiTranslationResult($decoded, 'openai', $this->model);
    }

    private function developerPrompt(): string
    {
        return <<<'PROMPT'
Return strict JSON only.
You localize OLING French source content into the requested target locale.
Use semantic localization, not literal word-for-word translation.
Preserve factual claims, brand names, structure, HTML tags, links, placeholders, route names, codes, emails, phone numbers, image paths, schema IDs, numeric values and technical identifiers.
Do not translate JSON keys. Translate only linguistic values.
Generate a localized SEO-friendly slug for the target locale.
Use international English for en and international Spanish for es.
For French business terms, use understandable international terminology. Do not force literal translations of AMOA, DSI, DPO, RFE or similar acronyms.
Never mark content as reviewed or published.
PROMPT;
    }

    private function formatProviderError(\Throwable $exception): string
    {
        if ($exception instanceof HttpExceptionInterface) {
            $body = $exception->getResponse()->getContent(false);
            if ($body !== '') {
                return $body;
            }
        }

        return $exception->getMessage();
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractOutputText(array $payload): string
    {
        if (isset($payload['output_text']) && is_string($payload['output_text'])) {
            return $payload['output_text'];
        }

        foreach (($payload['output'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }
            foreach (($item['content'] ?? []) as $content) {
                if (is_array($content) && isset($content['text']) && is_string($content['text'])) {
                    return $content['text'];
                }
            }
        }

        throw new \RuntimeException('No textual output returned by OpenAI Responses API.');
    }
}
