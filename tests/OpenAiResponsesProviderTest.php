<?php

namespace App\Tests;

use App\Entity\ChatConversation;
use App\Service\Chat\Ai\OpenAiResponsesProvider;
use App\Service\Chat\AiConsultantContentProvider;
use App\Service\Chat\ChatQualificationService;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class OpenAiResponsesProviderTest extends TestCase
{
    public function testResponseWithRawControlCharactersStillBuildsDecision(): void
    {
        $output = '{"reply":"Ligne 1
Ligne 2","request_lead":false,"qualification":{"primary_need":"amoa_erp","urgency_level":null,"maturity_level":"cadre","organization_type":null,"organization_size":null,"commercial_intent":"quote_request","potential_value":"medium"},"missing_fields":[],"commercial_progression":{"main_intent":"proposal","project_maturity":"cadre","known_elements":[],"missing_elements":[],"qualification_level":"qualified","recommended_next_action":"open_lead_form","rationale":null},"confidence":0.9}';
        $provider = new OpenAiResponsesProvider(
            new MockHttpClient([new MockResponse(json_encode([
                'output_text' => $output,
                'usage' => ['input_tokens' => 10, 'output_tokens' => 20],
            ], JSON_THROW_ON_ERROR))], 'https://api.openai.com/v1'),
            new ChatQualificationService(),
            new NullLogger(),
            new AiConsultantContentProvider(dirname(__DIR__)),
            'auto',
            'test-key',
            'https://api.openai.com/v1',
            'gpt-5.6-sol',
        );

        $decision = $provider->generateDecision(new ChatConversation(), 'Projet ERP', [], []);

        self::assertSame("Ligne 1\nLigne 2", $decision->reply);
        self::assertSame('openai', $decision->provider);
        self::assertSame('gpt-5.6-sol', $decision->model);
        self::assertSame('amoa_erp', $decision->qualification['primary_need']);
    }
}
