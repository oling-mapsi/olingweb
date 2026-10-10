<?php

namespace App\Service\Chat;

use App\Entity\ChatConversation;
use App\Service\Chat\Ai\AiDecision;
use App\Service\Chat\Ai\AiProviderInterface;
use Psr\Log\LoggerInterface;

class ChatResponder
{
    /**
     * @param iterable<AiProviderInterface> $providers
     */
    public function __construct(
        private readonly PublicContentCatalog $publicContentCatalog,
        private readonly ChatQualificationService $qualificationService,
        private readonly iterable $providers,
        private readonly LoggerInterface $logger,
        private readonly AiConsultantContentProvider $contentProvider,
        private readonly SectorTaxonomy $sectorTaxonomy = new SectorTaxonomy(),
        ?ChatOwnerRouter $ownerRouter = null,
        private readonly int $globalLatencyBudgetMs = 28000,
    ) {
        $this->ownerRouter = $ownerRouter ?? new ChatOwnerRouter();
    }

    private readonly ChatOwnerRouter $ownerRouter;

    public function getWelcomeMessage(string $locale = AiConsultantContentProvider::LOCALE): string
    {
        return $this->contentProvider->text('copy.welcome', $locale);
    }

    public function reply(ChatConversation $conversation, string $visitorMessage): ChatReply
    {
        $startedAt = microtime(true);
        $qualification = $this->qualificationService->qualify($conversation);

        if ($this->asksForNamedClientOrClientList($visitorMessage)) {
            return new ChatReply(
                $this->confidentialityRefusal($visitorMessage),
                false,
                [],
                $qualification,
                'confidentiality_guard',
                'question'
            );
        }

        if ($this->showsStrongContactIntent($visitorMessage)) {
            return new ChatReply(
                $this->leadRequestText($conversation, $qualification),
                true,
                [],
                $qualification,
                'contact_router',
                'lead_request',
                actions: [[
                    'type' => 'open_lead_form',
                    'label' => 'Transmettre mon projet à OLING',
                ]]
            );
        }

        if ($this->showsDirectContactQuestion($visitorMessage)) {
            return new ChatReply(
                $this->contactDetailsText(false),
                false,
                [],
                $qualification,
                'contact_router',
                'contact_info'
            );
        }

        $lookupStartedAt = microtime(true);
        $documents = $this->shouldSkipDocumentLookup($visitorMessage, $qualification)
            ? []
            : $this->findRelevantDocumentsSafely($conversation, $visitorMessage, $qualification);
        $retrievalDurationMs = (int) round((microtime(true) - $lookupStartedAt) * 1000);

        $fallbackUsed = false;
        $errorCode = null;
        foreach ($this->providers as $provider) {
            if (!$provider->isAvailable()) {
                continue;
            }

            for ($attempt = 1; $attempt <= 2; ++$attempt) {
                if ($this->elapsedMs($startedAt) >= $this->globalLatencyBudgetMs) {
                    $fallbackUsed = true;
                    $errorCode = 'GlobalLatencyBudgetExceeded';
                    $this->logger->warning('Chat provider global latency budget exceeded.', [
                        'provider' => $provider->getName(),
                        'budget_ms' => $this->globalLatencyBudgetMs,
                    ]);
                    break 2;
                }

                try {
                    $providerStartedAt = microtime(true);
                    $decision = $provider->generateDecision($conversation, $visitorMessage, $documents, $qualification);
                    $reply = $this->createReplyFromDecision($conversation, $visitorMessage, $documents, $qualification, $decision, $provider->getName(), $fallbackUsed, $errorCode, $this->elapsedMs($startedAt));
                    $this->logTechnicalMetrics($visitorMessage, $documents, $reply, $provider->getName(), $retrievalDurationMs, (int) round((microtime(true) - $providerStartedAt) * 1000), $this->elapsedMs($startedAt), $fallbackUsed);

                    return $reply;
                } catch (\Throwable $exception) {
                    $fallbackUsed = true;
                    $rootException = $exception;
                    while ($rootException->getPrevious() !== null) {
                        $rootException = $rootException->getPrevious();
                    }
                    $errorCode = (new \ReflectionClass($rootException))->getShortName();
                    $this->logger->warning('Chat provider failed.', [
                        'provider' => $provider->getName(),
                        'attempt' => $attempt,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }
        }

        $reply = $this->createUnavailableReply($qualification, $visitorMessage, $errorCode);
        $this->logTechnicalMetrics($visitorMessage, $documents, $reply, 'llm_unavailable', $retrievalDurationMs, 0, $this->elapsedMs($startedAt), true);

        return $reply;
    }

    private function elapsedMs(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}> $documents
     * @param array<string, string|null> $qualification
     */
    private function createReplyFromDecision(
        ChatConversation $conversation,
        string $visitorMessage,
        array $documents,
        array $qualification,
        AiDecision $decision,
        ?string $provider,
        bool $fallbackUsed,
        ?string $errorCode,
        int $latencyMs
    ): ChatReply {
        $mergedQualification = $this->qualificationService->qualify($conversation, $decision->qualification ?: $qualification);
        $contactStep = $this->resolveContactStep($conversation, $visitorMessage, $mergedQualification, $decision->requestLead);

        return new ChatReply(
            $this->applyFinalSafetyGuard($this->enforceSectorReferenceTruth($this->finalizeReply($decision->reply, $contactStep, $conversation, $mergedQualification), $documents, $visitorMessage)),
            $this->shouldShowLeadForm($contactStep),
            $this->filterSources($documents, trim($this->visitorConversationText($conversation).' '.$visitorMessage), $mergedQualification),
            $mergedQualification,
            $provider,
            $contactStep,
            $decision->model,
            $fallbackUsed,
            $this->ownerRouter->resolveOwnerUrl($visitorMessage),
            array_map(static fn (array $document): array => [
                'url' => $document['url'],
                'type' => $document['type'],
                'score' => $document['score'] ?? null,
            ], $documents),
            $latencyMs,
            $decision->inputTokens,
            $decision->outputTokens,
            $errorCode,
            $decision->requestId,
            $fallbackUsed && $provider !== 'openai' ? 'llm_secondary' : 'llm_primary',
            $this->replyActions($conversation, $visitorMessage, $mergedQualification, $contactStep)
        );
    }

    /**
     * @param array<string, string|null> $qualification
     * @return array<int, array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}>
     */
    private function findRelevantDocumentsSafely(ChatConversation $conversation, string $visitorMessage, array $qualification): array
    {
        try {
            $qualificationTerms = array_values(array_filter(
                $qualification,
                static fn (mixed $value): bool => is_string($value) && $value !== ''
            ));
            $contextQuery = trim($this->visitorConversationText($conversation).' '.$visitorMessage);
            $primaryDocuments = $this->publicContentCatalog->findRelevantDocuments(
                $contextQuery,
                $conversation->getSourcePath(),
                6
            );
            $sector = $this->publicContentCatalog->detectSector($contextQuery);
            $sectorReferences = $sector === null
                ? []
                : $this->publicContentCatalog->findSectorReferences($sector, $contextQuery, 3);

            if ($qualificationTerms === []) {
                return array_slice($this->mergeDocumentsByUrl($sectorReferences, $primaryDocuments), 0, 8);
            }

            $expandedDocuments = $this->publicContentCatalog->findRelevantDocuments(
                trim($contextQuery.' '.implode(' ', $qualificationTerms)),
                $conversation->getSourcePath(),
                8
            );

            return array_slice($this->mergeDocumentsByUrl($sectorReferences, $primaryDocuments, $expandedDocuments), 0, 8);
        } catch (\Throwable $exception) {
            $this->logger->warning('Public content catalog lookup failed.', [
                'error' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}> $documents
     * @param array<string, string|null> $qualification
     */
    /**
     * @param array<string, string|null> $qualification
     */
    private function createUnavailableReply(array $qualification, string $visitorMessage, ?string $errorCode): ChatReply
    {
        $isProposalRequest = $this->isProposalRequest($visitorMessage, $qualification);

        return new ChatReply(
            $isProposalRequest ? $this->proposalUnavailableText($qualification) : $this->contentProvider->text('copy.assistant_unavailable'),
            false,
            [],
            $qualification,
            'llm_unavailable',
            'technical_unavailable',
            null,
            true,
            null,
            [],
            null,
            null,
            null,
            $errorCode,
            null,
            'llm_unavailable',
            $isProposalRequest ? [[
                'type' => 'open_lead_form',
                'label' => 'Transmettre ma demande de proposition à OLING',
            ]] : []
        );
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}> $documents
     * @param array<string, string|null> $qualification
     * @return string[]
     */
    private function filterSources(array $documents, string $visitorMessage, array $qualification): array
    {
        if ($documents === []) {
            return [];
        }

        if ($this->showsStrongContactIntent($visitorMessage)) {
            return [];
        }

        if (mb_strlen(trim($visitorMessage)) < 18 && $this->qualificationService->isTooVague($qualification)) {
            return [];
        }

        if (
            $this->commercialTopics($visitorMessage.' '.implode(' ', array_filter($qualification, 'is_string'))) === []
            && !$this->isReferenceIntent($visitorMessage)
            && !$this->isExpertIntent($visitorMessage)
            && !$this->isSectorIntent($visitorMessage)
        ) {
            return [];
        }

        $expertIntent = $this->isExpertIntent($visitorMessage);
        $referenceIntent = $this->isReferenceIntent($visitorMessage);
        $sectorIntent = $this->isSectorIntent($visitorMessage);
        $preferredTypes = $expertIntent
            ? ['team', 'expertise', 'service', 'page', 'reference']
            : ($sectorIntent
                ? ['reference', 'page', 'expertise', 'service']
                : ($referenceIntent ? ['reference', 'expertise', 'service', 'page'] : ['expertise', 'service', 'page', 'reference']));

        $filtered = array_values(array_filter(
            $documents,
            static fn (array $document): bool => $expertIntent || $document['type'] !== 'team'
        ));

        $filtered = array_values(array_filter(
            $filtered,
            fn (array $document): bool => ($referenceIntent && ($document['type'] ?? null) === 'reference')
                || $this->recommendedLinkScore($document, $visitorMessage, $qualification) >= $this->recommendedLinkThreshold($visitorMessage, $qualification)
        ));

        usort($filtered, function (array $left, array $right) use ($preferredTypes, $visitorMessage, $qualification): int {
            $leftRank = array_search($left['type'], $preferredTypes, true);
            $rightRank = array_search($right['type'], $preferredTypes, true);
            $leftScore = $this->recommendedLinkScore($left, $visitorMessage, $qualification);
            $rightScore = $this->recommendedLinkScore($right, $visitorMessage, $qualification);

            if ($leftScore !== $rightScore) {
                return $rightScore <=> $leftScore;
            }

            return ($leftRank === false ? 99 : $leftRank) <=> ($rightRank === false ? 99 : $rightRank);
        });

        $urls = [];
        $hasReference = false;
        foreach ($filtered as $document) {
            if (!$referenceIntent && !$sectorIntent && ($document['url'] ?? null) === '/projets') {
                continue;
            }

            if ($document['type'] === 'reference') {
                if ($hasReference) {
                    continue;
                }
                $hasReference = true;
            }

            $urls[] = $document['url'];
            if (count($urls) === 2) {
                break;
            }
        }

        return array_values(array_unique($urls));
    }

    /**
     * @param array{title:string,url:string,text:string,type:string,image:?string,excerpt:string} $document
     * @param array<string, string|null> $qualification
     */
    private function recommendedLinkScore(array $document, string $visitorMessage, array $qualification): int
    {
        $queryTopics = $this->commercialTopics($visitorMessage.' '.implode(' ', array_filter($qualification, 'is_string')));
        $documentTopics = $this->commercialTopics(($document['title'] ?? '').' '.($document['text'] ?? '').' '.($document['url'] ?? ''));

        if ($queryTopics !== [] && $documentTopics !== [] && array_intersect($queryTopics, $documentTopics) === []) {
            return -100;
        }

        if ($this->hasBlockingTopicMismatch($queryTopics, $documentTopics)) {
            return -100;
        }

        $score = $this->sourceDisplayScore($document, $visitorMessage);
        $score += min(16, (int) round(((int) ($document['score'] ?? 0)) / 6));
        $url = (string) ($document['url'] ?? '');

        if (in_array('rfe', $queryTopics, true)) {
            if (str_contains($url, '/facturation-electronique-amoa')) {
                $score += 80;
            } elseif (str_contains($url, '/si-finance')) {
                $score += 35;
            } elseif (!in_array('rfe', $documentTopics, true) && !in_array('finance', $documentTopics, true)) {
                $score -= 60;
            }
        }

        $primaryNeed = $qualification['primary_need'] ?? null;
        if (is_string($primaryNeed) && $primaryNeed !== '') {
            $primaryTopics = $this->topicsForNeed($primaryNeed);
            if (array_intersect($primaryTopics, $documentTopics) !== []) {
                $score += 18;
            }
        }

        if (($document['type'] ?? null) === 'reference' && $this->isReferenceIntent($visitorMessage)) {
            $score += 8;
        }

        if (($qualification['primary_need'] ?? null) === 'si_finance' && in_array('rfe', $documentTopics, true)) {
            $score += 100;
        }

        if (($document['type'] ?? null) === 'page' && count($documentTopics) > 1 && $queryTopics !== [] && count(array_intersect($queryTopics, $documentTopics)) === 1) {
            $score -= 8;
        }

        return $score;
    }

    /**
     * @param array<string, string|null> $qualification
     */
    private function recommendedLinkThreshold(string $visitorMessage, array $qualification): int
    {
        if ($this->isReferenceIntent($visitorMessage) || $this->isExpertIntent($visitorMessage)) {
            return 4;
        }

        if (($qualification['commercial_intent'] ?? null) !== null && ($qualification['commercial_intent'] ?? null) !== 'information') {
            return 12;
        }

        return 7;
    }

    /**
     * @return list<string>
     */
    private function commercialTopics(string $value): array
    {
        $text = $this->normalize($value);
        $text = preg_replace('/\b(pas de|sans|hors)\s+(sujet\s+)?(erp|progiciel|crm|gmao|sirh|rh|finance|facturation|rgpd|dpo|cyber|securite|qse|mapsi|ia|data)\b/', '', $text) ?? $text;
        $topics = [];
        $patterns = [
            'erp' => '/\b(erp|progiciel|pgi|sage|sage x3|sap|cegid|divalto|stocks|achats)\b/',
            'crm' => '/\b(crm|relation client|salesforce|force commerciale|commercial)\b/',
            'gmao' => '/\b(gmao|maintenance|equipements|interventions)\b/',
            'sirh' => '/\b(sirh|paie|rh|ressources humaines|gestion des temps)\b/',
            'finance' => '/\b(si finance|finance|comptabilite|budget|reporting financier|cloture)\b/',
            'rfe' => '/\b(facturation electronique|rfe|pdp|plateforme agreee|plateformes agreees|dematerialisation|e invoicing|e reporting)\b/',
            'rgpd' => '/\b(rgpd|dpo|dpd|cnil|donnees personnelles|registre|dpia|aipd)\b/',
            'cyber' => '/\b(cyber|cybersecurite|securite|ssi|iso 27001|nis2|dora|smsi)\b/',
            'qse' => '/\b(qse|qualite|iso 9001|iso 14001|iso 45001|qualiopi)\b/',
            'mapsi' => '/\b(mapsi|grc|controle interne|gestion des risques|plan d actions)\b/',
            'dsi' => '/\b(dsi|schema directeur|gouvernance si|urbanisation|transformation si)\b/',
            'ia' => '/\b(ia|intelligence artificielle|data|bi|power bi|automatisation)\b/',
        ];

        foreach ($patterns as $topic => $pattern) {
            if (preg_match($pattern, $text) === 1) {
                $topics[] = $topic;
            }
        }

        return array_values(array_unique($topics));
    }

    /**
     * @return list<string>
     */
    private function topicsForNeed(string $need): array
    {
        return match ($need) {
            'amoa_erp' => ['erp'],
            'crm' => ['crm'],
            'gmao' => ['gmao'],
            'sirh' => ['sirh'],
            'si_finance' => ['finance'],
            'rfe' => ['rfe'],
            'rgpd' => ['rgpd'],
            'cybersecurite' => ['cyber'],
            'qse' => ['qse'],
            'grc_mapsi' => ['mapsi'],
            'transformation_si', 'organisation_gouvernance' => ['dsi'],
            'ia_data_automatisation' => ['ia'],
            default => [],
        };
    }

    /**
     * @param list<string> $queryTopics
     * @param list<string> $documentTopics
     */
    private function hasBlockingTopicMismatch(array $queryTopics, array $documentTopics): bool
    {
        if ($queryTopics === [] || $documentTopics === []) {
            return false;
        }

        if (in_array('rgpd', $queryTopics, true) && in_array('cyber', $documentTopics, true) && !in_array('rgpd', $documentTopics, true)) {
            return true;
        }

        if (in_array('erp', $queryTopics, true) && in_array('crm', $documentTopics, true) && !in_array('erp', $documentTopics, true) && !in_array('crm', $queryTopics, true)) {
            return true;
        }

        if (in_array('crm', $queryTopics, true) && in_array('erp', $documentTopics, true) && !in_array('crm', $documentTopics, true) && !in_array('erp', $queryTopics, true)) {
            return true;
        }

        if (in_array('rfe', $queryTopics, true) && !in_array('rfe', $documentTopics, true) && !in_array('finance', $documentTopics, true)) {
            return true;
        }

        return false;
    }

    /**
     * @param array<string, string|null> $qualification
     */
    private function resolveContactStep(ChatConversation $conversation, string $visitorMessage, array $qualification, bool $providerRequestsLead): string
    {
        if ($this->isNonCommercialInformationOnly($visitorMessage)) {
            return 'question';
        }

        if ($this->isProposalRequest($visitorMessage, $qualification)) {
            return 'proposal_request';
        }

        if ($this->isScopingNoteIntent($visitorMessage)) {
            return 'scoping_note';
        }

        if ($this->isDiagnosticIntent($visitorMessage)) {
            return 'diagnostic';
        }

        if ($this->showsDirectContactQuestion($visitorMessage)) {
            return 'contact_info';
        }

        if ($this->showsStrongContactIntent($visitorMessage)) {
            return 'lead_request';
        }

        if ($this->hasPendingContactOffer($conversation) && $this->isPositiveReply($visitorMessage)) {
            return 'lead_request';
        }

        if (($providerRequestsLead || $this->isFirstTurnCommercialOpportunity($conversation, $visitorMessage, $qualification)) && $this->shouldOfferContact($conversation, $qualification)) {
            return 'contact_offer';
        }

        if ($this->shouldOfferContact($conversation, $qualification)) {
            return 'contact_offer';
        }

        return 'question';
    }

    /**
     * @param array<string, string|null> $qualification
     * @return array<int, array{type:string,label:string}>
     */
    private function replyActions(ChatConversation $conversation, string $visitorMessage, array $qualification, string $contactStep): array
    {
        if ($contactStep === 'diagnostic') {
            return [[
                'type' => 'generate_scoping_note',
                'label' => 'Préparer ma note de cadrage',
            ], [
                'type' => 'open_lead_form',
                'label' => 'Échanger avec un consultant OLING',
            ]];
        }

        if ($contactStep === 'scoping_note') {
            return [[
                'type' => 'download_scoping_note',
                'label' => 'Télécharger la note',
            ], [
                'type' => 'open_lead_form',
                'label' => 'Transmettre à OLING',
            ]];
        }

        if (in_array($contactStep, ['contact_offer', 'lead_request', 'proposal_request'], true)) {
            $actions = [];
            if ($contactStep === 'contact_offer' && $this->hasScopingMaterial($conversation, $visitorMessage, $qualification)) {
                $actions[] = [
                    'type' => 'generate_scoping_note',
                    'label' => 'Préparer ma note de cadrage PDF',
                ];
            }
            $actions[] = [
                'type' => 'open_lead_form',
                'label' => match ($contactStep) {
                    'proposal_request' => 'Recevoir une proposition OLING',
                    'contact_offer' => 'Être recontacté par OLING',
                    default => 'Transmettre mon projet à OLING',
                },
            ];

            return $actions;
        }

        if ($this->shouldOfferDiagnosticAction($conversation, $visitorMessage, $qualification)) {
            return [[
                'type' => 'start_diagnostic',
                'label' => 'Lancer un mini-diagnostic',
            ]];
        }

        if ($this->hasScopingMaterial($conversation, $visitorMessage, $qualification)) {
            return [[
                'type' => 'generate_scoping_note',
                'label' => 'Préparer ma note de cadrage',
            ]];
        }

        return [];
    }

    /**
     * @param array<string, string|null> $qualification
     */
    private function shouldOfferDiagnosticAction(ChatConversation $conversation, string $visitorMessage, array $qualification): bool
    {
        if ($this->isDiagnosticIntent($visitorMessage) || $this->hasActionAlreadyOffered($conversation, 'mini-diagnostic')) {
            return false;
        }

        return $this->commercialTopics($visitorMessage.' '.implode(' ', array_filter($qualification, 'is_string'))) !== []
            && !$this->qualificationService->isTooVague($qualification);
    }

    /**
     * @param array<string, string|null> $qualification
     */
    private function hasScopingMaterial(ChatConversation $conversation, string $visitorMessage, array $qualification): bool
    {
        if ($this->isScopingNoteIntent($visitorMessage) || $this->countVisitorMessages($conversation) < 2) {
            return false;
        }

        $known = array_filter($qualification, static fn (mixed $value): bool => is_string($value) && trim($value) !== '');

        return count($known) >= 4 && ($this->commercialTopics($visitorMessage.' '.implode(' ', $known)) !== []);
    }

    private function hasActionAlreadyOffered(ChatConversation $conversation, string $needle): bool
    {
        foreach ($conversation->getMessages() as $message) {
            if ($message->getRole() === 'assistant' && str_contains($this->normalize($message->getContent()), $this->normalize($needle))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, string|null> $qualification
     */
    private function shouldOfferContact(ChatConversation $conversation, array $qualification): bool
    {
        if ($this->hasContactRefusal($conversation)) {
            return false;
        }

        if ($this->isNonCommercialInformationOnly($this->visitorConversationText($conversation))) {
            return false;
        }

        if (
            $this->countVisitorMessages($conversation) === 1
            && !empty($qualification['primary_need'])
            && $this->isExplicitProfessionalCommercialNeed($this->visitorConversationText($conversation))
        ) {
            return true;
        }

        if (!$this->qualificationService->isReadyForLead($qualification, $conversation)) {
            return false;
        }

        if ($this->hasPendingContactOffer($conversation)) {
            return false;
        }

        return $this->countVisitorMessages($conversation) >= 1;
    }

    private function isFirstTurnCommercialOpportunity(ChatConversation $conversation, string $visitorMessage, array $qualification): bool
    {
        if ($this->countVisitorMessages($conversation) !== 1 || $this->hasContactRefusal($conversation)) {
            return false;
        }

        if (empty($qualification['primary_need']) || ($qualification['commercial_intent'] ?? null) === 'information') {
            if (!$this->isExplicitProfessionalCommercialNeed($visitorMessage)) {
                return false;
            }
        }

        if ($this->isNonCommercialInformationOnly($visitorMessage)) {
            return false;
        }

        return $this->isExplicitProfessionalCommercialNeed($visitorMessage) || (bool) preg_match(
            '/\b(nous|notre|je suis|societe|entreprise|organisation|recherchons|cherchons|besoin|devons|souhaitons|pouvez vous nous accompagner|prestataire|consultant|integrateur|conformes|conformite|remplacer|migration|dpo externalise|dsi de transition|demo|demonstration)\b/',
            $this->normalize($visitorMessage)
        );
    }

    private function isExplicitProfessionalCommercialNeed(string $message): bool
    {
        $text = $this->normalize($message);

        return (bool) preg_match('/\b(nous recherchons|nous cherchons|recherchons|cherchons|souhaitons|nous voulons|besoin|devons|remplacer|cadrer|accompagnement|accompagner|dpo externalise|si finance|flux de reporting|premier echange|prestataire|consultant)\b/', $text);
    }

    /**
     * @param array<string, string|null> $qualification
     */
    private function isProposalRequest(string $message, array $qualification): bool
    {
        $text = $this->normalize($message);
        if (in_array($qualification['commercial_intent'] ?? null, ['quote_request', 'proposal_request', 'devis'], true)
            && preg_match('/\b(methodologie|livrables|nombre de jours|jours homme|references|cout|prix|budget|proposition|devis|mission)\b/', $text) === 1) {
            return true;
        }

        $signals = 0;
        foreach (['methodologie', 'livrables', 'nombre de jours', 'jours homme', 'references', 'cout', 'prix', 'budget', 'proposition', 'devis'] as $needle) {
            if (str_contains($text, $needle)) {
                ++$signals;
            }
        }

        return $signals >= 3 && preg_match('/\b(erp|amoa|mission|prestations|pme|industrie|industrielle)\b/', $text) === 1;
    }

    /**
     * @param array<string, string|null> $qualification
     */
    private function proposalUnavailableText(array $qualification): string
    {
        $need = ($qualification['primary_need'] ?? null) === 'amoa_erp' ? ' de mission AMOA ERP' : '';

        return "Je rencontre momentanément une difficulté technique pour préparer votre réponse détaillée.\n\nVotre demande".$need." peut néanmoins être transmise directement à notre équipe, avec votre cahier des charges déjà renseigné.\n\nUn consultant OLING pourra ainsi examiner votre besoin et préparer une proposition adaptée.";
    }

    private function isNonCommercialInformationOnly(string $message): bool
    {
        $text = $this->normalize($message);

        if (preg_match('/\b(je suis etudiant|etudiante|definition|definir|c est quoi|qu est ce que|expliquez moi|simplement comprendre)\b/', $text) === 1) {
            return true;
        }

        return preg_match('/^\s*(dora|amoa|rfe|nis2|rgpd|iso 27001)\s*(c est quoi|qu est ce que c est|definition|definir|expliquez|explique moi)?\s*\??\s*$/', $text) === 1;
    }

    private function hasContactRefusal(ChatConversation $conversation): bool
    {
        $messages = $conversation->getMessages()->toArray();
        for ($index = count($messages) - 1; $index >= 0; --$index) {
            $message = $messages[$index];
            if ($message->getRole() !== 'visitor') {
                continue;
            }

            $text = $this->normalize((string) $message->getContent());
            if (preg_match('/\b(pas de contact|ne me contactez pas|pas etre recontacte|pas de formulaire|pas maintenant|plus tard)\b/', $text) === 1) {
                return true;
            }

            return false;
        }

        return false;
    }

    private function hasPendingContactOffer(ChatConversation $conversation): bool
    {
        $messages = $conversation->getMessages()->toArray();
        $lastAssistant = null;

        for ($index = count($messages) - 1; $index >= 0; --$index) {
            $message = $messages[$index];
            if ($message->getRole() === 'assistant') {
                $lastAssistant = $message;
                break;
            }
        }

        return $lastAssistant?->getMessageType() === 'contact_offer';
    }

    private function countVisitorMessages(ChatConversation $conversation): int
    {
        $count = 0;
        foreach ($conversation->getMessages() as $message) {
            if ($message->getRole() === 'visitor') {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @param array<string, string|null> $qualification
     */
    private function shouldSkipDocumentLookup(string $visitorMessage, array $qualification): bool
    {
        if ($this->asksForNamedClientOrClientList($visitorMessage)) {
            return true;
        }

        return $this->showsDirectContactQuestion($visitorMessage) || $this->showsStrongContactIntent($visitorMessage);
    }

    private function isDiagnosticIntent(string $message): bool
    {
        $text = $this->normalize($message);

        return (bool) preg_match('/\b(mini diagnostic|diagnostic|diagnostiqu\w*|questions de cadrage|approfondir le cadrage)\b/', $text);
    }

    private function isScopingNoteIntent(string $message): bool
    {
        $text = $this->normalize($message);

        return (bool) preg_match('/\b(note de cadrage|cadrage projet|synthese de cadrage|preparer ma note)\b/', $text);
    }

    private function showsStrongContactIntent(string $message): bool
    {
        $text = $this->normalize($message);

        return (bool) preg_match('/\b(contactez moi|je veux vous contacter|je souhaite vous contacter|recontactez moi|recontacte moi|recontactiez|recontacter|etre recontacte|etre recontact e|je veux etre recontacte|je veux etre recontact e|je souhaite etre contacte|etre rappele|rappelez moi|rappeler moi|prendre rendez vous|rendez vous|rdv|parler avec quelqu un|parler a un consultant|je souhaite une proposition|proposition commerciale|demande de devis|faites moi un devis|je veux un rendez vous|je veux etre contacte|appelez moi)\b/', $text);
    }

    private function showsDirectContactQuestion(string $message): bool
    {
        $text = $this->normalize($message);
        $compact = str_replace(' ', '', $text);

        $hasContactTerm = preg_match('/\b(tel|telephone|numero|mail|email|e-mail|joindre|contacter|contact)\b/', $text) === 1
            || str_contains($compact, 'telephone')
            || str_contains($compact, 'numero')
            || str_contains($compact, 'email')
            || str_contains($compact, 'contact');

        if (!$hasContactTerm) {
            return false;
        }

        return preg_match('/\b(votre|vos|comment|quel|quelle|joindre|contacter|contact)\b/', $text) === 1
            || str_contains($compact, 'votre')
            || str_contains($compact, 'comment');
    }

    private function isPositiveReply(string $message): bool
    {
        $text = trim($this->normalize($message));

        return $text !== '' && (bool) preg_match('/^(oui|oui volontiers|oui bien sur|ok|ok pour un echange|d accord|je veux bien|volontiers|avec plaisir|why not|yes|allons y|go)\b/', $text);
    }

    private function finalizeReply(string $reply, string $contactStep, ChatConversation $conversation, array $qualification): string
    {
        $reply = $this->normalizeReplyFormatting($reply);

        if ($contactStep === 'contact_info') {
            return $this->contactDetailsText(false);
        }

        if ($contactStep === 'contact_offer') {
            return $this->commercialOfferText($reply, $conversation, $qualification);
        }

        if ($contactStep === 'lead_request') {
            return $this->leadRequestText($conversation, $qualification);
        }

        return $reply;
    }

    private function commercialOfferText(string $reply, ChatConversation $conversation, array $qualification): string
    {
        $context = $this->normalize($this->visitorConversationText($conversation));
        if (preg_match('/\b(rfe|facturation electronique)\b/', $context) === 1 && preg_match('/\b(salesforce|dolibarr)\b/', $context) === 1) {
            return "Nous avons déjà de quoi cadrer un premier échange : votre projet concerne la réforme de la facturation électronique, avec Salesforce et Dolibarr dans le périmètre.\n\nLe premier travail consisterait à préciser le rôle de chaque application, cartographier les flux de facturation, fiabiliser les données clients et définir les interfaces nécessaires avec une plateforme agréée.\n\nOLING peut vous accompagner sur ce cadrage de manière indépendante, jusqu'à la définition de la trajectoire et des choix de solution.\n\nJe peux maintenant vous préparer une première note de cadrage PDF à partir de nos échanges, ou transmettre directement votre besoin à un consultant OLING.";
        }

        $reply = rtrim($reply, " \t\n\r\0\x0B?.!");
        return $reply;
    }

    private function leadRequestText(ChatConversation $conversation, array $qualification): string
    {
        if (($qualification['primary_need'] ?? null) === 'si_finance' && preg_match('/\b(rfe|facturation electronique)\b/', $this->normalize($this->visitorConversationText($conversation))) === 1) {
            return "J'ouvre la fiche projet préremplie avec le contexte AMOA SI Finance, le cadrage RFE, Salesforce, Dolibarr, les flux/interfaces et la trajectoire de mise en conformité. Vous pourrez modifier les champs avant validation.";
        }

        return "J'ouvre la fiche projet préremplie avec les éléments déjà partagés. Vous pourrez compléter vos coordonnées, modifier la demande et valider explicitement l'envoi.";
    }

    private function contactDetailsText(bool $includeLeadForm): string
    {
        return $this->contentProvider->text('copy.contact_details');
    }

    private function shouldShowLeadForm(string $contactStep): bool
    {
        return $contactStep === 'lead_request';
    }

    private function visitorConversationText(ChatConversation $conversation): string
    {
        $parts = [];
        foreach ($conversation->getMessages() as $message) {
            if ($message->getRole() === 'visitor') {
                $parts[] = (string) $message->getContent();
            }
        }

        return implode(' ', $parts);
    }

    private function asksForNamedClientOrClientList(string $message): bool
    {
        $text = $this->normalize($message);

        if (preg_match('/\b(des|plusieurs) grands? ports?\b|\b(une|des) mutuelles?\b|\b(un|des|plusieurs) organismes? de formation\b/', $text) === 1) {
            return false;
        }

        if (preg_match('/\b(quels sont vos clients|donnez moi vos principaux clients|principaux clients|noms de clients)\b/', $text) === 1) {
            return true;
        }

        if (preg_match('/\bavez vous\b.*\bavec\b/', $text) === 1) {
            return true;
        }

        return preg_match('/\b(travaille[- ]avec|travaille[- ]pour|avez[- ]vous accompagne|quel port accompagnez[- ]vous|quel client)\b/', $text) === 1;
    }

    private function confidentialityRefusal(string $message): string
    {
        $text = $this->normalize($message);

        if (preg_match('/\b(client|clients)\b/', $text) === 1) {
            return $this->contentProvider->text('copy.confidential_clients');
        }

        return $this->contentProvider->text('copy.confidential_named');
    }

    private function applyFinalSafetyGuard(string $reply): string
    {
        $normalized = $this->normalize($reply);

        if (preg_match('/\b(client|clients)\b/', $normalized) === 1 && preg_match('/\b(nom|noms|liste)\b/', $normalized) === 1) {
            return $this->contentProvider->text('copy.safety_client_names');
        }

        return $reply;
    }

    private function enforceSectorReferenceTruth(string $reply, array $documents, string $visitorMessage): string
    {
        $sector = $this->publicContentCatalog->detectSector($visitorMessage);
        if ($sector === null || $this->sectorReferenceCount($documents, $visitorMessage) === 0) {
            return $reply;
        }

        $normalized = $this->normalize($reply);
        $deniesReference = preg_match('/\b(pas de reference|aucune reference|ne dispose pas de reference|n avons pas identifie|ne documentent|pas identifie d experience|aucune experience)\b/', $normalized) === 1;
        $mentionsExperience = preg_match('/\b(reference|references|experience|experiences|intervient|accompagne)\b/', $normalized) === 1
            && str_contains($normalized, $this->normalize($sector));

        $reference = $this->firstDocumentOfType($documents, 'reference');
        $evidence = $reference === null ? '' : ' '.$this->shortEvidence($reference['text'] ?? '');
        $positive = sprintf($this->contentProvider->text('copy.sector_positive'), $sector, $evidence);

        if ($deniesReference) {
            return $positive;
        }

        if (!$mentionsExperience) {
            return $positive."\n\n".$reply;
        }

        return $reply;
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}> $documents
     * @return array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}|null
     */
    private function firstDocumentOfType(array $documents, string $type): ?array
    {
        foreach ($documents as $document) {
            if (($document['type'] ?? null) === $type) {
                return $document;
            }
        }

        return null;
    }

    private function shortEvidence(string $text): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? $text);
        if ($text === '') {
            return '';
        }

        return mb_strlen($text) <= 180 ? $text : rtrim(mb_substr($text, 0, 177)).'...';
    }

    private function normalizeReplyFormatting(string $reply): string
    {
        $reply = str_replace(["\r\n", "\r"], "\n", trim($reply));
        $lines = array_map(
            static fn (string $line): string => preg_replace('/[ \t]+/', ' ', trim($line)) ?? trim($line),
            explode("\n", $reply)
        );

        $normalized = [];
        $previousBlank = false;
        foreach ($lines as $line) {
            if ($line === '') {
                if (!$previousBlank) {
                    $normalized[] = '';
                }
                $previousBlank = true;
                continue;
            }

            $normalized[] = $line;
            $previousBlank = false;
        }

        return trim(implode("\n", $normalized));
    }

    private function logTechnicalMetrics(
        string $visitorMessage,
        array $documents,
        ChatReply $reply,
        string $provider,
        int $retrievalDurationMs,
        int $providerDurationMs,
        int $totalDurationMs,
        bool $fallbackUsed
    ): void {
        $this->logger->info('Chat technical metrics.', [
            'detected_intents' => $this->detectedIntents($visitorMessage),
            'detected_sector' => $this->publicContentCatalog->detectSector($visitorMessage),
            'canonical_sector' => $this->publicContentCatalog->detectSector($visitorMessage),
            'sector_reference_count' => $this->sectorReferenceCount($documents, $visitorMessage),
            'selected_reference_ids' => $this->selectedSourceIds($documents, 'reference'),
            'selected_service_ids' => $this->selectedSourceIds($documents, 'service'),
            'selected_expertise_ids' => $this->selectedSourceIds($documents, 'expertise'),
            'retrieval_sources_count' => count($documents),
            'no_reference_claim_allowed' => $this->sectorReferenceCount($documents, $visitorMessage) === 0,
            'intent' => $this->classificationIntent($visitorMessage),
            'retrieval_count' => count($documents),
            'retrieval_duration_ms' => $retrievalDurationMs,
            'provider' => $provider,
            'model' => $reply->model,
            'status' => $reply->status,
            'prompt_version' => AiConsultantContentProvider::VERSION,
            'provider_duration_ms' => $providerDurationMs,
            'total_duration_ms' => $totalDurationMs,
            'fallback_used' => $fallbackUsed,
            'error_code' => $reply->errorCode,
            'contact_step' => $reply->messageType,
        ]);
    }

    private function classificationIntent(string $message): string
    {
        if ($this->showsStrongContactIntent($message) || $this->showsDirectContactQuestion($message)) {
            return 'contact';
        }

        if ($this->asksForNamedClientOrClientList($message)) {
            return 'confidentiality';
        }

        $text = $this->normalize($message);

        if (preg_match('/\b(nous devons|nous voulons|notre|projet|remplacer|cahier des charges|consultation|probleme|obsolet)\b/', $text) === 1) {
            return 'project';
        }

        return 'information';
    }

    private function isExpertIntent(string $message): bool
    {
        return preg_match('/\b(qui|quel expert|quels experts|expert|consultant|equipe|profil)\b/', $this->normalize($message)) === 1;
    }

    private function isReferenceIntent(string $message): bool
    {
        return preg_match('/\b(reference|references|realisation|realisations|experience|experiences|secteur|accompagne|accompagn e)\b/', $this->normalize($message)) === 1;
    }

    private function isSectorIntent(string $message): bool
    {
        $text = $this->normalize($message);

        return $this->publicContentCatalog->detectSector($message) !== null
            || preg_match('/\b(secteur|public|services)\b/', $text) === 1;
    }

    /** @return list<string> */
    private function detectedIntents(string $message): array
    {
        $intents = [$this->classificationIntent($message)];
        $text = $this->normalize($message);
        foreach ([
            'erp' => '/\b(erp|progiciel|pgi)\b/',
            'si_client' => '/\b(si client|facturation|abonnes|usagers|portail client)\b/',
            'gmao' => '/\b(gmao|maintenance)\b/',
            'si_finance' => '/\b(si finance|finance|comptabilite|reporting)\b/',
            'pca_pra' => '/\b(pca|pra|continuite|resilience)\b/',
            'schema_directeur' => '/\b(schema directeur)\b/',
            'amoa' => '/\b(amoa|amo|assistance maitrise)\b/',
        ] as $intent => $pattern) {
            if (preg_match($pattern, $text) === 1) {
                $intents[] = $intent;
            }
        }

        return array_values(array_unique($intents));
    }

    private function sectorReferenceCount(array $documents, string $message): int
    {
        $sector = $this->publicContentCatalog->detectSector($message);
        if ($sector === null) {
            return 0;
        }

        $aliases = array_map(fn (string $alias): string => $this->normalize($alias), [$sector, ...$this->sectorTaxonomy->aliasesFor($sector)]);
        $count = 0;
        foreach ($documents as $document) {
            if (($document['type'] ?? null) !== 'reference') {
                continue;
            }
            $haystack = $this->normalize(($document['title'] ?? '').' '.($document['text'] ?? ''));
            foreach ($aliases as $alias) {
                if ($alias !== '' && str_contains($haystack, $alias)) {
                    ++$count;
                    break;
                }
            }
        }

        return $count;
    }

    /** @return list<int|string> */
    private function selectedSourceIds(array $documents, string $type): array
    {
        $ids = [];
        foreach ($documents as $document) {
            if (($document['type'] ?? null) !== $type) {
                continue;
            }
            $ids[] = $document['url'] ?? '';
        }

        return array_values(array_filter($ids));
    }

    /**
     * @param array{title:string,url:string,text:string,type:string,image:?string,excerpt:string} $document
     */
    private function sourceDisplayScore(array $document, string $visitorMessage): int
    {
        $queryTokens = $this->queryTokens($visitorMessage);
        if ($queryTokens === []) {
            return 0;
        }

        $titleTokens = $this->tokenSet((string) ($document['title'] ?? ''));
        $textTokens = $this->tokenSet((string) ($document['text'] ?? ''));
        $urlTokens = $this->tokenSet((string) ($document['url'] ?? ''));
        $score = 0;
        foreach ($queryTokens as $token) {
            if (isset($textTokens[$token])) {
                $score += 3;
            }
            if (isset($titleTokens[$token])) {
                $score += 4;
            }
            if (isset($urlTokens[$token])) {
                $score += 2;
            }
        }

        if (($document['type'] ?? null) === 'reference' && $this->isReferenceIntent($visitorMessage)) {
            $score += 4;
        }

        if (($document['type'] ?? null) === 'page' && $this->isSectorIntent($visitorMessage)) {
            $score += 5;
        }

        if (($document['type'] ?? null) === 'reference' && $this->isSectorIntent($visitorMessage)) {
            $score += 3;
        }

        if (($document['type'] ?? null) === 'team' && $this->isExpertIntent($visitorMessage)) {
            $score += 10;
        }

        return $score;
    }

    /**
     * @return string[]
     */
    private function queryTokens(string $message): array
    {
        $tokens = preg_split('/[^a-z0-9]+/i', $this->normalize($message)) ?: [];
        $tokens = array_values(array_filter($tokens, function (string $token): bool {
            if (strlen($token) < 3) {
                return false;
            }

            return !in_array($token, [
                'quel',
                'quelle',
                'quels',
                'quelles',
                'avec',
                'dans',
                'pour',
                'vous',
                'votre',
                'notre',
                'offre',
                'sujet',
            ], true);
        }));

        return array_values(array_unique(array_slice($tokens, 0, 10)));
    }

    /**
     * @return array<string, true>
     */
    private function tokenSet(string $value): array
    {
        $tokens = preg_split('/[^a-z0-9]+/i', $this->normalize($value)) ?: [];
        $set = [];
        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }

            $set[$token] = true;
        }

        return $set;
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}> ...$sets
     * @return array<int, array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}>
     */
    private function mergeDocumentsByUrl(array ...$sets): array
    {
        $merged = [];
        foreach ($sets as $documents) {
            foreach ($documents as $document) {
                $url = $document['url'] ?? null;
                if (!is_string($url) || $url === '' || isset($merged[$url])) {
                    continue;
                }

                $merged[$url] = $document;
            }
        }

        return array_values($merged);
    }

    private function normalize(string $value): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($normalized === false) {
            $normalized = $value;
        }

        $normalized = strtolower($normalized);

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $normalized) ?? $normalized);
    }
}
