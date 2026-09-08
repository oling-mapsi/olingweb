<?php

namespace App\Service\ErpQuestionnaire;

use App\Entity\ErpQuestionnaireSubmission;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class ErpQuestionnairePayloadMapper
{
    public function __construct(private readonly CsrfTokenManagerInterface $csrfTokenManager)
    {
    }

    /**
     * @param array<string, mixed> $values
     * @return string[]
     */
    public function validate(array $values, string $token): array
    {
        $errors = [];
        if (trim((string) ($values['website'] ?? '')) !== '') {
            $errors[] = 'La soumission n’a pas pu être validée.';
        }

        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('erp_questionnaire', $token))) {
            $errors[] = 'Jeton de sécurité invalide.';
        }

        foreach ([
            'fullName' => 'Nom et prénom',
            'email' => 'Email professionnel',
            'phone' => 'Téléphone',
            'company' => 'Organisation',
            'context' => 'Contexte',
            'need' => 'Besoin exprimé',
            'solutionType' => 'Type de solution',
            'projectMaturity' => 'Maturité projet',
            'irritants' => 'Irritants',
            'scope' => 'Périmètre',
            'organization' => 'Organisation projet',
            'planning' => 'Planning',
        ] as $field => $label) {
            if (trim((string) ($values[$field] ?? '')) === '') {
                $errors[] = $label.' est obligatoire.';
            }
        }

        if (!filter_var((string) ($values['email'] ?? ''), FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email professionnel invalide.';
        }

        if (empty($values['functionalScope']) || !is_array($values['functionalScope'])) {
            $errors[] = 'Au moins un module fonctionnel doit être sélectionné.';
        }

        if (empty($values['amoaExpectations']) || !is_array($values['amoaExpectations'])) {
            $errors[] = 'Au moins une attente AMOA doit être sélectionnée.';
        }

        if (empty($values['rgpdConsent'])) {
            $errors[] = 'Le consentement RGPD est obligatoire.';
        }

        foreach ($this->lengthLimits() as $field => $limit) {
            if (mb_strlen((string) ($values[$field] ?? '')) > $limit) {
                $errors[] = sprintf('%s dépasse la limite de %d caractères.', $field, $limit);
            }
        }

        return array_values(array_unique($errors));
    }

    /**
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    public function answers(array $values): array
    {
        $keys = [
            'fullName', 'email', 'phone', 'company', 'jobTitle', 'sector',
            'organizationSize', 'userCount', 'projectNature', 'solutionType',
            'hasExistingSolution', 'existingSolution', 'existingSolutionAge', 'existingSolutionHosting',
            'context', 'need', 'projectMaturity', 'hasSpecification', 'hasEditorIdentified',
            'consultationStatus', 'editorDetails', 'irritants', 'scope', 'organization', 'sponsor',
            'projectTeam', 'financeSpecific', 'dataMigration', 'migrationDetails', 'interfaceLevel',
            'dataInterfaces', 'securityRgpd', 'constraints', 'planning', 'urgency',
            'budgetStatus', 'budgetRange', 'amoaExpectationOther',
        ];
        $answers = [];
        foreach ($keys as $key) {
            $answers[$key] = trim((string) ($values[$key] ?? ''));
        }
        $answers['functionalScope'] = array_values(array_filter(
            $values['functionalScope'] ?? [],
            static fn (mixed $value): bool => is_string($value) && $value !== ''
        ));
        $answers['amoaExpectations'] = array_values(array_filter(
            $values['amoaExpectations'] ?? [],
            static fn (mixed $value): bool => is_string($value) && $value !== ''
        ));
        $answers['rgpdConsent'] = !empty($values['rgpdConsent']);

        return $answers;
    }

    /**
     * @param array<string, mixed> $answers
     * @param array<string, mixed> $summary
     */
    public function submission(array $answers, array $summary): ErpQuestionnaireSubmission
    {
        return (new ErpQuestionnaireSubmission())
            ->setPublicToken(bin2hex(random_bytes(24)))
            ->setFullName($answers['fullName'])
            ->setEmail($answers['email'])
            ->setPhone($answers['phone'])
            ->setCompany($answers['company'])
            ->setJobTitle($answers['jobTitle'] ?: null)
            ->setSector($answers['sector'] ?: null)
            ->setOrganizationSize($answers['organizationSize'] ?: null)
            ->setUserCount($answers['userCount'] ?: null)
            ->setSolutionType($answers['solutionType'] ?: null)
            ->setUrgency($answers['urgency'] ?: null)
            ->setBudgetRange($answers['budgetRange'] ?: null)
            ->setAnswers($answers)
            ->setSummary($summary);
    }

    /**
     * @return array<string, string>
     */
    public function functionalOptions(): array
    {
        return [
            'finance' => 'Finance / comptabilité',
            'budget' => 'Budget / engagements',
            'purchasing' => 'Achats / approvisionnements',
            'sales' => 'Ventes / gestion commerciale',
            'crm' => 'CRM / relation client',
            'stock' => 'Stocks / logistique',
            'production' => 'MES / production',
            'maintenance' => 'Maintenance / GMAO',
            'hr' => 'SIRH / paie',
            'reporting' => 'Reporting / BI',
            'ged' => 'GED / documents',
            'business' => 'Solution métier spécialisée',
            'interfaces' => 'Interfaces',
            'other' => 'Autre périmètre',
        ];
    }

    /**
     * @return array<string, int>
     */
    private function lengthLimits(): array
    {
        return [
            'fullName' => 160,
            'email' => 180,
            'phone' => 50,
            'company' => 180,
            'jobTitle' => 160,
            'sector' => 160,
            'existingSolution' => 240,
            'context' => 1200,
            'need' => 1200,
            'irritants' => 1200,
            'scope' => 1200,
            'organization' => 1000,
            'dataInterfaces' => 1000,
            'securityRgpd' => 800,
            'constraints' => 800,
            'planning' => 600,
        ];
    }
}
