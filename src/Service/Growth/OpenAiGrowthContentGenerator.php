<?php

namespace App\Service\Growth;

use App\Entity\GrowthCampaign;
use App\Entity\GrowthContent;
use App\Enum\GrowthContentStatus;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\RedirectionException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAiGrowthContentGenerator implements GrowthContentGeneratorInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly GrowthSlugger $slugger,
        private readonly LoggerInterface $logger,
        private readonly ?string $apiKey,
        private readonly string $baseUrl,
        private readonly string $model,
    ) {
    }

    public function generate(GrowthCampaign $campaign): GrowthContent
    {
        if (trim((string) $this->apiKey) === '') {
            throw new \RuntimeException('OPENAI_API_KEY is not configured.');
        }

        try {
            $response = $this->httpClient->request('POST', rtrim($this->baseUrl, '/').'/responses', [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $this->model,
                    'input' => [
                        ['role' => 'developer', 'content' => [['type' => 'input_text', 'text' => $this->systemPrompt()]]],
                        ['role' => 'user', 'content' => [['type' => 'input_text', 'text' => 'Sujet: '.$campaign->getTitle()]]],
                    ],
                    'text' => [
                        'format' => [
                            'type' => 'json_schema',
                            'name' => 'growth_content',
                            'strict' => true,
                            'schema' => $this->schema(),
                        ],
                    ],
                    'max_output_tokens' => 1400,
                ],
                'timeout' => 30,
            ]);

            $payload = $response->toArray();
            $text = $this->extractOutputText($payload);
            $decoded = json_decode($this->sanitizeJsonPayload($text), true, 512, JSON_THROW_ON_ERROR);
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
            $this->logger->warning('growth_content_generation_failed', ['error' => $exception->getMessage()]);
            throw new \RuntimeException('Generation failed.', 0, $exception);
        }

        foreach (['title', 'excerpt', 'content_html', 'meta_title', 'meta_description'] as $field) {
            if (!is_string($decoded[$field] ?? null) || trim((string) $decoded[$field]) === '') {
                throw new \RuntimeException('Generation returned an incomplete payload.');
            }
        }

        $title = trim((string) $decoded['title']);

        return (new GrowthContent())
            ->setCampaign($campaign)
            ->setStatus(GrowthContentStatus::GENERATED)
            ->setTitle($title)
            ->setSlug($this->slugger->slug((string) ($decoded['slug'] ?? $title)))
            ->setExcerpt((string) $decoded['excerpt'])
            ->setContentHtml((string) $decoded['content_html'])
            ->setMetaTitle((string) $decoded['meta_title'])
            ->setMetaDescription((string) $decoded['meta_description'])
            ->setCategories($this->stringList($decoded['categories'] ?? []))
            ->setTags($this->stringList($decoded['tags'] ?? []))
            ->setAuthorDisplayName((string) ($decoded['author_display_name'] ?? 'Growth Factory'));
    }

    private function systemPrompt(): string
    {
        return 'Tu produis un brouillon editorial B2B pour Oling. Retourne uniquement le JSON conforme. Ne publie rien. Les faits produit doivent rester prudents et sourcables.';
    }

    private function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['title', 'slug', 'excerpt', 'content_html', 'meta_title', 'meta_description', 'categories', 'tags', 'author_display_name'],
            'properties' => [
                'title' => ['type' => 'string'],
                'slug' => ['type' => 'string'],
                'excerpt' => ['type' => 'string'],
                'content_html' => ['type' => 'string'],
                'meta_title' => ['type' => 'string'],
                'meta_description' => ['type' => 'string'],
                'categories' => ['type' => 'array', 'items' => ['type' => 'string']],
                'tags' => ['type' => 'array', 'items' => ['type' => 'string']],
                'author_display_name' => ['type' => 'string'],
            ],
        ];
    }

    private function extractOutputText(array $payload): string
    {
        if (isset($payload['output_text']) && is_string($payload['output_text'])) {
            return $payload['output_text'];
        }
        foreach (($payload['output'] ?? []) as $item) {
            foreach (($item['content'] ?? []) as $content) {
                if (is_array($content) && is_string($content['text'] ?? null)) {
                    return $content['text'];
                }
            }
        }
        throw new \RuntimeException('Empty OpenAI output.');
    }

    private function sanitizeJsonPayload(string $payload): string
    {
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $payload) ?? $payload;
    }

    private function stringList(mixed $value): array
    {
        return is_array($value) ? array_values(array_filter($value, 'is_string')) : [];
    }
}
