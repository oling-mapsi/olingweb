<?php

namespace App\Tests;

use App\Entity\GrowthCampaign;
use App\Enum\GrowthContentStatus;
use App\Service\Growth\GrowthSlugger;
use App\Service\Growth\OpenAiGrowthContentGenerator;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class GrowthGenerationTest extends TestCase
{
    public function testOpenAiGenerationBuildsDraftContent(): void
    {
        $body = json_encode(['output_text' => json_encode([
            'title' => 'Piloter la conformité',
            'slug' => 'piloter-la-conformite',
            'excerpt' => '<p>Extrait</p>',
            'content_html' => '<p>Contenu</p>',
            'meta_title' => 'Meta title',
            'meta_description' => 'Meta description',
            'categories' => ['GRC'],
            'tags' => ['pilotage'],
            'author_display_name' => 'Growth Factory',
        ], JSON_THROW_ON_ERROR)], JSON_THROW_ON_ERROR);

        $generator = new OpenAiGrowthContentGenerator(
            new MockHttpClient(new MockResponse($body, ['http_code' => 200])),
            new GrowthSlugger(),
            new NullLogger(),
            'key',
            'https://api.openai.test/v1',
            'gpt-test'
        );

        $content = $generator->generate((new GrowthCampaign())->setTitle('Sujet'));

        self::assertSame(GrowthContentStatus::GENERATED, $content->getStatus());
        self::assertSame('piloter-la-conformite', $content->getSlug());
    }

    public function testOpenAiGenerationReportsApiError(): void
    {
        $generator = new OpenAiGrowthContentGenerator(
            new MockHttpClient(new MockResponse('{"error":"rate limit"}', ['http_code' => 429])),
            new GrowthSlugger(),
            new NullLogger(),
            'key',
            'https://api.openai.test/v1',
            'gpt-test'
        );

        $this->expectException(\RuntimeException::class);
        $generator->generate((new GrowthCampaign())->setTitle('Sujet'));
    }
}
