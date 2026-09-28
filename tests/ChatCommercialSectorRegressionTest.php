<?php

namespace App\Tests;

use App\Entity\ChatConversation;
use App\Entity\ChatMessage;
use App\Entity\ChatPublicDocument;
use App\Repository\ChatPublicDocumentRepository;
use App\Service\Chat\Ai\AiDecision;
use App\Service\Chat\Ai\AiProviderInterface;
use App\Service\Chat\Ai\HeuristicAiProvider;
use App\Service\Chat\ChatPublicContentIndexer;
use App\Service\Chat\ChatQualificationService;
use App\Service\Chat\ChatResponder;
use App\Service\Chat\PublicContentCatalog;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ChatCommercialSectorRegressionTest extends TestCase
{
    /** @return iterable<string, array{0:string,1:string,2:string}> */
    public static function sectorQuestions(): iterable
    {
        yield 'eau' => ['Eau et assainissement', 'Nous exploitons un service d eau et d assainissement et cherchons une AMOA pour notre ERP et notre SI client.', 'ERP SI client AMOA cadrage interfaces facturation'];
        yield 'transport' => ['Transport', 'Nous sommes un grand port et souhaitons refondre notre SI finance.', 'SI Finance refonte reporting continuité'];
        yield 'industrie' => ['Industrie', 'Nous sommes industriels et cherchons à remplacer ERP et GMAO.', 'ERP GMAO maintenance production'];
        yield 'sante' => ['Santé', 'Nous sommes un établissement de santé avec un projet ERP.', 'ERP données continuité établissement de santé'];
        yield 'mutuelle' => ['Mutuelle et assurance', 'Nous sommes une mutuelle et devons revoir PCA et gouvernance SI.', 'PCA gouvernance SI résilience'];
        yield 'formation' => ['Formation professionnelle', 'Notre organisme de formation veut changer ERP et structurer Qualiopi.', 'ERP Qualiopi processus formation'];
        yield 'collectivite' => ['Collectivités territoriales', 'Une communauté d agglomération souhaite un schéma directeur SI.', 'schéma directeur SI gouvernance'];
        yield 'consulaire' => ['Chambre consulaire', 'Notre CCI souhaite moderniser son ERP.', 'ERP modernisation chambre consulaire'];
        yield 'banque' => ['Banque', 'Notre banque doit moderniser une infrastructure critique.', 'infrastructure critique sécurité continuité'];
        yield 'negoce' => ['Négoce et distribution', 'Nous sommes un distributeur automobile et souhaitons revoir ERP et CRM.', 'ERP CRM distribution automobile'];
        yield 'habitat' => ['Aménagement du territoire et habitats', 'Nous sommes un bailleur social et cherchons une AMOA SI.', 'AMOA SI gouvernance habitat'];
    }

    /**
     * @dataProvider sectorQuestions
     */
    public function testSectorQuestionReturnsSectorReferenceWithoutFalseAbsence(string $sector, string $question, string $need): void
    {
        $reply = $this->responder([$this->reference($sector, $need), $this->service('AMOA ERP et transformation SI')])
            ->reply($this->conversation($question), $question);

        self::assertStringContainsString($sector, $reply->content);
        self::assertStringContainsString('références', mb_strtolower($reply->content));
        self::assertContains('/projets', $reply->sources);
        self::assertFalse($this->containsFalseAbsenceClaim($reply->content));
    }

    public function testCriticalWaterBugCannotDenyExistingSectorReference(): void
    {
        $question = 'Peut-on être accompagnés en AMOA sur un ERP et un SI client pour une entreprise de gestion de l eau et de l assainissement ?';
        $badProvider = new class implements AiProviderInterface {
            public function getName(): string { return 'bad_test_provider'; }
            public function isAvailable(): bool { return true; }
            public function generateDecision(ChatConversation $conversation, string $visitorMessage, array $documents, array $qualification): AiDecision
            {
                return new AiDecision('Les références fournies ne documentent toutefois pas d’intervention spécifique dans ce secteur.', false, $qualification, []);
            }
        };

        $reply = $this->responder([
            $this->reference('Eau et assainissement', 'AMOA ERP SI client facturation cadrage interfaces'),
            $this->service('AMOA ERP et SI client'),
        ], [$badProvider])->reply($this->conversation($question), $question);

        self::assertStringNotContainsString('ne documentent', mb_strtolower($reply->content));
        self::assertStringContainsString('OLING dispose de références dans le secteur Eau et assainissement', $reply->content);
        self::assertContains('/projets', $reply->sources);
    }

    public function testFictitiousSectorDoesNotInventSectorReference(): void
    {
        $question = 'Nous exploitons une société de tourisme spatial et cherchons une AMOA ERP.';
        $reply = $this->responder([$this->service('AMOA ERP cadrage choix solution reprise données')])
            ->reply($this->conversation($question), $question);

        self::assertStringNotContainsString('tourisme spatial', mb_strtolower($reply->content));
        self::assertFalse($this->containsFalseAbsenceClaim($reply->content));
    }

    public function testManagedSectorCanBeUsedWithoutCodeAlias(): void
    {
        $question = 'Nous sommes dans la plasturgie navale et cherchons une AMOA ERP.';

        $reply = $this->responder([
            $this->reference('Plasturgie navale', 'AMOA ERP cadrage interfaces'),
            $this->service('AMOA ERP cadrage choix solution reprise données'),
        ])->reply($this->conversation($question), $question);

        self::assertStringContainsString('Plasturgie navale', $reply->content);
        self::assertStringContainsString('références', mb_strtolower($reply->content));
        self::assertContains('/projets', $reply->sources);
    }

    public function testKnownSectorWithoutReferenceDoesNotInventReferenceExperience(): void
    {
        $question = 'Nous sommes une banque et cherchons une AMOA ERP.';

        $reply = $this->responder([$this->service('AMOA ERP cadrage choix solution reprise données')])
            ->reply($this->conversation($question), $question);

        self::assertStringNotContainsString('dispose de références', mb_strtolower($reply->content));
        self::assertStringNotContainsString('références anonymisées dans Banque', $reply->content);
    }

    /** @return iterable<string, array{0:string,1:string}> */
    public static function synonymQuestions(): iterable
    {
        yield 'societe des eaux' => ['Eau et assainissement', 'Notre société des eaux cherche une AMOA ERP.'];
        yield 'grand port maritime' => ['Transport', 'Nous sommes un grand port maritime et devons revoir le SI finance.'];
        yield 'bailleur social' => ['Aménagement du territoire et habitats', 'Un bailleur social cherche une AMOA SI.'];
        yield 'cfa' => ['Formation professionnelle', 'Notre CFA veut changer ERP.'];
        yield 'cci' => ['Chambre consulaire', 'Notre CCI modernise son ERP.'];
        yield 'complementaire sante' => ['Mutuelle et assurance', 'Une complémentaire santé veut revoir PCA et gouvernance SI.'];
        yield 'pmi industrielle' => ['Industrie', 'Une PMI industrielle veut remplacer ERP et GMAO.'];
        yield 'grossiste' => ['Négoce et distribution', 'Un grossiste souhaite revoir ERP et CRM.'];
        yield 'hopital' => ['Santé', 'Un hôpital lance un projet ERP.'];
        yield 'epci' => ['Collectivités territoriales', 'Un EPCI souhaite un schéma directeur SI.'];
    }

    /**
     * @dataProvider synonymQuestions
     */
    public function testSectorSynonymsAreNormalized(string $sector, string $question): void
    {
        $reply = $this->responder([$this->reference($sector, 'AMOA SI ERP cadrage'), $this->service('AMOA ERP')])
            ->reply($this->conversation($question), $question);

        self::assertStringContainsString($sector, $reply->content);
        self::assertStringContainsString('références', mb_strtolower($reply->content));
    }

    /** @param ChatPublicDocument[] $documents */
    private function responder(array $documents, array $providers = []): ChatResponder
    {
        $qualificationService = new ChatQualificationService();
        $repository = $this->createMock(ChatPublicDocumentRepository::class);
        $repository->method('findActiveDocuments')->willReturn($documents);

        return new ChatResponder(
            new PublicContentCatalog($repository, $this->createMock(ChatPublicContentIndexer::class)),
            $qualificationService,
            new HeuristicAiProvider($qualificationService),
            $providers,
            new NullLogger(),
        );
    }

    private function conversation(string $question): ChatConversation
    {
        $conversation = new ChatConversation();
        $conversation->addMessage((new ChatMessage())
            ->setRole('visitor')
            ->setContent($question)
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable()));

        return $conversation;
    }

    private function reference(string $sector, string $need): ChatPublicDocument
    {
        return $this->document('reference', '/projets', 'Référence '.$sector, 'Secteur '.$sector.'. Mission '.$need.'.');
    }

    private function service(string $text): ChatPublicDocument
    {
        return $this->document('service', '/business-apps/erp', 'Service '.$text, $text);
    }

    private function document(string $type, string $url, string $title, string $text): ChatPublicDocument
    {
        return (new ChatPublicDocument())
            ->setSourceType($type)
            ->setSourceEntityId(random_int(1, 100000))
            ->setSafeTitle($title)
            ->setSafeText($text)
            ->setUrl($url)
            ->setKeywords([$title, $text])
            ->setSearchText(mb_strtolower($title.' '.$text))
            ->setIsActive(true)
            ->setChecksum(sha1($type.$url.$title.$text))
            ->setUpdatedAt(new \DateTimeImmutable());
    }

    private function containsFalseAbsenceClaim(string $reply): bool
    {
        return preg_match('/pas de référence|aucune référence|ne dispose pas de référence|ne documentent pas|aucune expérience/i', $reply) === 1;
    }
}
