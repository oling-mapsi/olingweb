<?php

namespace App\Tests;

use App\Entity\ChatConversation;
use App\Service\Chat\Ai\MistralChatProvider;
use App\Service\Chat\AiConsultantContentProvider;
use App\Service\Chat\ChatQualificationService;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class MistralChatProviderTest extends TestCase
{
    public function testUnavailableWithoutApiKey(): void
    {
        $provider = $this->provider('', [new MockResponse('{}')]);

        self::assertFalse($provider->isAvailable());
    }

    public function testValidJsonBuildsDecision(): void
    {
        $provider = $this->provider('test-key', [new MockResponse(json_encode([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'reply' => 'Réponse Mistral.',
                        'request_lead' => true,
                        'qualification' => [
                            'primary_need' => 'crm',
                            'urgency_level' => 'short_term',
                            'maturity_level' => 'cadre',
                            'organization_type' => 'eti',
                            'organization_size' => '250_999',
                            'commercial_intent' => 'cadrage',
                            'potential_value' => 'high',
                        ],
                        'missing_fields' => ['email'],
                        'confidence' => 0.8,
                    ]),
                ],
            ]],
            'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 20],
        ], JSON_THROW_ON_ERROR))]);

        $decision = $provider->generateDecision(new ChatConversation(), 'Projet CRM', [], []);

        self::assertSame('Réponse Mistral.', $decision->reply);
        self::assertTrue($decision->requestLead);
        self::assertSame('mistral', $decision->provider);
        self::assertSame('crm', $decision->qualification['primary_need']);
        self::assertSame(['email'], $decision->missingFields);
    }

    public function testInvalidJsonThrows(): void
    {
        $provider = $this->provider('test-key', [new MockResponse(json_encode([
            'choices' => [[
                'message' => ['content' => '{bad json'],
            ]],
        ], JSON_THROW_ON_ERROR))]);

        $this->expectException(\RuntimeException::class);

        $provider->generateDecision(new ChatConversation(), 'Projet CRM', [], []);
    }

    /**
     * @param MockResponse[] $responses
     */
    private function provider(?string $apiKey, array $responses): MistralChatProvider
    {
        return new MistralChatProvider(
            new MockHttpClient($responses, 'https://api.mistral.ai/v1'),
            new ChatQualificationService(),
            new NullLogger(),
            new AiConsultantContentProvider(dirname(__DIR__)),
            $apiKey,
            'https://api.mistral.ai/v1',
            'mistral-large-latest'
        );
    }
}
