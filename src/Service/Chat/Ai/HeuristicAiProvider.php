<?php

namespace App\Service\Chat\Ai;

use App\Entity\ChatConversation;
use App\Service\Chat\AiConsultantContentProvider;
use App\Service\Chat\ChatQualificationService;

class HeuristicAiProvider implements AiProviderInterface
{
    public function __construct(
        private readonly ChatQualificationService $qualificationService,
        private readonly AiConsultantContentProvider $contentProvider
    ) {
        $this->sectorTaxonomy = new \App\Service\Chat\SectorTaxonomy();
    }

    private readonly \App\Service\Chat\SectorTaxonomy $sectorTaxonomy;

    public function getName(): string
    {
        return 'heuristic';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function generateDecision(
        ChatConversation $conversation,
        string $visitorMessage,
        array $documents,
        array $qualification
    ): AiDecision {
        $missingFields = $qualification['missing_fields'] ?? [];
        if (!is_array($missingFields)) {
            $missingFields = [];
        }

        $qualification = $this->qualificationService->sanitizeQualification($qualification);
        $reply = trim($this->buildReply($visitorMessage, $documents, $qualification, $missingFields));

        return new AiDecision(
            $reply,
            false,
            $qualification,
            array_column($documents, 'url'),
            $missingFields,
            null,
            $this->getName()
        );
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     * @param array<string, string|null> $qualification
     * @param string[] $missingFields
     */
    private function buildReply(string $visitorMessage, array $documents, array $qualification, array $missingFields): string
    {
        if ($this->asksForNamedClientOrClientList($visitorMessage)) {
            return $this->confidentialityRefusal($visitorMessage);
        }

        if ($this->isDirectContactQuestion($visitorMessage)) {
            return $this->formatBulletReply(
                $this->hText('contact.intro'),
                $this->hList('contact.items')
            );
        }

        if ($this->looksLikeGreeting($visitorMessage) && $this->qualificationService->isTooVague($qualification)) {
            return $this->hText('greeting');
        }

        if ($this->isAmoaIso27001Blend($visitorMessage)) {
            return $this->hText('amoa_iso27001_blend');
        }

        if ($this->asksForErpQuestionnaire($visitorMessage, $qualification)) {
            return $this->buildErpQuestionnaireReply();
        }

        if ($this->asksForSectorCoverage($visitorMessage)) {
            return $this->buildSectorCoverageReply($visitorMessage, $documents, $qualification);
        }

        if ($this->sectorTaxonomy->detect($visitorMessage) !== null && $this->mentionsAmoaProgiciel($visitorMessage)) {
            return $this->buildSectorCoverageReply($visitorMessage, $documents, $qualification);
        }

        if ($this->asksForReferences($visitorMessage) || $this->shouldPreferReferenceReply($visitorMessage, $documents)) {
            return $this->buildReferenceReply($visitorMessage, $documents, $qualification);
        }

        if ($this->isInformationRequest($visitorMessage)) {
            if ($this->asksForDomainExpertise($visitorMessage)) {
                return $this->buildDomainExpertReply($visitorMessage, $documents, $qualification);
            }

            if ($this->asksForCadrageDeliverables($visitorMessage)) {
                return $this->buildCadrageDeliverablesReply($visitorMessage, $documents, $qualification);
            }

            return $this->buildInformationReply($visitorMessage, $documents, $qualification);
        }

        $analysis = $this->buildProjectAnalysis($documents, $qualification);
        $question = $this->nextUsefulQuestion($visitorMessage, $qualification, $missingFields);

        return trim($analysis.' '.$question);
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     * @param array<string, string|null> $qualification
     */
    private function buildInformationReply(string $visitorMessage, array $documents, array $qualification): string
    {
        if ($documents !== []) {
            $reference = $this->firstDocumentOfType($documents, 'reference');
            $expertise = $this->firstDocumentOfTypes($documents, ['expertise', 'service', 'page']);
            $team = $this->firstDocumentOfType($documents, 'team');

            if ($this->asksForReferences($visitorMessage) && $reference !== null) {
                $reply = $this->formatBulletReply(
                    $this->hText('reference.with_reference'),
                    [$this->excerptSentence($reference['text'])]
                );
                if ($expertise !== null) {
                    $reply .= "\n".$this->bulletLine($this->bridgeSentence($expertise['text']));
                }

                return $reply;
            }

            if ($this->asksForExpert($visitorMessage) && $team !== null) {
                $reply = $this->formatBulletReply(
                    $this->hText('information.expert_intro'),
                    [$this->excerptSentence($team['text'])]
                );
                if ($expertise !== null) {
                    $reply .= "\n".$this->bulletLine($this->bridgeSentence($expertise['text']));
                }

                return $reply;
            }

            $first = $documents[0];
            $reply = $this->excerptSentence($first['text']);
            if ($expertise !== null && ($first['url'] ?? '') !== ($expertise['url'] ?? '')) {
                $reply .= ' '.$this->bridgeSentence($expertise['text']);
            }

            return $reply;
        }

        $primaryNeed = $qualification['primary_need'] ?? null;
        if ($primaryNeed !== null) {
            return $this->hMap('information.primary_need')[$primaryNeed] ?? '';
        }

        return $this->hText('information.default');
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     * @param array<string, string|null> $qualification
     */
    private function buildSectorCoverageReply(string $visitorMessage, array $documents, array $qualification): string
    {
        $sectorLabel = $this->detectSectorLabel($visitorMessage) ?? $this->hText('sector.generic');
        $reference = $this->firstDocumentOfType($documents, 'reference');
        $page = $this->firstDocumentOfType($documents, 'page');
        $service = $this->firstDocumentOfTypes($documents, ['service', 'expertise']);
        $items = [];

        if ($reference !== null) {
            $items[] = $this->excerptSentence($reference['text']);
        }

        if ($page !== null && count($items) < 2) {
            $items[] = $this->excerptSentence($page['text']);
        }

        if ($service !== null && count($items) < 3) {
            $items[] = $this->bridgeSentence($service['text']);
        }

        if ($items !== []) {
            return $this->formatBulletReply(
                ($this->asksForReferences($visitorMessage) || $reference !== null)
                    ? sprintf($this->hText('sector.with_references'), $sectorLabel)
                    : sprintf($this->hText('sector.also'), $sectorLabel),
                $items
            )."\n\n".$this->hText('sector.next_step');
        }

        if (($qualification['primary_need'] ?? null) === 'amoa_erp') {
            return sprintf($this->hText('sector.amoa'), $sectorLabel);
        }

        return sprintf($this->hText('sector.default'), $sectorLabel);
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     * @param array<string, string|null> $qualification
     */
    private function buildCadrageDeliverablesReply(string $visitorMessage, array $documents, array $qualification): string
    {
        $track = $this->detectAmoaTrack($visitorMessage, $documents, $qualification);

        return $this->twoPartReply('cadrage.'.($this->hasHeuristicBlock('cadrage.'.$track) ? $track : 'default'));
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     * @param array<string, string|null> $qualification
     */
    private function buildReferenceReply(string $visitorMessage, array $documents, array $qualification): string
    {
        $referenceDocuments = array_values(array_filter(
            $documents,
            static fn (array $document): bool => ($document['type'] ?? '') === 'reference'
        ));

        if ($referenceDocuments !== []) {
            $lead = $this->excerptSentence($referenceDocuments[0]['text']);

            if ($this->mentionsAmoaProgiciel($visitorMessage)) {
                return $this->formatBulletReply(
                    $this->hText('reference.with_amoa'),
                    [$lead]
                );
            }

            return $this->formatBulletReply(
                $this->hText('reference.with_reference'),
                [$lead]
            );
        }

        if (($qualification['primary_need'] ?? null) === 'amoa_erp') {
            return $this->hText('reference.amoa_no_doc');
        }

        return $this->hText('reference.default_no_doc');
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     * @param array<string, string|null> $qualification
     */
    private function buildDomainExpertReply(string $visitorMessage, array $documents, array $qualification): string
    {
        $domain = $this->detectExpertDomain($visitorMessage, $documents, $qualification);

        if (!$this->hasHeuristicBlock('domain.'.$domain)) {
            return $this->buildInformationReply($visitorMessage, $documents, $qualification);
        }

        return $this->twoPartReply('domain.'.$domain);
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     * @param array<string, string|null> $qualification
     */
    private function buildProjectAnalysis(array $documents, array $qualification): string
    {
        if ($documents !== []) {
            $lead = $this->excerptSentence($documents[0]['text']);
            $support = $this->firstDocumentOfTypes(array_slice($documents, 1), ['service', 'expertise', 'reference', 'team', 'page']);

            if ($support !== null) {
                return $this->formatBulletReply(
                    $this->hText('project_analysis.intro'),
                    [
                        $lead,
                        $this->bridgeSentence($support['text']),
                    ]
                );
            }

            return $lead;
        }

        $primaryNeed = $qualification['primary_need'] ?? null;

        return $this->hMap('project_analysis.primary_need')[$primaryNeed] ?? $this->hText('project_analysis.default');
    }

    /**
     * @param array<string, string|null> $qualification
     * @param string[] $missingFields
     */
    private function nextUsefulQuestion(string $visitorMessage, array $qualification, array $missingFields): string
    {
        if (($qualification['primary_need'] ?? null) === 'amoa_erp') {
            return $this->hText('next_question.amoa_erp');
        }

        if ($this->qualificationService->isTooVague($qualification)) {
            return $this->hText('next_question.too_vague');
        }

        if ($this->isInformationRequest($visitorMessage)) {
            return '';
        }

        if (in_array('primary_need', $missingFields, true)) {
            return $this->hText('next_question.missing_primary_need');
        }

        if (preg_match('/\b(remplacer|obsolete|obsolescent|migration)\b/', $this->normalize($visitorMessage)) === 1) {
            return $this->hText('next_question.replacement');
        }

        return $this->hText('next_question.default');
    }

    private function looksLikeGreeting(string $message): bool
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $message);
        if ($normalized === false) {
            $normalized = $message;
        }

        return (bool) preg_match('/^\s*(bonjour|bonsoir|salut|hello|coucou)\b/i', strtolower($normalized));
    }

    private function isShortKeywordQuery(string $message): bool
    {
        $normalized = $this->normalize($message);
        $tokens = preg_split('/[^a-z0-9]+/', $normalized) ?: [];
        $tokens = array_values(array_filter($tokens, static fn (string $token): bool => $token !== ''));

        return count($tokens) <= 4 && mb_strlen(trim($message)) <= 32;
    }

    private function isAmoaIso27001Blend(string $message): bool
    {
        $normalized = $this->normalize($message);

        return preg_match('/\b(amoa|amo|erp|applicatif|outil)\b/', $normalized) === 1
            && preg_match('/\b(iso27001|iso 27001|smsi|ssi|cyber|securite)\b/', $normalized) === 1;
    }

    private function normalize(string $message): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $message);
        if ($normalized === false) {
            $normalized = $message;
        }

        $normalized = strtolower($normalized);

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $normalized) ?? $normalized);
    }

    private function isInformationRequest(string $message): bool
    {
        $normalized = $this->normalize($message);

        if (preg_match('/\b(contactez moi|rendez vous|rdv|devis)\b/', $normalized) === 1) {
            return false;
        }

        if (preg_match('/\b(nous devons|nous voulons|notre|projet|remplacer|cahier des charges|consultation|probleme|obsolet)\b/', $normalized) === 1) {
            return false;
        }

        return preg_match('/\b(que|quoi|quelles|quelle|quels|qui|avez vous|connaissez vous|difference|faites vous)\b/', $normalized) === 1
            || $this->isShortKeywordQuery($message);
    }

    private function asksForReferences(string $message): bool
    {
        $text = $this->normalize($message);

        foreach (['reference', 'references', 'retour experience', 'retours experience', 'experience', 'experiences', 'secteur'] as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return str_contains($text, 'avez vous deja fait') || str_contains($text, 'avez vous deja accompagne');
    }

    private function mentionsAmoaProgiciel(string $message): bool
    {
        $text = $this->normalize($message);

        return preg_match('/\b(amoa|amo|progiciel|erp|gmao|crm|sirh|facturation)\b/', $text) === 1;
    }

    private function asksForExpert(string $message): bool
    {
        return preg_match('/\b(qui|quel expert|quels experts|expert|consultant|profil|equipe)\b/', $this->normalize($message)) === 1;
    }

    private function asksForDomainExpertise(string $message): bool
    {
        $text = $this->normalize($message);

        return preg_match('/\b(qse|qualiopi|iso 9001|iso 14001|iso 45001|conformite|rgpd|dpo|cyber|securite|iso27001|iso 27001|smsi|nis2|dora|pca|pra|resilience|ai act)\b/', $text) === 1;
    }

    private function asksForSectorCoverage(string $message): bool
    {
        $text = $this->normalize($message);

        return $this->sectorTaxonomy->detect($message) !== null
            || preg_match('/\b(secteur|public|services b2b|secteur des services)\b/', $text) === 1;
    }

    private function asksForCadrageDeliverables(string $message): bool
    {
        $text = $this->normalize($message);

        return preg_match('/\b(cadrage|livrable|livrables|note de cadrage|expression des besoins|cahier des charges|macro planning|gouvernance projet|recette|migration|reprise)\b/', $text) === 1;
    }

    /**
     * @param array<string, string|null> $qualification
     */
    private function asksForErpQuestionnaire(string $message, array $qualification): bool
    {
        $text = $this->normalize($message);

        return preg_match('/\b(questionnaire|qualifier|qualification)\b/', $text) === 1
            && (
                preg_match('/\b(erp|progiciel|applicatif|application metier|logiciel metier|sirh|sage|sap)\b/', $text) === 1
                || ($qualification['primary_need'] ?? null) === 'amoa_erp'
            );
    }

    private function buildErpQuestionnaireReply(): string
    {
        return $this->formatBulletReply(
            $this->hText('erp_questionnaire.intro'),
            $this->hList('erp_questionnaire.items')
        )."\n\n".$this->hText('erp_questionnaire.first_question');
    }

    private function isDirectContactQuestion(string $message): bool
    {
        $text = $this->normalize($message);
        $compact = str_replace(' ', '', $text);

        $hasContactTerm = preg_match('/\b(tel|telephone|numero|mail|email|e-mail|joindre|contacter|contact)\b/', $text) === 1
            || str_contains($compact, 'telephone')
            || str_contains($compact, 'numero')
            || str_contains($compact, 'email')
            || str_contains($compact, 'contact');

        $hasQuestionTerm = preg_match('/\b(votre|vos|comment|quel|quelle|joindre|contacter|contact)\b/', $text) === 1
            || str_contains($compact, 'votre')
            || str_contains($compact, 'comment');

        return $hasContactTerm && $hasQuestionTerm;
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     */
    private function shouldPreferReferenceReply(string $message, array $documents): bool
    {
        if ($documents === []) {
            return false;
        }

        if ($this->asksForSectorCoverage($message) && !$this->asksForReferences($message)) {
            return false;
        }

        $topType = $documents[0]['type'] ?? '';
        if ($topType !== 'reference') {
            return false;
        }

        $text = $this->normalize($message);

        return $this->isShortKeywordQuery($message)
            || str_contains($text, 'eau')
            || str_contains($text, 'assainissement')
            || $this->mentionsAmoaProgiciel($message);
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
            return $this->hText('confidential.clients');
        }

        return $this->hText('confidential.named');
    }

    private function excerptSentence(string $text): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', $text) ?? $text);
        if ($clean === '') {
            return $this->hText('excerpt_fallback');
        }

        if (mb_strlen($clean) <= 220) {
            return $clean;
        }

        return rtrim(mb_substr($clean, 0, 217)).'...';
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     * @return array{title:string,url:string,text:string,type:string}|null
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

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     * @param string[] $types
     * @return array{title:string,url:string,text:string,type:string}|null
     */
    private function firstDocumentOfTypes(array $documents, array $types): ?array
    {
        foreach ($documents as $document) {
            if (in_array($document['type'] ?? null, $types, true)) {
                return $document;
            }
        }

        return null;
    }

    private function bridgeSentence(string $text): string
    {
        return sprintf($this->hText('bridge'), $this->lowercaseFirst($this->excerptSentence($text)));
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     * @param array<string, string|null> $qualification
     */
    private function detectAmoaTrack(string $message, array $documents, array $qualification): string
    {
        $text = $this->normalize($message);

        if (preg_match('/\b(crm|relation client|vente|commercial|salesforce)\b/', $text) === 1) {
            return 'crm';
        }

        if (preg_match('/\b(gmao|maintenance|equipement|actif|intervention)\b/', $text) === 1) {
            return 'gmao';
        }

        if (preg_match('/\b(finance|comptabilite|reporting|controle de gestion|facturation)\b/', $text) === 1) {
            return 'si_finance';
        }

        foreach ($documents as $document) {
            $haystack = $this->normalize(($document['title'] ?? '').' '.($document['text'] ?? ''));
            if (preg_match('/\b(erp|progiciel|applicatif metier|application metier)\b/', $haystack) === 1) {
                return 'erp';
            }
            if (preg_match('/\b(crm|relation client|vente|commercial|salesforce)\b/', $haystack) === 1) {
                return 'crm';
            }
            if (preg_match('/\b(gmao|maintenance|equipement|actif|intervention)\b/', $haystack) === 1) {
                return 'gmao';
            }
            if (preg_match('/\b(si finance|finance|comptabilite|reporting|controle de gestion)\b/', $haystack) === 1) {
                return 'si_finance';
            }
        }

        return ($qualification['primary_need'] ?? null) === 'amoa_erp' ? 'erp' : 'transformation_si';
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     * @param array<string, string|null> $qualification
     */
    private function detectExpertDomain(string $message, array $documents, array $qualification): string
    {
        $text = $this->normalize($message);
        $haystack = $text;
        foreach ($documents as $document) {
            $haystack .= ' '.$this->normalize(($document['title'] ?? '').' '.($document['text'] ?? ''));
        }

        if (preg_match('/\b(ai act|conformite ia|gouvernance ia)\b/', $haystack) === 1) {
            return 'ia_conformite';
        }

        if (preg_match('/\b(rgpd|dpo|donnees personnelles|aipd|dpia|cnil)\b/', $haystack) === 1 || ($qualification['primary_need'] ?? null) === 'rgpd') {
            return 'rgpd';
        }

        if (preg_match('/\b(qse|qualiopi|iso 9001|iso 14001|iso 45001|qualite|environnement|securite au travail)\b/', $haystack) === 1) {
            return 'qse';
        }

        if (preg_match('/\b(cyber|securite|iso27001|iso 27001|smsi|nis2|dora|pca|pra|resilience|continuit[e]?)\b/', $haystack) === 1 || ($qualification['primary_need'] ?? null) === 'cybersecurite') {
            return 'cyber';
        }

        if (($qualification['primary_need'] ?? null) === 'conformite') {
            return 'qse';
        }

        return 'generic';
    }

    private function detectSectorLabel(string $message): ?string
    {
        return $this->sectorTaxonomy->detect($message);
    }

    /**
     * @param string[] $items
     */
    private function formatBulletReply(string $intro, array $items): string
    {
        $lines = [rtrim($intro)];
        foreach ($items as $item) {
            $item = trim($item);
            if ($item === '') {
                continue;
            }

            $lines[] = $this->bulletLine($item);
        }

        return implode("\n", $lines);
    }

    private function bulletLine(string $text): string
    {
        return '- '.$text;
    }

    private function hText(string $path): string
    {
        return $this->contentProvider->text('heuristic.'.$path);
    }

    /**
     * @return string[]
     */
    private function hList(string $path): array
    {
        return $this->contentProvider->list('heuristic.'.$path);
    }

    /**
     * @return array<string, string>
     */
    private function hMap(string $path): array
    {
        return $this->contentProvider->map('heuristic.'.$path);
    }

    private function hasHeuristicBlock(string $path): bool
    {
        return $this->hText($path.'.approach.intro') !== ''
            && $this->hList($path.'.approach.items') !== []
            && $this->hText($path.'.deliverables.intro') !== ''
            && $this->hList($path.'.deliverables.items') !== [];
    }

    private function twoPartReply(string $path): string
    {
        return $this->formatBulletReply(
            $this->hText($path.'.approach.intro'),
            $this->hList($path.'.approach.items')
        )."\n\n".$this->formatBulletReply(
            $this->hText($path.'.deliverables.intro'),
            $this->hList($path.'.deliverables.items')
        );
    }

    private function lowercaseFirst(string $text): string
    {
        $first = mb_substr($text, 0, 1);

        return mb_strtolower($first).mb_substr($text, 1);
    }
}
