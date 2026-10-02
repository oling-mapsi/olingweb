<?php

namespace App\Service\ErpQuestionnaire;

class ErpQuestionnaireSummaryService
{
    public function __construct(private readonly ErpQuestionnaireContentProvider $contentProvider)
    {
    }

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
                $this->text('executive'),
                $answers['company'] ?? $this->text('organization_fallback'),
                $this->label($answers['solutionType'] ?? $this->text('software_fallback')),
                mb_strtolower($this->value($answers['context'] ?? $this->text('clarify'))),
                $modules === [] ? $this->text('scope_fallback') : implode(', ', array_slice($modules, 0, 6)),
                mb_strtolower($maturity),
                mb_strtolower($complexity)
            ),
            'context' => $this->value($answers['context'] ?? '') ?: $this->text('context_fallback'),
            'current_situation' => $this->currentSituation($answers),
            'expressed_need' => $this->value($answers['need'] ?? '') ?: $this->text('need_fallback'),
            'functional_scope' => $modules === [] ? [$this->text('functional_scope_fallback')] : $modules,
            'irritants' => $irritants === [] ? [$this->text('irritants_fallback')] : $irritants,
            'maturity' => $maturity,
            'complexity' => $complexity,
            'risks' => $this->risks($answers, $modules, $complexity),
            'constraints' => $constraints === [] ? [$this->text('constraints_fallback')] : $constraints,
            'data_interfaces' => $dataInterfaces,
            'security_rgpd' => $securityRgpd === [] ? [$this->text('security_rgpd_fallback')] : $securityRgpd,
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
                $this->text('short'),
                $this->label($answers['solutionType'] ?? $this->text('software_fallback')),
                $answers['company'] ?? $this->text('organization_fallback'),
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
                $this->text('scoring_justification'),
                count($modules) >= 5 ? $this->text('scope_large') : (count($modules) >= 3 ? $this->text('scope_medium') : $this->text('scope_targeted')),
                $this->label($answers['userCount'] ?? $this->text('clarify')),
                mb_strtolower((string) $maturity),
                mb_strtolower($urgency)
            ),
            'next_action' => $potential === 'A' ? $this->text('next_action_a') : ($potential === 'B' ? $this->text('next_action_b') : $this->text('next_action_c')),
            'recommended_contact_delay' => $potential === 'A' ? $this->text('delay_a') : ($potential === 'B' ? $this->text('delay_b') : $this->text('delay_c')),
        ];
    }

    /**
     * @param array<string, mixed> $answers
     */
    private function maturity(array $answers): string
    {
        return match ((string) ($answers['projectMaturity'] ?? '')) {
            'idea' => $this->scale('low'),
            'scoping' => $this->scale('medium'),
            'consultation', 'running' => $this->scale('high'),
            'blocked' => $this->scale('medium'),
            default => $this->scale('medium'),
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
            $score >= 7 => $this->scale('very_high'),
            $score >= 5 => $this->scale('high'),
            $score >= 3 => $this->scale('medium'),
            default => $this->scale('low'),
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
            $items[] = sprintf($this->text('current_existing_solution'), $this->value($answers['existingSolution'] ?? '') ?: $this->text('current_existing_solution_fallback'));
            $items[] = sprintf($this->text('current_age_hosting'), $this->label($answers['existingSolutionAge'] ?? '') ?: $this->text('current_age_fallback'), $this->label($answers['existingSolutionHosting'] ?? '') ?: $this->text('current_hosting_fallback'));
        } else {
            $items[] = $this->text('current_no_solution');
        }
        $items[] = sprintf($this->text('current_project_nature'), $this->label($answers['projectNature'] ?? $this->text('clarify')));

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
            $risks[] = $this->text('risk_blocked');
        }
        if (count($modules) >= 5) {
            $risks[] = $this->text('risk_wide_scope');
        }
        if (in_array(($answers['interfaceLevel'] ?? null), ['several', 'many'], true) || ($answers['dataMigration'] ?? null) === 'yes') {
            $risks[] = $this->text('risk_data');
        }
        if ($this->value($answers['securityRgpd'] ?? '') !== '') {
            $risks[] = $this->text('risk_security');
        }
        if ($this->value($answers['financeSpecific'] ?? '') !== '') {
            $risks[] = $this->text('risk_finance');
        }
        if (in_array($complexity, [$this->scale('high'), $this->scale('very_high')], true)) {
            $risks[] = $this->text('risk_amoa');
        }

        return $risks === [] ? [$this->text('risk_fallback')] : $risks;
    }

    /**
     * @param array<string, mixed> $answers
     * @return string[]
     */
    private function deliverables(array $answers, string $complexity): array
    {
        $deliverables = $this->summaryList('deliverables');
        if (in_array(($answers['projectMaturity'] ?? null), ['consultation', 'scoping'], true)) {
            $deliverables[] = $this->text('deliverable_consultation');
        }
        if ($this->value($answers['financeSpecific'] ?? '') !== '') {
            $deliverables[] = $this->text('deliverable_finance');
        }
        if (($answers['dataMigration'] ?? null) === 'yes' || in_array(($answers['interfaceLevel'] ?? null), ['several', 'many'], true)) {
            $deliverables[] = $this->text('deliverable_data');
        }
        if (in_array($complexity, [$this->scale('high'), $this->scale('very_high')], true)) {
            $deliverables[] = $this->text('deliverable_steering');
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
            $items[] = sprintf($this->text('data_migration_yes'), $this->value($answers['migrationDetails'] ?? '') ?: $this->text('data_migration_details_fallback'));
        } else {
            $items[] = $this->text('data_migration_no');
        }
        $items[] = sprintf($this->text('interfaces_level'), $this->label($answers['interfaceLevel'] ?? $this->text('clarify')));
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
            sprintf($this->text('project_sponsor'), $this->value($answers['sponsor'] ?? '') ?: $this->text('confirm')),
            sprintf($this->text('project_team'), $this->value($answers['projectTeam'] ?? '') ?: $this->value($answers['organization'] ?? $this->text('confirm'))),
            sprintf($this->text('project_specification'), $this->label($answers['hasSpecification'] ?? $this->text('clarify'))),
            sprintf($this->text('project_editor_identified'), $this->label($answers['hasEditorIdentified'] ?? $this->text('clarify'))),
            sprintf($this->text('project_editor'), $this->value($answers['editorDetails'] ?? '') ?: $this->text('confirm')),
            sprintf($this->text('project_consultation'), $this->label($answers['consultationStatus'] ?? $this->text('clarify'))),
        ]));
    }

    /**
     * @param array<string, mixed> $answers
     * @return string[]
     */
    private function macroApproach(array $answers, string $complexity): array
    {
        $approach = $this->summaryList('approach');
        if (in_array($complexity, [$this->scale('high'), $this->scale('very_high')], true)) {
            array_splice($approach, 2, 0, [$this->text('approach_complex')]);
        }

        return $approach;
    }

    /**
     * @return string[]
     */
    private function macroPlanning(string $complexity): array
    {
        return match ($complexity) {
            $this->scale('very_high'), $this->scale('high') => $this->summaryList('planning.high'),
            $this->scale('medium') => $this->summaryList('planning.medium'),
            default => $this->summaryList('planning.low'),
        };
    }

    private function chargeEstimate(string $complexity): string
    {
        return match ($complexity) {
            $this->scale('very_high') => $this->text('charge.very_high'),
            $this->scale('high') => $this->text('charge.high'),
            $this->scale('medium') => $this->text('charge.medium'),
            default => $this->text('charge.low'),
        };
    }

    private function budgetEstimate(string $complexity): string
    {
        return match ($complexity) {
            $this->scale('very_high') => $this->text('budget.very_high'),
            $this->scale('high') => $this->text('budget.high'),
            $this->scale('medium') => $this->text('budget.medium'),
            default => $this->text('budget.low'),
        };
    }

    /**
     * @param array<string, mixed> $answers
     * @param string[] $modules
     * @return string[]
     */
    private function estimationAssumptions(array $answers, array $modules): array
    {
        $patterns = $this->summaryList('assumptions');

        return [
            sprintf($patterns[0] ?? '', max(1, count($modules))),
            sprintf($patterns[1] ?? '', $this->label($answers['userCount'] ?? $this->text('clarify'))),
            sprintf($patterns[2] ?? '', $this->label($answers['budgetStatus'] ?? $this->text('clarify')), $this->label($answers['budgetRange'] ?? $this->text('confirm'))),
            $patterns[3] ?? '',
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
            $points[] = $this->text('clarification_modules');
        }
        foreach ($this->summaryMap('clarification_fields') as $field => $label) {
            if ($this->value($answers[$field] ?? '') === '') {
                $points[] = $label;
            }
        }

        return $points === [] ? [$this->text('clarification_fallback')] : $points;
    }

    /**
     * @param array<string, mixed> $answers
     * @param string[] $modules
     * @return string[]
     */
    private function priorityMeetingQuestions(array $answers, array $modules): array
    {
        return $this->summaryList('meeting_questions');
    }

    /**
     * @param array<string, mixed> $answers
     */
    private function commercialNextStep(array $answers, string $complexity): string
    {
        if (in_array(($answers['urgency'] ?? null), ['immediate', 'short_term'], true) || in_array($complexity, [$this->scale('high'), $this->scale('very_high')], true)) {
            return $this->text('next_step_priority');
        }

        return $this->text('next_step_standard');
    }

    /**
     * @param array<string, mixed> $answers
     */
    private function urgencyScore(array $answers): string
    {
        return match ((string) ($answers['urgency'] ?? '')) {
            'immediate', 'short_term' => $this->scale('high'),
            'planned' => $this->scale('medium'),
            default => $this->scale('low'),
        };
    }

    /**
     * @param array<string, mixed> $answers
     */
    private function commercialMaturity(array $answers): string
    {
        return match ((string) ($answers['projectMaturity'] ?? '')) {
            'idea' => $this->scale('low'),
            'scoping', 'blocked' => $this->scale('medium'),
            'consultation', 'running' => $this->scale('high'),
            default => $this->scale('low'),
        };
    }

    private function commercialComplexity(string $complexity): string
    {
        return match ($complexity) {
            $this->scale('very_high') => $this->scale('very_high'),
            $this->scale('high') => $this->scale('high'),
            $this->scale('medium') => $this->scale('medium'),
            default => $this->scale('low'),
        };
    }

    private function olingPotential(array $answers, string $complexity, string $urgency): string
    {
        $score = 0;
        if (in_array($complexity, [$this->scale('high'), $this->scale('very_high')], true)) {
            $score += 2;
        } elseif ($complexity === $this->scale('medium')) {
            ++$score;
        }
        if ($urgency === $this->scale('high')) {
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
        return $this->contentProvider->label((string) $value);
    }

    private function scale(string $level): string
    {
        $content = $this->contentProvider->content();
        $value = $content['scale'][$level] ?? null;

        return is_string($value) ? $value : $level;
    }

    private function text(string $key): string
    {
        return $this->contentProvider->text('summary.'.$key);
    }

    /**
     * @return string[]
     */
    private function summaryList(string $key): array
    {
        $content = $this->contentProvider->content();
        $value = $this->path($content['summary'] ?? [], $key);

        return is_array($value) ? array_values(array_filter($value, 'is_string')) : [];
    }

    /**
     * @return array<string, string>
     */
    private function summaryMap(string $key): array
    {
        $content = $this->contentProvider->content();
        $value = $this->path($content['summary'] ?? [], $key);

        return is_array($value) ? array_filter($value, 'is_string') : [];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function path(array $data, string $path): mixed
    {
        $value = $data;
        foreach (explode('.', $path) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return null;
            }
            $value = $value[$part];
        }

        return $value;
    }
}
