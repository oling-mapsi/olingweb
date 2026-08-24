<?php

namespace App\Service\Chat\Ai;

use App\Entity\ChatConversation;
use App\Service\Chat\ChatQualificationService;

class HeuristicAiProvider implements AiProviderInterface
{
    public function __construct(private readonly ChatQualificationService $qualificationService)
    {
    }

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
        $reply = trim($this->buildLeadSentence($visitorMessage, $documents, $qualification).' '.$this->nextQuestion($qualification, $missingFields));

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
     */
    private function buildLeadSentence(string $visitorMessage, array $documents, array $qualification): string
    {
        if ($this->looksLikeGreeting($visitorMessage) && $this->qualificationService->isTooVague($qualification)) {
            return 'Bonjour.';
        }

        if ($documents !== [] && !$this->qualificationService->isTooVague($qualification)) {
            $titles = array_slice(array_map(static fn (array $document): string => $document['title'], $documents), 0, 1);

            return 'Je pense notamment à '.implode(' et ', $titles).'.';
        }

        $primaryNeed = $qualification['primary_need'] ?? null;
        if ($primaryNeed !== null) {
            return match ($primaryNeed) {
                'amoa_erp' => 'OLING intervient sur le cadrage et la sécurisation des projets ERP et applicatifs métiers.',
                'rgpd' => 'OLING intervient sur les sujets de gouvernance RGPD, DPO et conformité associée.',
                'cybersecurite' => 'OLING intervient sur la sécurité, la résilience et la réduction du risque opérationnel.',
                'ia_data_automatisation' => 'OLING intervient sur les sujets data, automatisation et usage raisonné de l’IA.',
                'conformite' => 'OLING intervient sur les dispositifs de conformité et de pilotage des risques.',
                'organisation_gouvernance' => 'OLING intervient sur les sujets d’organisation, de gouvernance et de pilotage.',
                'transformation_si' => 'OLING intervient sur les trajectoires de transformation SI et d’alignement métier.',
                default => '',
            };
        }

        if ($this->qualificationService->isTooVague($qualification)) {
            return 'Je peux vous aider à cadrer le sujet.';
        }

        return 'Je peux vous aider à clarifier le besoin.';
    }

    /**
     * @param array<string, string|null> $qualification
     * @param string[] $missingFields
     */
    private function nextQuestion(array $qualification, array $missingFields): string
    {
        if ($this->qualificationService->isTooVague($qualification)) {
            return 'Pour bien cadrer: votre sujet porte surtout sur un outil, une organisation, une contrainte réglementaire ou un risque ?';
        }

        if (in_array('primary_need', $missingFields, true)) {
            return 'On parle plutôt de transformation SI, d’ERP, de conformité, de cybersécurité, de RGPD ou d’IA/data ?';
        }

        if (in_array('urgency_level', $missingFields, true)) {
            return 'Quel est votre horizon: immédiat, ce trimestre, dans les prochains mois, ou plutôt exploratoire ?';
        }

        if (in_array('maturity_level', $missingFields, true)) {
            return 'Où en êtes-vous aujourd’hui: réflexion, cadrage, consultation, projet en cours ou blocage ?';
        }

        if (in_array('organization_type', $missingFields, true)) {
            return 'Votre structure est plutôt une PME, une PMI, une ETI, un acteur public, une association, ou autre ?';
        }

        if (in_array('organization_size', $missingFields, true)) {
            return 'Quel est l’ordre de grandeur de votre structure: moins de 50, 50 à 250, 250 à 1000 personnes, ou davantage ?';
        }

        return 'Quel est le point métier ou opérationnel le plus important à sécuriser ?';
    }

    private function looksLikeGreeting(string $message): bool
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $message);
        if ($normalized === false) {
            $normalized = $message;
        }

        return (bool) preg_match('/^\s*(bonjour|bonsoir|salut|hello|coucou)\b/i', strtolower($normalized));
    }
}
