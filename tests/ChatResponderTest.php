<?php

namespace App\Tests;

use App\Entity\ChatConversation;
use App\Entity\ChatMessage;
use App\Entity\ChatPublicDocument;
use App\Repository\ChatPublicDocumentRepository;
use App\Service\Chat\Ai\AiDecision;
use App\Service\Chat\Ai\AiProviderInterface;
use App\Service\Chat\AiConsultantContentProvider;
use App\Service\Chat\ChatPublicContentIndexer;
use App\Service\Chat\ChatQualificationService;
use App\Service\Chat\ChatResponder;
use App\Service\Chat\PublicContentCatalog;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ChatResponderTest extends TestCase
{
    public function testGreetingKeepsConversationalOpeningWithoutGenericScopeSentence(): void
    {
        $conversation = new ChatConversation();
        $message = (new ChatMessage())
            ->setRole('visitor')
            ->setContent('Bonjour')
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable());
        $conversation->addMessage($message);

        $reply = $this->buildResponder()->reply($conversation, 'Bonjour');

        self::assertStringStartsWith('Bonjour.', $reply->content);
        self::assertStringNotContainsString('Je reste dans le périmètre public du site OLING.', $reply->content);
        self::assertSame([], $reply->sources);
    }

    public function testDirectContactQuestionReturnsPhoneAndEmailWithoutSources(): void
    {
        $conversation = new ChatConversation();
        $message = (new ChatMessage())
            ->setRole('visitor')
            ->setContent('votre numero de tel')
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable());
        $conversation->addMessage($message);

        $reply = $this->buildResponder()->reply($conversation, 'votre numero de tel');

        self::assertStringContainsString('01 89 70 15 60', $reply->content);
        self::assertStringContainsString('contact@oling.fr', $reply->content);
        self::assertSame([], $reply->sources);
        self::assertFalse($reply->requestLead);
    }

    public function testDirectContactQuestionWithAccentsReturnsPhoneAndEmail(): void
    {
        $conversation = new ChatConversation();
        $message = (new ChatMessage())
            ->setRole('visitor')
            ->setContent('votre numéro de téléphone ?')
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable());
        $conversation->addMessage($message);

        $reply = $this->buildResponder()->reply($conversation, 'votre numéro de téléphone ?');

        self::assertStringContainsString('01 89 70 15 60', $reply->content);
        self::assertStringContainsString('contact@oling.fr', $reply->content);
    }

    public function testRecontactRequestTriggersLeadFlowWithoutSources(): void
    {
        $reply = $this->buildResponderWithDocuments([
            $this->buildDocument('service', '/business-apps/erp', 'AMOA ERP, CRM, GMAO, SI Finance et SIRH'),
            $this->buildDocument('service', '/consulting/reforme-facturation-electronique-amoa', 'AMOA Réforme de la facturation électronique (RFE)'),
        ])->reply(new ChatConversation(), 'je veux que vous me recontactiez');

        self::assertStringContainsString('fiche projet préremplie', $reply->content);
        self::assertSame([], $reply->sources);
        self::assertTrue($reply->requestLead);
        self::assertContains('open_lead_form', array_column($reply->actions, 'type'));
    }

    public function testWelcomeMessageUsesNaturalFormat(): void
    {
        $message = $this->buildResponder()->getWelcomeMessage();

        self::assertStringStartsWith('Bonjour.', $message);
        self::assertStringContainsString('assistant expert OLING', $message);
    }

    public function testAuditQuestionDoesNotTriggerLeadRequest(): void
    {
        $conversation = new ChatConversation();
        $message = (new ChatMessage())
            ->setRole('visitor')
            ->setContent('Faites-vous des audits RGPD ?')
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable());
        $conversation->addMessage($message);

        $reply = $this->buildResponder()->reply($conversation, 'Faites-vous des audits RGPD ?');

        self::assertFalse($reply->requestLead);
        self::assertStringNotContainsString('formulaire', $reply->content);
    }

    public function testNamedClientQuestionReturnsConfidentialityRefusal(): void
    {
        $reply = $this->buildResponder()->reply(new ChatConversation(), 'Avez-vous travaillé avec Société X ?');

        self::assertStringContainsString('Je ne confirme ni ne détaille', $reply->content);
        self::assertSame([], $reply->sources);
    }

    public function testGenericGrandPortQuestionIsNotTreatedAsNamedClient(): void
    {
        $reply = $this->buildResponderWithDocuments([
            $this->buildDocument('reference', '/projets', 'Référence grand port maritime : continuité, PCA et cybersécurité'),
        ])->reply(new ChatConversation(), 'Avez-vous accompagné des grands ports sur la continuité ou la cybersécurité ?');

        self::assertStringNotContainsString('Je ne confirme ni ne détaille', $reply->content);
        self::assertContains('/projets', $reply->sources);
    }

    public function testGenericTrainingOrganizationQuestionIsNotTreatedAsNamedClient(): void
    {
        $reply = $this->buildResponderWithDocuments([
            $this->buildDocument('reference', '/projets', 'Référence organisme de formation : QSE et Qualiopi'),
        ])->reply(new ChatConversation(), 'Avez-vous accompagné un organisme de formation sur un sujet QSE ou Qualiopi ?');

        self::assertStringNotContainsString('Je ne confirme ni ne détaille', $reply->content);
        self::assertContains('/projets', $reply->sources);
    }

    public function testReferenceQuestionDoesNotExposeTeamCardWhenExpertWasNotAsked(): void
    {
        $reply = $this->buildResponderWithDocuments([
            $this->buildDocument('team', '/equipe/jean-claude-vati', 'Jean-Claude Vati Consultant SI Senior'),
            $this->buildDocument('reference', '/projets', 'Référence Eau et assainissement'),
            $this->buildDocument('expertise', '/expertises/amoa-erp', 'AMOA ERP et applicatifs métiers'),
        ])->reply(new ChatConversation(), 'avez vous des références eaux et assainissement');

        self::assertCount(2, $reply->sources);
        self::assertContains('/expertises/amoa-erp', $reply->sources);
        self::assertContains('/projets', $reply->sources);
    }

    public function testExpertQuestionCanReturnTeamCardFirst(): void
    {
        $reply = $this->buildResponderWithDocuments([
            $this->buildDocument('team', '/equipe/jean-claude-vati', 'Jean-Claude Vati Consultant SI Senior'),
            $this->buildDocument('expertise', '/expertises/amoa-erp', 'AMOA ERP et applicatifs métiers'),
        ])->reply(new ChatConversation(), 'quel expert oling pour mon projet erp');

        self::assertSame(['/equipe/jean-claude-vati', '/expertises/amoa-erp'], $reply->sources);
    }

    public function testErpQuestionDoesNotSelectOffTopicSources(): void
    {
        $reply = $this->buildResponderWithDocuments([
            $this->buildDocument('service', '/expertises-audit/controle-de-gestion', 'Contrôle de gestion et évaluation des risques', 'pilotage financier risques cartographie kpis'),
            $this->buildDocument('service', '/expertises-audit/si', 'Sécurité des SI, ISO 27001, DORA et NIS2', 'audit gouvernance securite smsi iso 27001'),
            $this->buildDocument('service', '/business-apps/erp', 'AMOA ERP, CRM, GMAO, SI Finance et SIRH', 'amoa erp progiciels metiers cadrage reprise donnees interfaces recette'),
            $this->buildDocument('expertise', '/practice/business-apps', 'Transformation digitale et progiciels métier', 'erp crm gmao si finance pilotage projet'),
        ])->reply(new ChatConversation(), 'quelle est votre offre erp ?');

        self::assertSame(['/business-apps/erp', '/practice/business-apps'], $reply->sources);
    }

    public function testErpQuestionnaireUsesAmoaQualificationFrame(): void
    {
        $conversation = new ChatConversation();
        $message = (new ChatMessage())
            ->setRole('visitor')
            ->setContent('Je souhaite lancer un questionnaire ERP pour cadrer un progiciel.')
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable());
        $conversation->addMessage($message);

        $reply = $this->buildResponder()->reply($conversation, 'Je souhaite lancer un questionnaire ERP pour cadrer un progiciel.');

        self::assertStringContainsString('modules concernés', $reply->content);
        self::assertStringContainsString('sécurité, RGPD', $reply->content);
        self::assertStringContainsString('livrables AMOA', $reply->content);
        self::assertStringContainsString('macro-planning', $reply->content);
    }

    public function testUnavailableOpenAiProviderDoesNotGenerateCommercialFallback(): void
    {
        $qualificationService = new ChatQualificationService();
        $failingOpenAi = new class implements AiProviderInterface {
            public int $calls = 0;

            public function getName(): string
            {
                return 'openai_responses';
            }

            public function isAvailable(): bool
            {
                return true;
            }

            public function generateDecision(ChatConversation $conversation, string $visitorMessage, array $documents, array $qualification): AiDecision
            {
                ++$this->calls;

                throw new \RuntimeException('OpenAI unavailable');
            }
        };

        $repository = $this->createMock(ChatPublicDocumentRepository::class);
        $repository->method('findActiveDocuments')->willReturn([]);
        $responder = new ChatResponder(
            new PublicContentCatalog($repository, $this->createMock(ChatPublicContentIndexer::class)),
            $qualificationService,
            [$failingOpenAi],
            new NullLogger(),
            $this->contentProvider(),
        );

        $reply = $responder->reply(new ChatConversation(), 'Je veux qualifier un besoin ERP finance avec reprise de données.');

        self::assertSame(2, $failingOpenAi->calls);
        self::assertSame('llm_unavailable', $reply->provider);
        self::assertSame('technical_unavailable', $reply->messageType);
        self::assertStringContainsString('difficulté technique', $reply->content);
        self::assertStringNotContainsString('ERP finance', $reply->content);
        self::assertStringNotContainsString('/contact?chat_fallback=1', $reply->content);
    }

    public function testProposalRequestGetsDedicatedLeadAction(): void
    {
        $message = $this->erpProposalRequestMessage();
        $conversation = new ChatConversation();
        $conversation->addMessage((new ChatMessage())
            ->setRole('visitor')
            ->setContent($message)
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable()));

        $reply = $this->buildResponderWithDocuments($this->commercialLibraryDocuments())->reply($conversation, $message);

        self::assertSame('amoa_erp', $reply->qualification['primary_need']);
        self::assertContains($reply->qualification['commercial_intent'], ['quote_request', 'proposal_request', 'devis']);
        self::assertSame('proposal_request', $reply->messageType);
        self::assertFalse($reply->requestLead);
        self::assertContains('open_lead_form', array_column($reply->actions, 'type'));
        self::assertContains('Recevoir une proposition OLING', array_column($reply->actions, 'label'));
    }

    public function testProposalRequestUnavailableProviderPreservesDemandWithDedicatedAction(): void
    {
        $message = $this->erpProposalRequestMessage();
        $failingOpenAi = new class implements AiProviderInterface {
            public int $calls = 0;

            public function getName(): string { return 'openai_responses'; }
            public function isAvailable(): bool { return true; }
            public function generateDecision(ChatConversation $conversation, string $visitorMessage, array $documents, array $qualification): AiDecision
            {
                ++$this->calls;
                throw new \RuntimeException('OpenAI unavailable');
            }
        };
        $repository = $this->createMock(ChatPublicDocumentRepository::class);
        $repository->method('findActiveDocuments')->willReturn($this->commercialLibraryDocuments());
        $responder = new ChatResponder(
            new PublicContentCatalog($repository, $this->createMock(ChatPublicContentIndexer::class)),
            new ChatQualificationService(),
            [$failingOpenAi],
            new NullLogger(),
            $this->contentProvider(),
        );

        $conversation = new ChatConversation();
        $conversation->addMessage((new ChatMessage())
            ->setRole('visitor')
            ->setContent($message)
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable()));

        $reply = $responder->reply($conversation, $message);

        self::assertSame(2, $failingOpenAi->calls);
        self::assertSame('llm_unavailable', $reply->provider);
        self::assertSame('technical_unavailable', $reply->messageType);
        self::assertStringContainsString('difficulté technique', $reply->content);
        self::assertStringContainsString('demande de mission AMOA ERP', $reply->content);
        self::assertStringContainsString('proposition adaptée', $reply->content);
        self::assertContains('Transmettre ma demande de proposition à OLING', array_column($reply->actions, 'label'));
        self::assertStringNotContainsString('nombre de jours estimés', $reply->content);
        self::assertStringNotContainsString('/contact?chat_fallback=1', $reply->content);
    }

    public function testPrimaryProviderRetryCanRecoverWithoutTechnicalFallback(): void
    {
        $qualificationService = new ChatQualificationService();
        $flakyOpenAi = new class implements AiProviderInterface {
            public int $calls = 0;

            public function getName(): string { return 'openai'; }
            public function isAvailable(): bool { return true; }
            public function generateDecision(ChatConversation $conversation, string $visitorMessage, array $documents, array $qualification): AiDecision
            {
                ++$this->calls;
                if ($this->calls === 1) {
                    throw new \RuntimeException('HTTP 503');
                }

                return new AiDecision('Réponse générative récupérée après nouvelle tentative.', false, $qualification, []);
            }
        };
        $repository = $this->createMock(ChatPublicDocumentRepository::class);
        $repository->method('findActiveDocuments')->willReturn([]);
        $responder = new ChatResponder(
            new PublicContentCatalog($repository, $this->createMock(ChatPublicContentIndexer::class)),
            $qualificationService,
            [$flakyOpenAi],
            new NullLogger(),
            $this->contentProvider(),
        );

        $reply = $responder->reply(new ChatConversation(), 'Nous avons un projet ERP urgent.');

        self::assertSame(2, $flakyOpenAi->calls);
        self::assertSame('openai', $reply->provider);
        self::assertSame('llm_primary', $reply->status);
        self::assertStringContainsString('générative', $reply->content);
    }

    public function testSecondaryProviderCanAnswerWhenPrimaryFails(): void
    {
        $qualificationService = new ChatQualificationService();
        $primary = new class implements AiProviderInterface {
            public function getName(): string { return 'openai'; }
            public function isAvailable(): bool { return true; }
            public function generateDecision(ChatConversation $conversation, string $visitorMessage, array $documents, array $qualification): AiDecision
            {
                throw new \RuntimeException('DNS error');
            }
        };
        $secondary = new class implements AiProviderInterface {
            public function getName(): string { return 'secondary_llm'; }
            public function isAvailable(): bool { return true; }
            public function generateDecision(ChatConversation $conversation, string $visitorMessage, array $documents, array $qualification): AiDecision
            {
                return new AiDecision('Réponse générée par le modèle secondaire configuré.', false, $qualification, []);
            }
        };
        $repository = $this->createMock(ChatPublicDocumentRepository::class);
        $repository->method('findActiveDocuments')->willReturn([]);
        $responder = new ChatResponder(
            new PublicContentCatalog($repository, $this->createMock(ChatPublicContentIndexer::class)),
            $qualificationService,
            [$primary, $secondary],
            new NullLogger(),
            $this->contentProvider(),
        );

        $reply = $responder->reply(new ChatConversation(), 'Nous avons un projet ERP urgent.');

        self::assertSame('secondary_llm', $reply->provider);
        self::assertSame('llm_secondary', $reply->status);
        self::assertStringContainsString('secondaire', $reply->content);
    }

    public function testSectorQuestionCanSelectReferenceAndSectorPage(): void
    {
        $reply = $this->buildResponderWithDocuments([
            $this->buildDocument('page', '/secteurs', 'Secteurs métiers accompagnés par OLING', 'Transports, collectivités, industrie, services, conformité, sécurité et schémas directeurs SI.'),
            $this->buildDocument('reference', '/projets', 'Référence Eau et assainissement', 'Mission AMOA progiciel en eau et assainissement avec cadrage, consultation, reprise de données et déploiement.'),
            $this->buildDocument('service', '/business-apps/erp', 'AMOA ERP, CRM, GMAO, SI Finance et SIRH', 'amoa erp progiciels metiers cadrage reprise donnees interfaces recette'),
        ])->reply(new ChatConversation(), 'avez vous des références dans le transport et l assainissement ?');

        self::assertContains('/projets', $reply->sources);
        self::assertContains('/secteurs', $reply->sources);
    }

    /**
     * @dataProvider linkRecommendationScenarioProvider
     *
     * @param list<string> $allowedUrls
     */
    public function testRecommendedLinksStaySemanticallyRelevant(string $question, array $allowedUrls): void
    {
        $reply = $this->buildResponderWithDocuments($this->commercialLibraryDocuments())
            ->reply(new ChatConversation(), $question);

        self::assertLessThanOrEqual(2, count($reply->sources), $question);
        foreach ($reply->sources as $source) {
            self::assertNotSame('', $source);
            self::assertStringNotContainsString('127.0.0.1', $source);
            self::assertContains($source, $allowedUrls, $question.' -> '.$source);
        }
    }

    /**
     * @dataProvider commercialPathScenarioProvider
     */
    public function testCommercialPathActionsCoverDiagnosticScopingAndConversion(string $message, string $expectedAction): void
    {
        $conversation = new ChatConversation();
        $conversation->addMessage((new ChatMessage())
            ->setRole('visitor')
            ->setContent('Contexte initial : organisation, objectifs, contraintes, calendrier et décision à préparer.')
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable()));

        $reply = $this->buildResponderWithDocuments($this->commercialLibraryDocuments())->reply($conversation, $message);

        $actionTypes = array_column($reply->actions, 'type');
        if ($expectedAction === 'open_lead_form') {
            self::assertContains($reply->messageType, ['lead_request', 'contact_offer', 'contact_info']);
            return;
        }

        self::assertContains($expectedAction, $actionTypes, $message);
    }

    public function testRfeFinanceSequenceKeepsMainNeedAndOffersPdfAndContact(): void
    {
        $conversation = new ChatConversation();
        foreach (['accompagnement amoa SI finance ?', 'un cadrage en amont', 'la rfe', 'salesforce', 'dolibarr'] as $index => $content) {
            $conversation->addMessage((new ChatMessage())
                ->setRole('visitor')
                ->setContent($content)
                ->setMessageType('answer')
                ->setSequenceNumber($index + 1)
                ->setCreatedAt(new \DateTimeImmutable()));
        }

        $reply = $this->buildResponderWithDocuments($this->commercialLibraryDocuments())
            ->reply($conversation, 'dolibarr');

        self::assertSame('si_finance', $reply->qualification['primary_need']);
        self::assertSame('contact_offer', $reply->messageType);
        self::assertFalse($reply->requestLead);
        self::assertContains('generate_scoping_note', array_column($reply->actions, 'type'));
        self::assertContains('open_lead_form', array_column($reply->actions, 'type'));
        self::assertStringContainsString('réforme de la facturation électronique', $reply->content);
        self::assertStringContainsString('Salesforce et Dolibarr', $reply->content);
        self::assertStringContainsString('plateforme agréée', $reply->content);
        self::assertSame('/facturation-electronique-amoa', $reply->sources[0] ?? null);
    }

    /**
     * @dataProvider firstTurnCommercialOpportunityProvider
     */
    public function testFirstTurnCommercialOpportunityShowsContactCta(string $message): void
    {
        $conversation = new ChatConversation();
        $conversation->addMessage((new ChatMessage())
            ->setRole('visitor')
            ->setContent($message)
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable()));

        $reply = $this->buildResponderWithDocuments($this->commercialLibraryDocuments())->reply($conversation, $message);

        self::assertSame('contact_offer', $reply->messageType, $message);
        self::assertFalse($reply->requestLead, $message);
        self::assertContains('open_lead_form', array_column($reply->actions, 'type'), $message);
        self::assertContains('Être recontacté par OLING', array_column($reply->actions, 'label'), $message);
    }

    public function testDoraFirstMessageGetsCommercialCtaWithoutBlockingQuestion(): void
    {
        $message = 'Bonjour, je suis une société de gestion des actifs financiers. Nous devons être conformes à DORA. Avez-vous cette expérience ?';
        $conversation = new ChatConversation();
        $conversation->addMessage((new ChatMessage())
            ->setRole('visitor')
            ->setContent($message)
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable()));

        $reply = $this->buildResponderWithDocuments($this->commercialLibraryDocuments())->reply($conversation, $message);

        self::assertSame('cybersecurite', $reply->qualification['primary_need']);
        self::assertSame('contact_offer', $reply->messageType);
        self::assertFalse($reply->requestLead);
        self::assertContains('open_lead_form', array_column($reply->actions, 'type'));
        self::assertContains('Être recontacté par OLING', array_column($reply->actions, 'label'));
        self::assertStringContainsString('DORA', $reply->content);
    }

    /**
     * @dataProvider firstTurnCommercialCorrectionProvider
     */
    public function testFirstTurnCommercialCorrections(string $message, bool $expectsContact): void
    {
        $conversation = new ChatConversation();
        $conversation->addMessage((new ChatMessage())
            ->setRole('visitor')
            ->setContent($message)
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable()));

        $reply = $this->buildResponderWithDocuments($this->commercialLibraryDocuments())->reply($conversation, $message);
        $actionTypes = array_column($reply->actions, 'type');

        if ($expectsContact) {
            self::assertSame('contact_offer', $reply->messageType, $message);
            self::assertContains('open_lead_form', $actionTypes, $message);
            return;
        }

        self::assertNotSame('contact_offer', $reply->messageType, $message);
        self::assertNotContains('open_lead_form', $actionTypes, $message);
    }

    public function testContactRefusalDoesNotShowCommercialCta(): void
    {
        $message = 'Nous recherchons une AMOA ERP, mais pas de contact maintenant.';
        $conversation = new ChatConversation();
        $conversation->addMessage((new ChatMessage())
            ->setRole('visitor')
            ->setContent($message)
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable()));

        $reply = $this->buildResponderWithDocuments($this->commercialLibraryDocuments())->reply($conversation, $message);

        self::assertNotSame('contact_offer', $reply->messageType);
        self::assertNotContains('open_lead_form', array_column($reply->actions, 'type'));
    }

    public function testExplicitContactAfterRfeSequenceOpensLeadFormImmediately(): void
    {
        $conversation = new ChatConversation();
        foreach (['accompagnement amoa SI finance ?', 'un cadrage en amont', 'la rfe', 'salesforce', 'dolibarr', 'Je veux vous contacter'] as $index => $content) {
            $conversation->addMessage((new ChatMessage())
                ->setRole('visitor')
                ->setContent($content)
                ->setMessageType('answer')
                ->setSequenceNumber($index + 1)
                ->setCreatedAt(new \DateTimeImmutable()));
        }

        $reply = $this->buildResponderWithDocuments($this->commercialLibraryDocuments())
            ->reply($conversation, 'Je veux vous contacter');

        self::assertSame('lead_request', $reply->messageType);
        self::assertTrue($reply->requestLead);
        self::assertContains('open_lead_form', array_column($reply->actions, 'type'));
        self::assertSame([], $reply->sources);
        self::assertStringContainsString('AMOA SI Finance', $reply->content);
        self::assertStringContainsString('Salesforce', $reply->content);
        self::assertStringContainsString('Dolibarr', $reply->content);
    }

    /**
     * @return iterable<string, array{0:string, 1:string}>
     */
    public static function commercialPathScenarioProvider(): iterable
    {
        yield 'diag sage x3' => ['Sage X3 achats stocks CRM, proposez un mini diagnostic', 'generate_scoping_note'];
        yield 'diag maison objet crm' => ['Maison&Objet veut diagnostiquer remplacement CRM et architecture cible', 'generate_scoping_note'];
        yield 'diag gmao eau' => ['Mini diagnostic GMAO eau et assainissement interventions équipements', 'generate_scoping_note'];
        yield 'diag dpo' => ['Diagnostic RGPD DPO externalisé registre et AIPD', 'generate_scoping_note'];
        yield 'diag iso27001' => ['Diagnostic ISO 27001 SMSI cybersécurité', 'generate_scoping_note'];
        yield 'diag nis2' => ['Diagnostic NIS2 gouvernance sécurité', 'generate_scoping_note'];
        yield 'diag pca pra' => ['Diagnostic PCA PRA continuité SI et risques', 'generate_scoping_note'];
        yield 'diag iso9001' => ['Diagnostic ISO 9001 QSE qualité', 'generate_scoping_note'];
        yield 'diag rfe' => ['Diagnostic facturation électronique PDP', 'generate_scoping_note'];
        yield 'diag dsi transition' => ['Diagnostic DSI de transition schéma directeur', 'generate_scoping_note'];

        yield 'note sage x3' => ['Préparez une note de cadrage Sage X3 achats stocks CRM', 'download_scoping_note'];
        yield 'note maison objet crm' => ['Préparez ma note de cadrage Maison&Objet CRM architecture cible AMOA indépendante', 'download_scoping_note'];
        yield 'note gmao eau' => ['Note de cadrage GMAO eau interventions équipements', 'download_scoping_note'];
        yield 'note dpo' => ['Note de cadrage RGPD DPO registre AIPD', 'download_scoping_note'];
        yield 'note iso27001' => ['Note de cadrage ISO 27001 SMSI', 'download_scoping_note'];
        yield 'note nis2' => ['Note de cadrage NIS2 cybersécurité', 'download_scoping_note'];
        yield 'note pca pra' => ['Note de cadrage PCA PRA continuité', 'download_scoping_note'];
        yield 'note iso9001' => ['Note de cadrage ISO 9001 QSE', 'download_scoping_note'];
        yield 'note rfe' => ['Note de cadrage facturation électronique PDP', 'download_scoping_note'];
        yield 'note dsi transition' => ['Note de cadrage DSI transition gouvernance SI', 'download_scoping_note'];

        yield 'contact sage x3' => ['Je souhaite être recontacté pour Sage X3', 'open_lead_form'];
        yield 'contact maison objet crm' => ['Je veux un rendez vous pour CRM Maison&Objet', 'open_lead_form'];
        yield 'contact gmao eau' => ['Appelez moi pour GMAO eau', 'open_lead_form'];
        yield 'contact dpo' => ['Je veux être contacté pour DPO RGPD', 'open_lead_form'];
        yield 'contact iso27001' => ['Demande de devis ISO 27001', 'open_lead_form'];
        yield 'contact nis2' => ['Prendre rendez vous NIS2', 'open_lead_form'];
        yield 'contact pca pra' => ['Proposition commerciale PCA PRA', 'open_lead_form'];
        yield 'contact iso9001' => ['Rdv ISO 9001 QSE', 'open_lead_form'];
        yield 'contact rfe' => ['Contactez moi pour facturation électronique', 'open_lead_form'];
        yield 'contact dsi transition' => ['Je souhaite une proposition DSI de transition', 'open_lead_form'];
    }

    /**
     * @return iterable<string, array{0:string}>
     */
    public static function firstTurnCommercialOpportunityProvider(): iterable
    {
        yield 'amoa erp' => ['Nous recherchons un AMOA pour remplacer notre ERP.'];
        yield 'dpo' => ['Nous cherchons un prestataire DPO externalisé.'];
        yield 'si finance' => ['Nous voulons cadrer notre SI Finance et nos flux de reporting.'];
        yield 'sap difficulte' => ['Notre intégrateur SAP est en difficulté, pouvez-vous intervenir ?'];
        yield 'consultation crm' => ['Nous devons lancer une consultation CRM.'];
        yield 'iso 27001' => ['Pouvez-vous nous accompagner sur ISO 27001 ?'];
        yield 'rfe' => ['Nous recherchons une assistance pour la RFE.'];
        yield 'dsi transition' => ['Nous cherchons un DSI de transition.'];
        yield 'gmao services publics' => ['Avez-vous une expérience GMAO dans les services publics ?'];
        yield 'nis2' => ['Nous avons besoin d’un accompagnement sur NIS2.'];
        yield 'mapsi' => ['Nous aimerions faire une démonstration MAPSI.'];
    }

    /**
     * @return iterable<string, array{0:string, 1:bool}>
     */
    public static function firstTurnCommercialCorrectionProvider(): iterable
    {
        yield 'dpo externalise commercial' => ['Nous recherchons un DPO externalisé pour notre organisation.', true];
        yield 'si finance commercial' => ['Nous voulons cadrer notre SI Finance et nos flux de reporting.', true];
        yield 'dora information only' => ['DORA, c’est quoi ?', false];
        yield 'student amoa definition' => ['Je suis étudiant, pouvez-vous me définir l’AMOA ?', false];
    }

    /**
     * @return iterable<string, array{0:string, 1:list<string>}>
     */
    public static function linkRecommendationScenarioProvider(): iterable
    {
        yield 'erp sage x3 stocks' => ['ETI industrielle sous Sage X3 avec problèmes de stocks et achats', ['/business-apps/erp']];
        yield 'erp remplacement' => ['Nous voulons remplacer notre ERP vieillissant', ['/business-apps/erp']];
        yield 'erp consultation' => ['Cahier des charges ERP et consultation intégrateur à préparer', ['/business-apps/erp']];
        yield 'erp interfaces' => ['Projet ERP avec interfaces et reprise de données', ['/business-apps/erp']];
        yield 'crm explicite' => ['Nous souhaitons revoir notre CRM et la relation client', ['/crm']];
        yield 'crm salesforce' => ['Salesforce ne couvre plus nos besoins commerciaux', ['/crm']];
        yield 'gmao maintenance' => ['Besoin de choisir une GMAO pour la maintenance', ['/gmao']];
        yield 'gmao interventions' => ['Les interventions et équipements sont mal suivis', ['/gmao']];
        yield 'sirh paie' => ['Refonte SIRH, paie et gestion des temps', ['/sirh']];
        yield 'sirh rh' => ['Projet ressources humaines et logiciel RH', ['/sirh']];
        yield 'finance reporting' => ['SI finance et reporting budgétaire à cadrer', ['/si-finance']];
        yield 'finance cloture' => ['La clôture comptable et le reporting posent problème', ['/si-finance']];
        yield 'rfe pdp' => ['Préparer la facturation électronique et le choix PDP', ['/facturation-electronique-amoa', '/si-finance']];
        yield 'rfe demat' => ['Réforme facturation électronique, besoin AMOA', ['/facturation-electronique-amoa', '/si-finance']];
        yield 'rgpd dpo' => ['Nous cherchons un DPO externalisé RGPD', ['/rgpd-dpo']];
        yield 'rgpd cnil' => ['Audit RGPD après remarque CNIL', ['/rgpd-dpo']];
        yield 'rgpd registre' => ['Mettre à jour registre et DPIA', ['/rgpd-dpo']];
        yield 'cyber iso27001' => ['Préparer ISO 27001 et SMSI', ['/cybersecurite']];
        yield 'cyber nis2' => ['NIS2 et cybersécurité pour organisation régulée', ['/cybersecurite']];
        yield 'qse iso9001' => ['Audit QSE ISO 9001', ['/qse']];
        yield 'qse qualiopi' => ['Qualiopi et démarche qualité', ['/qse']];
        yield 'mapsi grc' => ['MAPSI pour gestion des risques et contrôle interne', ['/mapsi']];
        yield 'mapsi plan actions' => ['Plan d actions risques et contrôle interne', ['/mapsi']];
        yield 'dsi schema directeur' => ['Schéma directeur SI pour la DSI', ['/transformation-si']];
        yield 'dsi urbanisation' => ['Urbanisation SI et gouvernance DSI', ['/transformation-si']];
        yield 'ia data' => ['Automatisation IA et data BI', ['/ia-data']];
        yield 'ia power bi' => ['Power BI et automatisation reporting', ['/ia-data']];
        yield 'mix erp crm' => ['ERP à remplacer, CRM seulement à interfacer', ['/business-apps/erp', '/crm']];
        yield 'mix rgpd cyber' => ['RGPD prioritaire avec questions sécurité secondaires', ['/rgpd-dpo', '/cybersecurite']];
        yield 'contact direct' => ['Je veux être recontacté pour un projet ERP', []];
        yield 'vague bonjour' => ['Bonjour', []];
        yield 'prix erp' => ['Combien coûte une AMOA ERP ?', ['/business-apps/erp']];
        yield 'objection integrateur' => ['Pourquoi ne pas confier notre ERP à notre intégrateur ?', ['/business-apps/erp']];
        yield 'freelance dpo' => ['Un freelance DPO suffit-il pour le RGPD ?', ['/rgpd-dpo']];
        yield 'grand cabinet crm' => ['Pourquoi OLING plutôt qu un grand cabinet CRM ?', ['/crm']];
        yield 'projet bloque erp' => ['Projet ERP bloqué avec intégrateur', ['/business-apps/erp']];
        yield 'urgence reglementaire rgpd' => ['Urgence réglementaire RGPD', ['/rgpd-dpo']];
        yield 'consultation gmao' => ['Consultation prochaine pour GMAO', ['/gmao']];
        yield 'aucun lien naturel' => ['Pouvez-vous expliquer votre méthode générale ?', []];
        yield 'dsi pas erp' => ['DSI cherche gouvernance et arbitrages SI', ['/transformation-si']];
        yield 'finance pas crm' => ['Refonte SI finance, pas de sujet CRM', ['/si-finance']];
    }

    /**
     * @return list<ChatPublicDocument>
     */
    private function commercialLibraryDocuments(): array
    {
        return [
            $this->buildDocument('service', '/business-apps/erp', 'AMOA ERP Sage X3', 'ERP progiciel Sage X3 achats stocks interfaces reprise de données consultation intégrateur'),
            $this->buildDocument('service', '/crm', 'CRM relation client', 'CRM relation client ventes Salesforce force commerciale parcours commercial'),
            $this->buildDocument('service', '/gmao', 'GMAO maintenance', 'GMAO maintenance équipements interventions actifs ordres de travail'),
            $this->buildDocument('service', '/sirh', 'SIRH RH paie', 'SIRH ressources humaines paie gestion des temps'),
            $this->buildDocument('service', '/si-finance', 'SI finance reporting', 'SI finance comptabilité budget reporting financier clôture'),
            $this->buildDocument('service', '/facturation-electronique-amoa', 'AMOA facturation électronique', 'RFE facturation électronique e-invoicing e-reporting plateforme agréée flux interfaces annuaire données de facturation AMOA'),
            $this->buildDocument('service', '/rgpd-dpo', 'RGPD DPO externalisé', 'RGPD DPO CNIL registre DPIA AIPD données personnelles'),
            $this->buildDocument('service', '/cybersecurite', 'Cybersécurité ISO 27001 NIS2', 'Cybersécurité SSI SMSI ISO 27001 NIS2 DORA sécurité'),
            $this->buildDocument('service', '/qse', 'QSE qualité', 'QSE qualité ISO 9001 ISO 14001 ISO 45001 Qualiopi'),
            $this->buildDocument('service', '/mapsi', 'MAPSI GRC risques', 'MAPSI GRC contrôle interne gestion des risques plan actions'),
            $this->buildDocument('service', '/transformation-si', 'Transformation SI DSI', 'DSI schéma directeur gouvernance SI urbanisation transformation SI arbitrages'),
            $this->buildDocument('service', '/ia-data', 'IA data BI', 'IA intelligence artificielle data BI Power BI automatisation reporting'),
            $this->buildDocument('page', '/expertises', 'Expertises OLING', 'Page générale expertises conseil audit transformation conformité applications métiers'),
        ];
    }

    private function buildResponder(): ChatResponder
    {
        return $this->buildResponderWithDocuments([]);
    }

    /**
     * @param ChatPublicDocument[] $documents
     */
    private function buildResponderWithDocuments(array $documents): ChatResponder
    {
        $qualificationService = new ChatQualificationService();
        $repository = $this->createMock(ChatPublicDocumentRepository::class);
        $repository
            ->method('findActiveDocuments')
            ->willReturn($documents);

        return new ChatResponder(
            new PublicContentCatalog(
                $repository,
                $this->createMock(ChatPublicContentIndexer::class),
            ),
            $qualificationService,
            [$this->testGenerativeProvider()],
            new NullLogger(),
            $this->contentProvider(),
        );
    }

    private function contentProvider(): AiConsultantContentProvider
    {
        return new AiConsultantContentProvider(dirname(__DIR__));
    }

    private function testGenerativeProvider(): AiProviderInterface
    {
        return new class implements AiProviderInterface {
            public function getName(): string
            {
                return 'test_llm';
            }

            public function isAvailable(): bool
            {
                return true;
            }

            public function generateDecision(ChatConversation $conversation, string $visitorMessage, array $documents, array $qualification): AiDecision
            {
                $text = mb_strtolower($visitorMessage);
                $reply = 'Bonjour. Je peux vous aider à qualifier ce besoin OLING.';

                if (str_contains($text, 'audit') && str_contains($text, 'rgpd')) {
                    $reply = 'Oui, OLING peut cadrer un audit RGPD à partir des traitements, preuves, AIPD et responsabilités déjà en place.';
                } elseif (str_contains($text, 'dora')) {
                    $reply = 'Oui, OLING peut accompagner un projet de conformité DORA : cadrage du périmètre, analyse des exigences, trajectoire de mise en conformité et pilotage des actions avec les équipes risques, DSI et métiers.';
                } elseif (str_contains($text, 'questionnaire') && str_contains($text, 'erp')) {
                    $reply = 'Pour cadrer un progiciel, on qualifie les modules concernés, la sécurité, RGPD, les livrables AMOA et le macro-planning.';
                } elseif (str_contains($text, 'erp') || str_contains($text, 'crm')) {
                    $reply = 'OLING peut vous répondre sur l’ERP, le CRM ou les applications métiers avec un cadrage adapté.';
                } elseif ($documents !== []) {
                    $reply = $documents[0]['text'];
                }

                return new AiDecision($reply, false, $qualification, array_column($documents, 'url'));
            }
        };
    }

    private function erpProposalRequestMessage(): string
    {
        return 'Bonjour, PME industrielle, environ 40 utilisateurs ERP. Nous cherchons une mission courte d’AMOA ERP pour cadrage projet, ateliers métiers, cartographie des processus études, achats, approvisionnements, stocks, production, qualité, ventes et pilotage, expression des besoins, cahier des charges allégé, préparation de consultation, démonstrations éditeurs, analyse comparative et recommandation. Pouvez-vous nous donner votre méthodologie, le nombre de jours estimés, les livrables, des références industrielles comparables et le coût de la mission ?';
    }

    private function buildDocument(string $type, string $url, string $title, ?string $text = null): ChatPublicDocument
    {
        return (new ChatPublicDocument())
            ->setSourceType($type)
            ->setSourceEntityId(random_int(1, 1000))
            ->setSafeTitle($title)
            ->setSafeText($text ?? ($title.' cadrage projet SI ERP facturation eau assainissement'))
            ->setUrl($url)
            ->setKeywords(['erp', 'facturation', 'eau', 'assainissement', 'consultant'])
            ->setSearchText(strtolower($title).' cadrage projet si erp facturation eau assainissement consultant')
            ->setIsActive(true)
            ->setChecksum($type.'-'.$url)
            ->setUpdatedAt(new \DateTimeImmutable());
    }
}
