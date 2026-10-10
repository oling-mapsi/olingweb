<?php

namespace App\Service\Chat;

use App\Entity\ChatConversation;

class ChatQualificationService
{
    /**
     * @return array<string, string|null>
     */
    public function qualify(ChatConversation $conversation, array $modelHints = []): array
    {
        $text = $this->conversationText($conversation);

        $stage = $this->commercialStage($text);
        $qualification = [
            'primary_need' => $this->matchPrimaryNeed($text),
            'urgency_level' => $this->matchUrgency($text),
            'maturity_level' => $this->matchMaturity($text),
            'organization_type' => $this->matchOrganizationType($text),
            'organization_size' => $this->matchOrganizationSize($text),
            'commercial_intent' => $this->matchCommercialIntent($text),
            'commercial_stage' => $stage,
        ];

        $qualification = $this->mergeHints($qualification, $modelHints);
        $qualification['potential_value'] = $this->matchPotentialValue($qualification, $text);
        $qualification['missing_fields'] = $this->missingFields($qualification);
        $qualification['detail_score'] = (string) $this->detailScore($qualification, $text);
        $qualification['is_vague'] = $this->detailScore($qualification, $text) <= 1 ? '1' : '0';

        return $qualification;
    }

    /**
     * @param array<string, string|null> $qualification
     */
    public function isReadyForLead(array $qualification, ?ChatConversation $conversation = null): bool
    {
        if (($qualification['commercial_stage'] ?? null) === 'intent_contact') {
            return true;
        }

        if (($qualification['commercial_stage'] ?? null) === 'identified_need' && !empty($qualification['primary_need']) && !empty($qualification['commercial_intent'])) {
            return true;
        }

        foreach (['primary_need', 'maturity_level', 'commercial_intent'] as $field) {
            if (empty($qualification[$field])) {
                return false;
            }
        }

        $score = (int) ($qualification['detail_score'] ?? 0);
        if ($score < 3 && ($qualification['commercial_stage'] ?? null) !== 'qualified_opportunity') {
            return false;
        }

        if ($conversation === null) {
            return true;
        }

        $visitorMessages = 0;
        foreach ($conversation->getMessages() as $message) {
            if ($message->getRole() === 'visitor') {
                ++$visitorMessages;
            }
        }

        return $visitorMessages >= 1;
    }

    /**
     * @param array<string, string|null> $qualification
     * @return array<string, string|null>
     */
    public function sanitizeQualification(array $qualification): array
    {
        $allowed = $this->allowedValues();
        $sanitized = [];

        foreach ($allowed as $field => $values) {
            $value = $qualification[$field] ?? null;
            $sanitized[$field] = in_array($value, $values, true) ? $value : null;
        }

        if (isset($qualification['missing_fields']) && is_array($qualification['missing_fields'])) {
            $sanitized['missing_fields'] = array_values(array_filter(
                $qualification['missing_fields'],
                static fn (mixed $field): bool => is_string($field)
            ));
        }

        if (isset($qualification['detail_score'])) {
            $sanitized['detail_score'] = (string) max(0, min(6, (int) $qualification['detail_score']));
        }

        if (isset($qualification['is_vague'])) {
            $sanitized['is_vague'] = $qualification['is_vague'];
        }

        return $sanitized;
    }

    /**
     * @param array<string, string|null> $qualification
     */
    public function isTooVague(array $qualification): bool
    {
        return ($qualification['is_vague'] ?? '0') === '1';
    }

    private function conversationText(ChatConversation $conversation): string
    {
        $parts = [];
        foreach ($conversation->getMessages() as $message) {
            if ($message->getRole() === 'visitor') {
                $parts[] = (string) $message->getContent();
            }
        }

        return $this->normalize(implode(' ', $parts));
    }

    private function matchPrimaryNeed(string $text): ?string
    {
        if (preg_match('/\b(rfe|facturation electronique)\b/', $text) === 1
            && preg_match('/\b(amoa|cadrage|amont|salesforce|dolibarr|erp|crm|si finance)\b/', $text) === 1) {
            return 'si_finance';
        }

        if (preg_match('/\b(si finance)\b/', $text) === 1
            && preg_match('/\b(amoa|cadrage|amont|salesforce|dolibarr|erp|crm)\b/', $text) === 1) {
            return 'si_finance';
        }

        if (preg_match('/\b(amoa\s+erp|erp|progiciel)\b/', $text) === 1
            && preg_match('/\b(cadrage|mission|processus|achats|approvisionnements|stocks|production|qualite|consultation|livrables)\b/', $text) === 1) {
            return 'amoa_erp';
        }

        $map = [
            'gmao' => ['gmao', 'maintenance', 'equipements', 'ordres de travail'],
            'si_finance' => ['si finance', 'comptabilite', 'reporting financier', 'cloture'],
            'rfe' => ['facturation electronique', 'rfe', 'pdp', 'plateforme de dematerialisation'],
            'crm' => ['crm', 'relation client', 'salesforce', 'ventes', 'force commerciale'],
            'amoa_erp' => ['amoa', 'amo', 'erp', 'sage', 'sap', 'progiciel', 'applicatif', 'application metier', 'applications metiers'],
            'rgpd' => ['rgpd', 'cnil', 'dpo', 'donnees personnelles', 'donnees perso'],
            'pca_pra' => ['pca', 'pra', 'continuite', 'resilience', 'iso 22301', 'iso22301'],
            'cybersecurite' => ['cyber', 'securite', 'ransomware', 'rancongiciel', 'iso27001', 'iso 27001', 'ssi', 'smsi', 'nis2', 'dora'],
            'grc_mapsi' => ['mapsi', 'grc', 'controle interne', 'gestion des risques', 'plan d actions', 'plans d actions'],
            'qse' => ['qse', 'qualite', 'iso 9001', 'iso9001', 'iso 14001', 'qualiopi'],
            'ia_data_automatisation' => ['ia', 'intelligence artificielle', 'data', 'automatisation', 'reporting', 'bi', 'power bi'],
            'conformite' => ['conformite', 'audit', 'controle interne', 'reglementaire', 'iso', 'qse', 'qualiopi', 'iso 9001', 'iso 14001', 'iso 45001', 'ai act'],
            'organisation_gouvernance' => ['gouvernance', 'organisation', 'pilotage', 'comite', 'process', 'operating model'],
            'transformation_si' => ['transformation si', 'schema directeur', 'urbanisation', 'systeme information', 'transformation numerique', 'dsi de transition', 'dsi externalisee'],
        ];

        foreach ($map as $label => $keywords) {
            foreach ($keywords as $keyword) {
                if ($keyword === 'crm' && str_contains($text, 'si client') && !preg_match('/\b(crm|salesforce|relation client|ventes|commercial)\b/', $text)) {
                    continue;
                }

                if (str_contains($text, $keyword)) {
                    return $label;
                }
            }
        }

        return null;
    }

    private function matchUrgency(string $text): ?string
    {
        if (preg_match('/urgent|immediat|cette semaine|sous 15 jours|bloquant/', $text)) {
            return 'immediate';
        }
        if (preg_match('/ce mois|ce trimestre|sous 2 mois|8 semaines/', $text)) {
            return 'short_term';
        }
        if (preg_match('/prochains mois|semestre|cette annee|planifie/', $text)) {
            return 'planned';
        }
        if (preg_match('/reflexion|exploratoire|veille|amont|cadrage/', $text)) {
            return 'exploratory';
        }

        return null;
    }

    private function matchMaturity(string $text): ?string
    {
        if (preg_match('/bloque|retard|derive|incident|crise/', $text)) {
            return 'bloque';
        }
        if (preg_match('/projet en cours|deploiement|implementation|lance/', $text)) {
            return 'en_cours';
        }
        if (preg_match('/consultation|appel d offres|cahier des charges/', $text)) {
            return 'consultation';
        }
        if (preg_match('/cadrage|qualification|diagnostic/', $text)) {
            return 'cadre';
        }
        if (preg_match('/conforme|conformite|mise en conformite|certification|remplacer|migration|consultant|prestataire|accompagnement|assistance/', $text)) {
            return 'cadre';
        }
        if (preg_match('/reflexion|idee|amont|pas encore defini/', $text)) {
            return 'flou';
        }

        return null;
    }

    private function matchOrganizationType(string $text): ?string
    {
        if (preg_match('/pme/', $text)) {
            return 'pme';
        }
        if (preg_match('/pmi|industrie/', $text)) {
            return 'pmi';
        }
        if (preg_match('/eti/', $text)) {
            return 'eti';
        }
        if (preg_match('/collectivite|public|administration|hopital/', $text)) {
            return 'public';
        }
        if (preg_match('/association/', $text)) {
            return 'association';
        }

        return null;
    }

    private function matchOrganizationSize(string $text): ?string
    {
        if (preg_match('/\b([1-4]?\d)\s*(salaries|personnes)\b/', $text)) {
            return '1_49';
        }
        if (preg_match('/\b([5-9]\d|1\d\d|2[0-4]\d)\s*(salaries|personnes)\b/', $text)) {
            return '50_249';
        }
        if (preg_match('/\b([2-9]\d\d)\s*(salaries|personnes)\b/', $text)) {
            return '250_999';
        }
        if (preg_match('/\b([1-9]\d{3,})\s*(salaries|personnes)\b/', $text)) {
            return '1000_plus';
        }

        return null;
    }

    private function matchCommercialIntent(string $text): ?string
    {
        if (preg_match('/\b(proposition commerciale|demande de proposition|offre commerciale|recevoir une proposition|methodologie|livrables|nombre de jours|jours homme|references|cout|co[uû]t|budget|prix|tarif)\b/', $text)) {
            return preg_match('/\b(devis|prix|tarif|cout|co[uû]t|budget)\b/', $text) ? 'quote_request' : 'proposal_request';
        }
        if (preg_match('/devis|prix|tarif|combien/', $text)) {
            return 'devis';
        }
        if (preg_match('/demo|demonstration|d emonstration/', $text)) {
            return 'demo_logiciel';
        }
        if (preg_match('/contact|rendez vous|rdv|rappelez moi|recontact/', $text)) {
            return 'contact';
        }
        if (preg_match('/diagnostic|audit/', $text)) {
            return 'diagnostic';
        }
        if (preg_match('/cadrage|amont|roadmap|schema directeur/', $text)) {
            return 'cadrage';
        }
        if (preg_match('/projet en cours|assistance|pilotage|accompagnement|accompagner|prestataire|consultant|amoa|amo|devis|proposition|prise de contact|contactez moi|rappelez moi/', $text)) {
            return 'assistance_projet';
        }
        if (preg_match('/conformite|conformes|mise en conformite|dora|nis2/', $text)) {
            return 'mise_en_conformite';
        }
        if (preg_match('/expertise|experience|avis/', $text)) {
            return 'expertise_ponctuelle';
        }

        return !empty($text) ? 'information' : null;
    }

    private function commercialStage(string $text): string
    {
        if (preg_match('/\b(contact|rendez vous|rdv|devis|proposition commerciale|demande de proposition|offre commerciale|rappelez|recontact|appelez)\b/', $text) === 1) {
            return 'intent_contact';
        }

        $hasNeed = $this->matchPrimaryNeed($text) !== null;
        $hasProject = preg_match('/\b(projet|cadrage|amont|remplacer|recherchons|cherchons|besoin|devons|souhaitons|pouvez vous nous accompagner|migration|mise en conformite|conformes|conformite|certification|rfe|facturation electronique|salesforce|dolibarr|sage|sap|gmao|dpo|dora|nis2|iso 27001|dsi de transition|demo|demonstration|d emonstration)\b/', $text) === 1;
        $hasScope = preg_match('/\b(interface|interfaces|flux|donnees|architecture|trajectoire|calendrier|organisation|processus|crm|erp|salesforce|dolibarr|pdp|plateforme agreee)\b/', $text) === 1;

        if ($hasNeed && $hasProject && $hasScope) {
            return 'qualified_opportunity';
        }

        if ($hasNeed && $hasProject) {
            return 'identified_need';
        }

        return 'exploration';
    }

    /**
     * @param array<string, string|null> $qualification
     */
    private function matchPotentialValue(array $qualification, string $text): string
    {
        if (
            in_array($qualification['urgency_level'], ['immediate', 'short_term'], true)
            && in_array($qualification['commercial_intent'], ['cadrage', 'assistance_projet', 'mise_en_conformite'], true)
        ) {
            return 'high';
        }

        if (preg_match('/budget|comex|direction|dg|dsi|consultation|appel d offres/', $text)) {
            return 'medium';
        }

        if (!empty($qualification['primary_need'])) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * @param array<string, string|null> $qualification
     * @param array<string, string|null> $modelHints
     * @return array<string, string|null>
     */
    private function mergeHints(array $qualification, array $modelHints): array
    {
        $sanitizedHints = $this->sanitizeQualification($modelHints);
        foreach ($sanitizedHints as $field => $value) {
            if (!array_key_exists($field, $qualification)) {
                continue;
            }

            if (($qualification[$field] === null || $qualification[$field] === 'information') && $value !== null) {
                $qualification[$field] = $value;
            }
        }

        return $qualification;
    }

    /**
     * @param array<string, string|null> $qualification
     * @return string[]
     */
    private function missingFields(array $qualification): array
    {
        $fields = [];
        foreach (['primary_need', 'urgency_level', 'maturity_level', 'organization_type', 'organization_size', 'commercial_intent'] as $field) {
            if (empty($qualification[$field])) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * @param array<string, string|null> $qualification
     */
    private function detailScore(array $qualification, string $text): int
    {
        $score = 0;
        foreach (['primary_need', 'urgency_level', 'maturity_level', 'organization_type', 'organization_size', 'commercial_intent'] as $field) {
            if (!empty($qualification[$field])) {
                ++$score;
            }
        }

        if (strlen($text) > 120) {
            ++$score;
        }

        return min(6, $score);
    }

    /**
     * @return array<string, string[]>
     */
    private function allowedValues(): array
    {
        return [
            'primary_need' => ['transformation_si', 'amoa_erp', 'crm', 'gmao', 'si_finance', 'rfe', 'organisation_gouvernance', 'conformite', 'rgpd', 'cybersecurite', 'pca_pra', 'qse', 'grc_mapsi', 'ia_data_automatisation', 'autre'],
            'urgency_level' => ['immediate', 'short_term', 'planned', 'exploratory'],
            'maturity_level' => ['flou', 'cadre', 'consultation', 'en_cours', 'bloque'],
            'organization_type' => ['pme', 'pmi', 'eti', 'public', 'association', 'autre'],
            'organization_size' => ['1_49', '50_249', '250_999', '1000_plus', 'unknown'],
            'commercial_intent' => ['information', 'orientation', 'diagnostic', 'cadrage', 'assistance_projet', 'audit', 'mise_en_conformite', 'demo_logiciel', 'devis', 'quote_request', 'proposal_request', 'contact', 'expertise_ponctuelle'],
            'potential_value' => ['low', 'medium', 'high'],
            'commercial_stage' => ['exploration', 'identified_need', 'qualified_opportunity', 'intent_contact'],
        ];
    }

    private function normalize(string $text): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($normalized === false) {
            $normalized = $text;
        }

        $normalized = strtolower($normalized);

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $normalized) ?? $normalized);
    }
}
