<?php

namespace App\Service\ErpQuestionnaire;

class ErpQuestionnaireSummaryService
{
    /**
     * @param array<string, mixed> $answers
     * @return array<string, mixed>
     */
    public function build(array $answers): array
    {
        $modules = $this->cleanList($answers['functionalScope'] ?? []);
        $irritants = $this->splitText($answers['irritants'] ?? '');
        $constraints = $this->splitText($answers['constraints'] ?? '');
        $dataInterfaces = $this->dataInterfaces($answers);
        $securityRgpd = $this->splitText($answers['securityRgpd'] ?? '');
        $maturity = $this->maturity($answers);
        $complexity = $this->complexity($answers, $modules);
        $deliverables = $this->deliverables($answers, $complexity);
        $charge = $this->chargeEstimate($complexity);
        $budget = $this->budgetEstimate($complexity);
        $clarifications = $this->clarificationPoints($answers, $modules);
        $nextStep = $this->commercialNextStep($answers, $complexity);

        return [
            'executive_summary' => sprintf(
                '%s souhaite qualifier un besoin %s dans un contexte %s. Le périmètre pressenti couvre %s. À ce stade, la maturité est évaluée comme %s et la complexité comme %s.',
                $answers['company'] ?? 'L’organisation',
                $this->label($answers['solutionType'] ?? 'progiciel'),
                mb_strtolower($this->value($answers['context'] ?? 'à préciser')),
                $modules === [] ? 'un périmètre à préciser' : implode(', ', array_slice($modules, 0, 6)),
                mb_strtolower($maturity),
                mb_strtolower($complexity)
            ),
            'context' => $this->value($answers['context'] ?? '') ?: 'Contexte à préciser en échange de cadrage.',
            'current_situation' => $this->currentSituation($answers),
            'expressed_need' => $this->value($answers['need'] ?? '') ?: 'Besoin à préciser.',
            'functional_scope' => $modules === [] ? ['Périmètre fonctionnel à préciser'] : $modules,
            'irritants' => $irritants === [] ? ['Irritants à préciser en échange de cadrage'] : $irritants,
            'maturity' => $maturity,
            'complexity' => $complexity,
            'risks' => $this->risks($answers, $modules, $complexity),
            'constraints' => $constraints === [] ? ['Contraintes à confirmer'] : $constraints,
            'data_interfaces' => $dataInterfaces,
            'security_rgpd' => $securityRgpd === [] ? ['Habilitations, données personnelles, RGPD et traçabilité à confirmer selon le périmètre.'] : $securityRgpd,
            'project_organization' => $this->projectOrganization($answers),
            'recommended_deliverables' => $deliverables,
            'macro_approach' => $this->macroApproach($answers, $complexity),
            'macro_planning' => $this->macroPlanning($complexity),
            'amoa_charge_estimate' => $charge,
            'amoa_budget_estimate' => $budget,
            'estimation_assumptions' => $this->estimationAssumptions($answers, $modules),
            'missing_information' => $clarifications,
            'priority_meeting_questions' => $this->priorityMeetingQuestions($answers, $modules),
            'recommended_commercial_next_step' => $nextStep,
            'oling_next_step' => $nextStep,
            'clarification_points' => $clarifications,
            'short_summary' => sprintf(
                'Besoin %s pour %s, maturité %s, complexité %s, estimation AMOA %s.',
                $this->label($answers['solutionType'] ?? 'progiciel'),
                $answers['company'] ?? 'organisation à qualifier',
                mb_strtolower($maturity),
                mb_strtolower($complexity),
                $budget
            ),
        ];
    }

    /**
     * @param array<string, mixed> $answers
     * @return array<string, mixed>
     */
    public function scoring(array $answers, ?array $summary = null): array
    {
        $modules = $this->cleanList($answers['functionalScope'] ?? []);
        $complexity = $summary['complexity'] ?? $this->complexity($answers, $modules);
        $maturity = $summary['maturity'] ?? $this->maturity($answers);
        $urgency = $this->urgencyScore($answers);
        $potential = $this->olingPotential($answers, $complexity, $urgency);

        return [
            'maturity' => $this->commercialMaturity($answers),
            'complexity' => $this->commercialComplexity($complexity),
            'urgency' => $urgency,
            'oling_potential' => $potential,
            'justification' => sprintf(
                'Périmètre %s, %s utilisateurs, maturité %s, urgence %s.',
                count($modules) >= 5 ? 'large' : (count($modules) >= 3 ? 'intermédiaire' : 'ciblé'),
                $this->label($answers['userCount'] ?? 'à préciser'),
                mb_strtolower((string) $maturity),
                mb_strtolower($urgency)
            ),
            'next_action' => $potential === 'A' ? 'Appel de qualification prioritaire' : ($potential === 'B' ? 'Proposer un rendez-vous de cadrage' : 'Nurturing ou qualification courte'),
            'recommended_contact_delay' => $potential === 'A' ? '24h' : ($potential === 'B' ? '3 jours ouvrés' : '7 à 10 jours ouvrés'),
        ];
    }

    /**
     * @param array<string, mixed> $answers
     */
    private function maturity(array $answers): string
    {
        return match ((string) ($answers['projectMaturity'] ?? '')) {
            'idea' => 'Faible',
            'scoping' => 'Moyenne',
            'consultation', 'running' => 'Forte',
            'blocked' => 'Moyenne',
            default => 'Moyenne',
        };
    }

    /**
     * @param array<string, mixed> $answers
     * @param string[] $modules
     */
    private function complexity(array $answers, array $modules): string
    {
        $score = count($modules) >= 6 ? 3 : (count($modules) >= 4 ? 2 : (count($modules) >= 2 ? 1 : 0));
        $users = (string) ($answers['userCount'] ?? '');
        if (in_array($users, ['250_999', '1000_plus'], true)) {
            $score += 2;
        } elseif ($users === '50_249') {
            ++$score;
        }
        if (in_array(($answers['interfaceLevel'] ?? null), ['several', 'many'], true)) {
            $score += 2;
        }
        if (($answers['dataMigration'] ?? null) === 'yes') {
            ++$score;
        }
        if (($answers['projectMaturity'] ?? null) === 'blocked') {
            $score += 2;
        }

        return match (true) {
            $score >= 7 => 'Très forte',
            $score >= 5 => 'Forte',
            $score >= 3 => 'Moyenne',
            default => 'Faible',
        };
    }

    /**
     * @param array<string, mixed> $answers
     * @return string[]
     */
    private function currentSituation(array $answers): array
    {
        $items = [];
        if (($answers['hasExistingSolution'] ?? '') === 'yes') {
            $items[] = 'Solution existante : '.($this->value($answers['existingSolution'] ?? '') ?: 'à préciser');
            $items[] = 'Ancienneté / hébergement : '.trim(($this->label($answers['existingSolutionAge'] ?? '') ?: 'ancienneté à préciser').' / '.($this->label($answers['existingSolutionHosting'] ?? '') ?: 'hébergement à préciser'));
        } else {
            $items[] = 'Aucune solution existante structurante déclarée ou information non stabilisée.';
        }
        $items[] = 'Nature du projet : '.$this->label($answers['projectNature'] ?? 'à préciser');

        return $items;
    }

    /**
     * @param array<string, mixed> $answers
     * @param string[] $modules
     * @return string[]
     */
    private function risks(array $answers, array $modules, string $complexity): array
    {
        $risks = [];
        if (($answers['projectMaturity'] ?? null) === 'blocked') {
            $risks[] = 'Projet bloqué ou trajectoire à reprendre avant relance.';
        }
        if (count($modules) >= 5) {
            $risks[] = 'Périmètre fonctionnel large nécessitant priorisation et arbitrages.';
        }
        if (in_array(($answers['interfaceLevel'] ?? null), ['several', 'many'], true) || ($answers['dataMigration'] ?? null) === 'yes') {
            $risks[] = 'Reprise de données, qualité des référentiels et interfaces à cadrer finement.';
        }
        if ($this->value($answers['securityRgpd'] ?? '') !== '') {
            $risks[] = 'Sécurité, habilitations et conformité RGPD à intégrer dès le cadrage.';
        }
        if ($this->value($answers['financeSpecific'] ?? '') !== '') {
            $risks[] = 'Contraintes finance, budget, engagements ou clôture à sécuriser avec les métiers concernés.';
        }
        if (in_array($complexity, ['Forte', 'Très forte'], true)) {
            $risks[] = 'Charge AMOA, gouvernance et disponibilité métier à sécuriser avant engagement.';
        }

        return $risks === [] ? ['Risques projet à qualifier lors du cadrage.'] : $risks;
    }

    /**
     * @param array<string, mixed> $answers
     * @return string[]
     */
    private function deliverables(array $answers, string $complexity): array
    {
        $deliverables = [
            'Note de cadrage AMOA ERP / progiciel',
            'Expression structurée des besoins et périmètre fonctionnel cible',
            'Cartographie des processus, irritants et priorités',
            'Macro-planning, gouvernance et points d’arbitrage',
        ];
        if (in_array(($answers['projectMaturity'] ?? null), ['consultation', 'scoping'], true)) {
            $deliverables[] = 'Cahier des charges, grille de choix et scénarios de démonstration';
        }
        if ($this->value($answers['financeSpecific'] ?? '') !== '') {
            $deliverables[] = 'Atelier Finance dédié : comptabilité, budget, engagements, facturation, reporting et contrôles';
        }
        if (($answers['dataMigration'] ?? null) === 'yes' || in_array(($answers['interfaceLevel'] ?? null), ['several', 'many'], true)) {
            $deliverables[] = 'Stratégie de reprise de données et cadrage des interfaces';
        }
        if (in_array($complexity, ['Forte', 'Très forte'], true)) {
            $deliverables[] = 'Stratégie de recette, conduite du changement et dispositif de pilotage';
        }

        return $deliverables;
    }

    /**
     * @param array<string, mixed> $answers
     * @return string[]
     */
    private function dataInterfaces(array $answers): array
    {
        $items = [];
        if (($answers['dataMigration'] ?? '') === 'yes') {
            $items[] = 'Migration de données prévue : '.($this->value($answers['migrationDetails'] ?? '') ?: 'volumétrie et objets à préciser.');
        } else {
            $items[] = 'Migration de données non confirmée à ce stade.';
        }
        $items[] = 'Niveau d’interfaces : '.$this->label($answers['interfaceLevel'] ?? 'à préciser');
        foreach ($this->splitText($answers['dataInterfaces'] ?? '') as $item) {
            $items[] = $item;
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $answers
     * @return string[]
     */
    private function projectOrganization(array $answers): array
    {
        return array_values(array_filter([
            'Sponsor : '.($this->value($answers['sponsor'] ?? '') ?: 'à confirmer'),
            'Équipe projet : '.($this->value($answers['projectTeam'] ?? '') ?: $this->value($answers['organization'] ?? 'à confirmer')),
            'Spécification existante : '.$this->label($answers['hasSpecification'] ?? 'à préciser'),
            'Éditeur/intégrateur identifié : '.$this->label($answers['hasEditorIdentified'] ?? 'à préciser'),
            'Éditeur/intégrateur pressenti : '.($this->value($answers['editorDetails'] ?? '') ?: 'à confirmer'),
            'Consultation : '.$this->label($answers['consultationStatus'] ?? 'à préciser'),
        ]));
    }

    /**
     * @param array<string, mixed> $answers
     * @return string[]
     */
    private function macroApproach(array $answers, string $complexity): array
    {
        $approach = ['Cadrage flash du besoin, des parties prenantes et des hypothèses clés.'];
        $approach[] = 'Ateliers métiers ciblés sur le périmètre prioritaire et les irritants.';
        if (in_array($complexity, ['Forte', 'Très forte'], true)) {
            $approach[] = 'Séquence dédiée données, interfaces, sécurité, gouvernance et trajectoire de consultation.';
        }
        $approach[] = 'Restitution OLING avec livrables AMOA, risques, charge et budget indicatifs.';

        return $approach;
    }

    /**
     * @return string[]
     */
    private function macroPlanning(string $complexity): array
    {
        return match ($complexity) {
            'Très forte', 'Forte' => [
                '2 à 4 semaines : cadrage, ateliers et collecte documentaire',
                '3 à 6 semaines : expression de besoins, périmètre, risques, données et interfaces',
                '2 à 4 semaines : dossier de choix, macro-planning, budget et trajectoire projet',
            ],
            'Moyenne' => [
                '1 à 2 semaines : cadrage et ateliers clés',
                '2 à 4 semaines : expression de besoins, périmètre et risques',
                '1 à 2 semaines : synthèse, trajectoire, charge et budget indicatifs',
            ],
            default => [
                '1 semaine : qualification et cadrage rapide',
                '1 à 2 semaines : expression synthétique du besoin et périmètre',
                '1 semaine : trajectoire, livrables et estimation indicative',
            ],
        };
    }

    private function chargeEstimate(string $complexity): string
    {
        return match ($complexity) {
            'Très forte' => '30 à 60 jours AMOA indicatifs',
            'Forte' => '20 à 45 jours AMOA indicatifs',
            'Moyenne' => '10 à 25 jours AMOA indicatifs',
            default => '5 à 12 jours AMOA indicatifs',
        };
    }

    private function budgetEstimate(string $complexity): string
    {
        return match ($complexity) {
            'Très forte' => '36 000 à 78 000 € HT indicatifs',
            'Forte' => '24 000 à 58 000 € HT indicatifs',
            'Moyenne' => '12 000 à 32 000 € HT indicatifs',
            default => '6 000 à 15 000 € HT indicatifs',
        };
    }

    /**
     * @param array<string, mixed> $answers
     * @param string[] $modules
     * @return string[]
     */
    private function estimationAssumptions(array $answers, array $modules): array
    {
        return [
            'Estimation fondée sur '.max(1, count($modules)).' module(s) fonctionnel(s) déclaré(s).',
            'Nombre d’utilisateurs retenu : '.$this->label($answers['userCount'] ?? 'à préciser').'.',
            'Budget prospect déclaré : '.$this->label($answers['budgetStatus'] ?? 'à préciser').' / '.$this->label($answers['budgetRange'] ?? 'à confirmer').'.',
            'Les estimations excluent les coûts licence, intégration éditeur et développements spécifiques.',
        ];
    }

    /**
     * @param array<string, mixed> $answers
     * @param string[] $modules
     * @return string[]
     */
    private function clarificationPoints(array $answers, array $modules): array
    {
        $points = [];
        if ($modules === []) {
            $points[] = 'Modules fonctionnels réellement prioritaires.';
        }
        foreach ([
            'Périmètre exact et sites concernés.' => 'scope',
            'Données à reprendre et interfaces critiques.' => 'dataInterfaces',
            'Contraintes sécurité, RGPD et habilitations.' => 'securityRgpd',
            'Budget et fenêtre de décision.' => 'budgetRange',
        ] as $label => $field) {
            if ($this->value($answers[$field] ?? '') === '') {
                $points[] = $label;
            }
        }

        return $points === [] ? ['Valider les hypothèses de charge, budget et planning en rendez-vous.'] : $points;
    }

    /**
     * @param array<string, mixed> $answers
     * @param string[] $modules
     * @return string[]
     */
    private function priorityMeetingQuestions(array $answers, array $modules): array
    {
        return [
            'Quels processus et modules sont réellement prioritaires dans les 3 à 6 prochains mois ?',
            'Quels arbitrages sont déjà actés côté sponsor, DSI et métiers ?',
            'Quelles données, interfaces et contraintes réglementaires peuvent bloquer le planning ?',
            'Quel niveau de livrable OLING est attendu : cadrage, cahier des charges, consultation, pilotage ?',
        ];
    }

    /**
     * @param array<string, mixed> $answers
     */
    private function commercialNextStep(array $answers, string $complexity): string
    {
        if (in_array(($answers['urgency'] ?? null), ['immediate', 'short_term'], true) || in_array($complexity, ['Forte', 'Très forte'], true)) {
            return 'Proposer un rendez-vous de qualification OLING sous 24 à 48h pour sécuriser le périmètre, les risques et la trajectoire AMOA.';
        }

        return 'Proposer un échange de cadrage OLING pour confirmer le besoin, les livrables attendus, la charge et le budget indicatifs.';
    }

    /**
     * @param array<string, mixed> $answers
     */
    private function urgencyScore(array $answers): string
    {
        return match ((string) ($answers['urgency'] ?? '')) {
            'immediate', 'short_term' => 'Forte',
            'planned' => 'Moyenne',
            default => 'Faible',
        };
    }

    /**
     * @param array<string, mixed> $answers
     */
    private function commercialMaturity(array $answers): string
    {
        return match ((string) ($answers['projectMaturity'] ?? '')) {
            'idea' => 'Faible',
            'scoping', 'blocked' => 'Moyenne',
            'consultation', 'running' => 'Forte',
            default => 'Faible',
        };
    }

    private function commercialComplexity(string $complexity): string
    {
        return match ($complexity) {
            'Très forte' => 'Très forte',
            'Forte' => 'Forte',
            'Moyenne' => 'Moyenne',
            default => 'Faible',
        };
    }

    private function olingPotential(array $answers, string $complexity, string $urgency): string
    {
        $score = 0;
        if (in_array($complexity, ['Forte', 'Très forte'], true)) {
            $score += 2;
        } elseif ($complexity === 'Moyenne') {
            ++$score;
        }
        if ($urgency === 'Forte') {
            $score += 2;
        }
        if (in_array(($answers['budgetStatus'] ?? null), ['known', 'approx', 'confidential'], true)) {
            ++$score;
        }
        if (in_array(($answers['projectMaturity'] ?? null), ['scoping', 'consultation', 'blocked'], true)) {
            ++$score;
        }

        return $score >= 5 ? 'A' : ($score >= 3 ? 'B' : 'C');
    }

    /**
     * @param mixed $value
     * @return string[]
     */
    private function cleanList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map([$this, 'label'], $value)));
    }

    /**
     * @return string[]
     */
    private function splitText(mixed $value): array
    {
        $text = $this->value($value);
        if ($text === '') {
            return [];
        }

        $parts = preg_split('/[\n;]+/', $text) ?: [];

        return array_values(array_filter(array_map('trim', $parts)));
    }

    private function value(mixed $value): string
    {
        return trim(is_scalar($value) ? (string) $value : '');
    }

    private function label(mixed $value): string
    {
        return match ((string) $value) {
            'erp' => 'ERP',
            'si_finance' => 'SI Finance',
            'purchasing_solution' => 'Solution achats',
            'sales_solution' => 'Gestion commerciale',
            'crm' => 'CRM',
            'gmao' => 'GMAO',
            'sirh' => 'SIRH / paie',
            'mes' => 'MES / production',
            'bi' => 'BI / reporting',
            'ged' => 'GED',
            'business_solution' => 'Solution métier spécialisée',
            'other' => 'Autre progiciel',
            'replacement' => 'Remplacement',
            'first_equipment' => 'Premier équipement',
            'extension' => 'Extension de périmètre',
            'rescue' => 'Reprise de projet',
            'yes' => 'Oui',
            'no' => 'Non',
            'unknown' => 'À préciser',
            'recent' => 'Moins de 3 ans',
            'mid' => '3 à 8 ans',
            'old' => 'Plus de 8 ans',
            'cloud' => 'Cloud / SaaS',
            'on_premise' => 'On premise',
            'hybrid' => 'Hybride',
            'idea' => 'Réflexion initiale',
            'scoping' => 'Cadrage',
            'consultation' => 'Consultation / choix de solution',
            'running' => 'Projet en cours',
            'blocked' => 'Projet bloqué ou à reprendre',
            'none' => 'Aucune interface majeure',
            'few' => 'Quelques interfaces',
            'several' => 'Plusieurs systèmes',
            'many' => 'SI fortement interfacé',
            'draft' => 'Brouillon',
            'formalized' => 'Formalisée',
            'not_started' => 'Non démarrée',
            'started' => 'Démarrée',
            'ongoing' => 'En cours',
            'immediate' => 'Immédiate',
            'short_term' => 'Court terme',
            'planned' => 'Planifiée',
            'exploratory' => 'Exploratoire',
            'known' => 'Budget connu',
            'approx' => 'Ordre de grandeur',
            'confidential' => 'Budget confidentiel',
            'finance' => 'Finance / comptabilité',
            'budget' => 'Budget / engagements',
            'purchasing' => 'Achats / approvisionnements',
            'sales' => 'Ventes / gestion commerciale',
            'stock' => 'Stocks / logistique',
            'production' => 'MES / production',
            'maintenance' => 'Maintenance / GMAO',
            'hr' => 'SIRH / paie',
            'reporting' => 'Reporting / BI',
            'interfaces' => 'Interfaces',
            'business' => 'Solution métier spécialisée',
            'lt_15k' => '< 15 k€',
            '15_30k' => '15 à 30 k€',
            '30_60k' => '30 à 60 k€',
            '60k_plus' => '> 60 k€',
            '100k_plus' => '> 100 k€',
            default => $this->value($value),
        };
    }
}
