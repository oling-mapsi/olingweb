<?php

namespace App\Tests;

use App\Entity\ChatConversation;
use App\Entity\ChatMessage;
use App\Entity\ChatPublicDocument;
use App\Repository\ChatPublicDocumentRepository;
use App\Service\Chat\ChatPublicContentIndexer;
use App\Service\Chat\Ai\HeuristicAiProvider;
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

        self::assertStringContainsString('01 89 70 15 60', $reply->content);
        self::assertStringContainsString('contact@oling.fr', $reply->content);
        self::assertSame([], $reply->sources);
        self::assertFalse($reply->requestLead);
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

    public function testReferenceQuestionDoesNotExposeTeamCardWhenExpertWasNotAsked(): void
    {
        $reply = $this->buildResponderWithDocuments([
            $this->buildDocument('team', '/equipe/jean-claude-vati', 'Jean Claude VATI Consultant SI Senior'),
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
            $this->buildDocument('team', '/equipe/jean-claude-vati', 'Jean Claude VATI Consultant SI Senior'),
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
            new HeuristicAiProvider($qualificationService),
            [],
            new NullLogger(),
        );
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
