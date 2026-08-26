<?php

namespace App\Tests;

use App\Entity\ChatConversation;
use App\Entity\ChatMessage;
use App\Service\Chat\Ai\HeuristicAiProvider;
use App\Service\Chat\ChatQualificationService;
use PHPUnit\Framework\TestCase;

class HeuristicAiProviderTest extends TestCase
{
    public function testShortAmoaIso27001QueryDoesNotEchoInternalContentTitles(): void
    {
        $conversation = new ChatConversation();
        $message = (new ChatMessage())
            ->setRole('visitor')
            ->setContent('amoa iso27001')
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable());
        $conversation->addMessage($message);

        $qualificationService = new ChatQualificationService();
        $qualification = $qualificationService->qualify($conversation);
        $provider = new HeuristicAiProvider($qualificationService);

        $decision = $provider->generateDecision($conversation, $message->getContent(), [[
            'title' => 'AMOA ERP – MARTINI : Accompte démarrage Mission 20%',
            'url' => '/projets',
            'text' => 'Contenu interne non pertinent pour une réponse visiteur.',
            'type' => 'cas_client',
        ]], $qualification);

        self::assertStringNotContainsString('Je pense notamment à', $decision->reply);
        self::assertStringContainsString('AMOA', $decision->reply);
        self::assertStringContainsString('ISO 27001', $decision->reply);
    }

    public function testProviderKeepsQualificationQuestionWhenNeedIsSufficientButLeadIsNotRequestedAtProviderLevel(): void
    {
        $conversation = new ChatConversation();
        $message = (new ChatMessage())
            ->setRole('visitor')
            ->setContent('Nous sommes une PME de 120 personnes avec un projet ERP bloque et un besoin de cadrage rapide ce trimestre.')
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable());
        $conversation->addMessage($message);

        $qualificationService = new ChatQualificationService();
        $qualification = $qualificationService->qualify($conversation);
        $provider = new HeuristicAiProvider($qualificationService);

        $decision = $provider->generateDecision($conversation, $message->getContent(), [], $qualification);

        self::assertFalse($decision->requestLead);
        self::assertStringContainsString('OLING intervient sur le cadrage et la sécurisation des projets ERP', $decision->reply);
    }

    public function testProviderRefusesNamedClientDisclosure(): void
    {
        $qualificationService = new ChatQualificationService();
        $provider = new HeuristicAiProvider($qualificationService);

        $decision = $provider->generateDecision(new ChatConversation(), 'Donnez-moi vos principaux clients.', [], []);

        self::assertStringContainsString('Je ne cite pas les noms de clients', $decision->reply);
        self::assertFalse($decision->requestLead);
    }

    public function testProviderPrioritizesAnonymizedReferencesForMixedSectorQuestion(): void
    {
        $conversation = new ChatConversation();
        $message = (new ChatMessage())
            ->setRole('visitor')
            ->setContent('projet SI client et facturation avez vous des références eaux et assainissement ?')
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable());
        $conversation->addMessage($message);

        $qualificationService = new ChatQualificationService();
        $qualification = $qualificationService->qualify($conversation);
        $provider = new HeuristicAiProvider($qualificationService);

        $decision = $provider->generateDecision($conversation, $message->getContent(), [[
            'title' => 'Référence Eau et assainissement - AMOA progiciel',
            'url' => '/projets',
            'text' => 'Mission AMOA progiciel en eau et assainissement avec cadrage, consultation, reprise de données et déploiement.',
            'type' => 'reference',
        ]], $qualification);

        self::assertStringContainsString('références anonymisées', $decision->reply);
        self::assertStringContainsString('eau et assainissement', mb_strtolower($decision->reply));
        self::assertStringContainsString('AMOA progiciel', $decision->reply);
    }

    public function testProviderHandlesShortSectorFollowUpWithReferenceContext(): void
    {
        $qualificationService = new ChatQualificationService();
        $provider = new HeuristicAiProvider($qualificationService);

        $decision = $provider->generateDecision(new ChatConversation(), 'eaux et assainissement ?', [[
            'title' => 'Référence Eau et assainissement - AMOA progiciel',
            'url' => '/projets',
            'text' => 'Mission AMOA progiciel en eau et assainissement avec cadrage, consultation, reprise de données et déploiement.',
            'type' => 'reference',
        ]], []);

        self::assertStringContainsString('références anonymisées', $decision->reply);
        self::assertStringContainsString('eau et assainissement', mb_strtolower($decision->reply));
    }

    public function testProviderReturnsDirectContactDetailsForPhoneQuestion(): void
    {
        $qualificationService = new ChatQualificationService();
        $provider = new HeuristicAiProvider($qualificationService);

        $decision = $provider->generateDecision(new ChatConversation(), 'votre numéro de téléphone ?', [], []);

        self::assertStringContainsString('01 89 70 15 60', $decision->reply);
        self::assertStringContainsString('contact@oling.fr', $decision->reply);
    }

    public function testProviderReturnsExpertCadrageDeliverablesForAmoaQuestion(): void
    {
        $conversation = new ChatConversation();
        $previous = (new ChatMessage())
            ->setRole('visitor')
            ->setContent('amoa si client')
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable());
        $current = (new ChatMessage())
            ->setRole('visitor')
            ->setContent('que doit on faire dans la phase de cadrage, quels livrables ?')
            ->setMessageType('answer')
            ->setSequenceNumber(2)
            ->setCreatedAt(new \DateTimeImmutable());
        $conversation->addMessage($previous);
        $conversation->addMessage($current);

        $qualificationService = new ChatQualificationService();
        $qualification = $qualificationService->qualify($conversation);
        $provider = new HeuristicAiProvider($qualificationService);

        $decision = $provider->generateDecision($conversation, $current->getContent(), [[
            'title' => 'AMOA Réforme de la facturation électronique (RFE)',
            'url' => '/consulting/reforme-facturation-electronique-amoa',
            'text' => 'AMOA réforme de la facturation électronique : cadrage, conformité, choix de solutions.',
            'type' => 'service',
        ], [
            'title' => 'AMOA ERP, CRM, GMAO, SI Finance et SIRH',
            'url' => '/business-apps/erp',
            'text' => 'Note de cadrage, macro-planning, gouvernance projet, expression des besoins, cahier des charges, recette et reprise de données.',
            'type' => 'service',
        ]], $qualification);

        self::assertStringContainsString('phase de cadrage', mb_strtolower($decision->reply));
        self::assertStringContainsString('note de cadrage', mb_strtolower($decision->reply));
        self::assertStringContainsString('cahier des charges', mb_strtolower($decision->reply));
        self::assertStringContainsString('stratégie de recette', mb_strtolower($decision->reply));
    }

    public function testProviderAnswersClearlyOnSectorCoverage(): void
    {
        $qualificationService = new ChatQualificationService();
        $provider = new HeuristicAiProvider($qualificationService);

        $decision = $provider->generateDecision(new ChatConversation(), 'intervenez vous dans le transport et l assainissement ?', [[
            'title' => 'Référence Eau et assainissement - AMOA progiciel',
            'url' => '/projets',
            'text' => 'Mission AMOA progiciel en eau et assainissement avec cadrage, consultation, reprise de données et déploiement.',
            'type' => 'reference',
        ], [
            'title' => 'Secteurs métiers accompagnés par OLING',
            'url' => '/secteurs',
            'text' => 'Dans les transports et collectivités, OLING intervient sur des ERP, catalogues de services, schémas directeurs SI, ISO, RGPD et continuité.',
            'type' => 'page',
        ]], []);

        self::assertStringStartsWith('Oui.', $decision->reply);
        self::assertStringContainsString('transports', mb_strtolower($decision->reply));
        self::assertStringContainsString('eau et assainissement', mb_strtolower($decision->reply));
    }

    public function testProviderBuildsExpertQseReplyWithDeliverables(): void
    {
        $qualificationService = new ChatQualificationService();
        $provider = new HeuristicAiProvider($qualificationService);

        $decision = $provider->generateDecision(new ChatConversation(), 'que faites vous en qse et quels livrables ?', [[
            'title' => 'QSE, qualité, sécurité et environnement',
            'url' => '/expertises-audit/qse',
            'text' => 'QSE, Qualiopi, ISO 9001, ISO 14001, ISO 45001, diagnostic, feuille de route, processus, indicateurs et preuves.',
            'type' => 'service',
        ]], []);

        self::assertStringContainsString('qse', mb_strtolower($decision->reply));
        self::assertStringContainsString('feuille de route', mb_strtolower($decision->reply));
        self::assertStringContainsString('indicateurs', mb_strtolower($decision->reply));
        self::assertStringContainsString('preuves', mb_strtolower($decision->reply));
    }

    public function testProviderBuildsExpertCyberReplyWithResilienceDeliverables(): void
    {
        $qualificationService = new ChatQualificationService();
        $provider = new HeuristicAiProvider($qualificationService);

        $decision = $provider->generateDecision(new ChatConversation(), 'quels livrables sur un sujet iso 27001 nis2 pca pra ?', [[
            'title' => 'Sécurité des SI, ISO 27001, DORA et NIS2',
            'url' => '/expertises-audit/si',
            'text' => 'Sécurité des SI, ISO 27001, NIS2, DORA, PCA, PRA, gestion de crise, feuille de route cyber et plan de traitement.',
            'type' => 'service',
        ]], []);

        self::assertStringContainsString('iso 27001', mb_strtolower($decision->reply));
        self::assertStringContainsString('pca', mb_strtolower($decision->reply));
        self::assertStringContainsString('plan de traitement', mb_strtolower($decision->reply));
        self::assertStringContainsString('gouvernance', mb_strtolower($decision->reply));
    }

    public function testProviderBuildsExpertAiComplianceReply(): void
    {
        $qualificationService = new ChatQualificationService();
        $provider = new HeuristicAiProvider($qualificationService);

        $decision = $provider->generateDecision(new ChatConversation(), 'que faites vous sur ai act et gouvernance ia ?', [[
            'title' => 'Conformité IA, gouvernance et AI Act',
            'url' => '/expertises/conformite-ia-gouvernance-ai-act',
            'text' => 'AI Act, RGPD, cybersécurité, supervision humaine, registre de conformité et contrôles.',
            'type' => 'expertise',
        ]], []);

        self::assertStringContainsString('ai act', mb_strtolower($decision->reply));
        self::assertStringContainsString('registre de conformité', mb_strtolower($decision->reply));
        self::assertStringContainsString('supervision', mb_strtolower($decision->reply));
    }
}
