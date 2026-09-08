<?php

namespace App\Tests;

use App\Service\ErpQuestionnaire\ErpQuestionnaireMailer;
use App\Service\ErpQuestionnaire\ErpQuestionnairePayloadMapper;
use App\Service\ErpQuestionnaire\ErpQuestionnairePdfGenerator;
use App\Service\ErpQuestionnaire\ErpQuestionnaireRateLimitGuard;
use App\Service\ErpQuestionnaire\ErpQuestionnaireSummaryService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ErpQuestionnaireTest extends TestCase
{
    public function testPayloadValidationAndSubmissionMapping(): void
    {
        $mapper = new ErpQuestionnairePayloadMapper($this->csrf(true));
        $answers = $mapper->answers($this->validPayload());
        $summaryService = new ErpQuestionnaireSummaryService();
        $summary = $summaryService->build($answers);
        $submission = $mapper->submission($answers, $summary);
        $submission->setScoring($summaryService->scoring($answers, $summary));

        self::assertSame([], $mapper->validate($this->validPayload(), 'valid'));
        self::assertSame('Acme', $submission->getCompany());
        self::assertSame('erp', $submission->getSolutionType());
        self::assertContains('finance', $submission->getAnswers()['functionalScope']);
        self::assertStringContainsString('Acme', $submission->getSummary()['executive_summary']);
        self::assertSame('A', $submission->getScoring()['oling_potential']);
    }

    public function testPayloadValidationRejectsMissingFields(): void
    {
        $mapper = new ErpQuestionnairePayloadMapper($this->csrf(true));
        $payload = $this->validPayload();
        $payload['email'] = 'bad-email';
        unset($payload['functionalScope'], $payload['rgpdConsent']);

        $errors = $mapper->validate($payload, 'valid');

        self::assertContains('Email professionnel invalide.', $errors);
        self::assertContains('Au moins un module fonctionnel doit être sélectionné.', $errors);
        self::assertContains('Le consentement RGPD est obligatoire.', $errors);
    }

    public function testSummaryFallbackBuildsExpectedStructuredSections(): void
    {
        $summary = (new ErpQuestionnaireSummaryService())->build($this->validPayload());

        foreach ([
            'executive_summary', 'context', 'current_situation', 'expressed_need',
            'functional_scope', 'irritants', 'maturity', 'complexity', 'risks',
            'constraints', 'data_interfaces', 'security_rgpd', 'project_organization',
            'recommended_deliverables', 'macro_approach', 'macro_planning',
            'amoa_charge_estimate', 'amoa_budget_estimate', 'estimation_assumptions',
            'missing_information', 'priority_meeting_questions', 'recommended_commercial_next_step',
        ] as $key) {
            self::assertArrayHasKey($key, $summary);
        }
        self::assertContains('Finance / comptabilité', $summary['functional_scope']);
        self::assertNotEmpty($summary['risks']);
    }

    public function testPdfGenerationReturnsPdfBinary(): void
    {
        $submission = $this->submission();
        $pdf = (new ErpQuestionnairePdfGenerator($this->twig(), dirname(__DIR__)))->generate($submission);

        self::assertStringStartsWith('%PDF', $pdf);
        self::assertStringContainsString('%%EOF', $pdf);
    }

    public function testMailerSendsProspectAndInternalEmailsWithPdfAttachment(): void
    {
        $sent = [];
        $mailer = $this->createMock(MailerInterface::class);
        $mailer
            ->expects(self::exactly(2))
            ->method('send')
            ->willReturnCallback(function (Email $email) use (&$sent): void {
                $sent[] = $email;
            });

        $service = new ErpQuestionnaireMailer(
            $mailer,
            $this->twig(),
            new ErpQuestionnairePdfGenerator($this->twig(), dirname(__DIR__)),
            'interne@example.test'
        );
        $service->sendProspectAndInternal($this->submission());

        self::assertSame('prospect@example.test', $sent[0]->getTo()[0]->getAddress());
        self::assertSame('interne@example.test', $sent[1]->getTo()[0]->getAddress());
        self::assertCount(1, $sent[0]->getAttachments());
        self::assertCount(1, $sent[1]->getAttachments());
        self::assertStringContainsString('Potentiel OLING', $sent[1]->getTextBody() ?? '');
        self::assertStringNotContainsString('Potentiel OLING', $sent[0]->getTextBody() ?? '');
    }

    public function testRateLimitGuardRejectsAfterConfiguredLimit(): void
    {
        $factory = new RateLimiterFactory([
            'id' => 'erp_questionnaire_test',
            'policy' => 'fixed_window',
            'limit' => 2,
            'interval' => '10 minutes',
        ], new InMemoryStorage());
        $guard = new ErpQuestionnaireRateLimitGuard($factory);
        $request = Request::create('/erp-progiciel/questionnaire', 'POST', server: ['REMOTE_ADDR' => '203.0.113.10']);

        self::assertTrue($guard->isAccepted($request));
        self::assertTrue($guard->isAccepted($request));
        self::assertFalse($guard->isAccepted($request));
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'fullName' => 'Jane Doe',
            'email' => 'prospect@example.test',
            'phone' => '0102030405',
            'company' => 'Acme',
            'jobTitle' => 'DAF',
            'sector' => 'Industrie',
            'organizationSize' => '250_999',
            'userCount' => '50_249',
            'projectNature' => 'replacement',
            'solutionType' => 'erp',
            'hasExistingSolution' => 'yes',
            'existingSolution' => 'ERP obsolète',
            'existingSolutionAge' => 'old',
            'existingSolutionHosting' => 'on_premise',
            'context' => 'Organisation multi-sites avec ERP vieillissant.',
            'need' => 'Cadrer le remplacement ERP finance et achats.',
            'projectMaturity' => 'scoping',
            'hasSpecification' => 'draft',
            'hasEditorIdentified' => 'no',
            'consultationStatus' => 'not_started',
            'financeSpecific' => 'Comptabilité, budget, engagements et reporting',
            'functionalScope' => ['finance', 'purchasing', 'interfaces'],
            'irritants' => 'Reporting lent; doubles saisies',
            'scope' => 'Finance, achats, interfaces comptables',
            'organization' => 'DAF, DSI, métiers achats',
            'sponsor' => 'DAF',
            'projectTeam' => 'DAF, DSI, achats',
            'dataMigration' => 'yes',
            'migrationDetails' => 'Référentiels fournisseurs et écritures ouvertes',
            'interfaceLevel' => 'several',
            'dataInterfaces' => 'Reprise fournisseurs et interfaces BI',
            'securityRgpd' => 'Habilitations et données personnelles',
            'constraints' => 'Décision avant fin de trimestre',
            'planning' => 'Cadrage sous 6 semaines',
            'urgency' => 'short_term',
            'budgetStatus' => 'approx',
            'budgetRange' => '30_60k',
            'amoaExpectations' => ['cadrage', 'requirements', 'vendor'],
            'rgpdConsent' => '1',
        ];
    }

    private function submission()
    {
        $answers = $this->validPayload();
        $summaryService = new ErpQuestionnaireSummaryService();
        $summary = $summaryService->build($answers);

        return (new ErpQuestionnairePayloadMapper($this->csrf(true)))
            ->submission($answers, $summary)
            ->setScoring($summaryService->scoring($answers, $summary));
    }

    private function twig(): Environment
    {
        return new Environment(new FilesystemLoader(dirname(__DIR__).'/templates'));
    }

    private function csrf(bool $valid): CsrfTokenManagerInterface
    {
        $manager = $this->createMock(CsrfTokenManagerInterface::class);
        $manager
            ->method('isTokenValid')
            ->willReturnCallback(static fn (CsrfToken $token): bool => $valid && $token->getValue() === 'valid');

        return $manager;
    }
}
