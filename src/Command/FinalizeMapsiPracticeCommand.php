<?php

namespace App\Command;

use App\Entity\SitePageTranslation;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:content:finalize-mapsi-practice',
    description: 'Finalise le contenu et les associations CMS de la Practice MAPSI.',
)]
class FinalizeMapsiPracticeCommand extends Command
{
    private const MAPSI_SERVICES = [
        'cadrage-conception-systemes-management' => [
            'title' => 'Cadrage et conception des systèmes de management',
            'short' => 'Cadrage GRC',
            'intro' => 'Diagnostic, périmètre, référentiels, rôles et trajectoire de mise sous pilotage dans MAPSI.',
            'description' => 'OLING structure le système de management avant l’outillage : processus, référentiels, responsabilités, risques, contrôles, preuves attendues et gouvernance de décision. Livrables : note de cadrage, cartographie des processus, matrice de responsabilités, feuille de route et backlog de configuration MAPSI.',
        ],
        'deploiement-parametrage-mapsi' => [
            'title' => 'Déploiement et paramétrage MAPSI',
            'short' => 'Paramétrage MAPSI',
            'intro' => 'Configuration des modules MAPSI, workflows, droits, écrans, alertes, tableaux de bord et circuits de validation.',
            'description' => 'OLING paramètre MAPSI selon les usages réels : registres, risques, contrôles, audits, plans d’actions, documents, indicateurs et profils utilisateurs. Bénéfices : dispositif exploitable, homogène, traçable et aligné sur les pratiques de terrain.',
        ],
        'reprise-import-structuration-donnees' => [
            'title' => 'Reprise, import et structuration des données',
            'short' => 'Données & imports',
            'intro' => 'Nettoyage, mapping et reprise des registres, référentiels, actions, preuves et historiques existants.',
            'description' => 'OLING prépare les données avant import : sources, modèles, règles de qualité, correspondances, contrôles et validation métier. Livrables : fichiers de reprise, dictionnaire de données, journal d’import, contrôles de cohérence et arbitrages de nettoyage.',
        ],
        'accompagnement-metier-conformite' => [
            'title' => 'Accompagnement métier et conformité',
            'short' => 'Conformité opérationnelle',
            'intro' => 'Appui RGPD, QSE, cyber, risques, contrôle interne et PCA/PRA pour transformer les exigences en pratiques pilotables.',
            'description' => 'OLING accompagne les responsables métier, DPO, QSE, DSI et contrôle interne dans la formalisation des règles, contrôles, preuves et plans d’actions. Bénéfices : conformité documentée, responsabilités clarifiées et audits mieux préparés.',
        ],
        'formation-conduite-changement' => [
            'title' => 'Formation et conduite du changement',
            'short' => 'Adoption utilisateurs',
            'intro' => 'Formation des administrateurs, pilotes de processus, contributeurs et utilisateurs finaux MAPSI.',
            'description' => 'OLING prépare les supports, anime les sessions, accompagne les premiers usages et ajuste les pratiques après démarrage. Livrables : supports de formation, modes opératoires, scénarios utilisateurs, plan d’adoption et retours d’expérience.',
        ],
        'exploitation-support-amelioration-continue' => [
            'title' => 'Exploitation, support et amélioration continue',
            'short' => 'Run & amélioration',
            'intro' => 'Suivi post-déploiement, support fonctionnel, amélioration des workflows et pilotage des indicateurs.',
            'description' => 'OLING aide à faire vivre MAPSI dans la durée : revues périodiques, traitement des irritants, évolution des référentiels, nouveaux modules, tableaux de bord et préparation des audits. Bénéfices : dispositif maintenu, adopté et améliorable.',
        ],
    ];

    private const TEAM_NAMES = [
        'Florestan Rouet',
        'Dorothée Maitrias',
        'Manuel Feuillard',
        'Maeva Phoulchand Kantapareddy',
        'Hanna Badan',
    ];

    private const PROJECT_SLUGS = [
        'dpo-externalise-rgpd-grand-port-maritime-de-la-guadeloupe-conformite-pilotee-avec-mapsi',
        'audit-de-conformite-rgpd-grand-port-maritime-de-la-guadeloupe-plan-dactions-et-pilotage-mapsi',
        'direction-qsee-externalisee-grand-port-maritime-de-la-martinique-iso-audits-et-mapsi',
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->connection->beginTransaction();

        try {
            $practiceId = $this->updatePractice();
            $this->updateNarrative();
            $serviceIds = $this->upsertServices($practiceId);
            $teamIds = $this->teamIds();
            $projectIds = $this->projectIds();
            $this->replaceServiceTeams($serviceIds, $teamIds);
            $this->replaceServiceProjects($serviceIds, $projectIds);
            $this->connection->commit();
        } catch (\Throwable $exception) {
            $this->connection->rollBack();
            throw $exception;
        }

        $io->success(sprintf(
            'MAPSI finalisé: services=%d experts=%d projets=%d',
            count($serviceIds),
            count($teamIds),
            count($projectIds)
        ));

        return Command::SUCCESS;
    }

    private function updatePractice(): int
    {
        $practiceId = (int) $this->connection->fetchOne('SELECT id FROM practice WHERE slug = :slug', ['slug' => 'mapsi']);
        if ($practiceId <= 0) {
            throw new \RuntimeException('Practice MAPSI introuvable.');
        }

        $this->connection->update('practice', [
            'designation' => 'MAPSI – Plateforme GRC',
            'designation_short' => 'MAPSI',
            'h1_title' => 'MAPSI – Plateforme GRC pour piloter conformité, risques et systèmes de management',
            'introduction' => 'MAPSI est la plateforme SaaS développée par OLING pour centraliser les référentiels, processus, risques, contrôles, audits, incidents, documents, actions et indicateurs des organisations.',
            'introduction_short' => 'Plateforme GRC développée par OLING pour piloter RGPD, QSE, cyber, risques, contrôle interne, PCA/PRA, SI et ITSM.',
            'description' => '<p>MAPSI associe un socle logiciel GRC à l’expertise métier OLING. La plateforme structure les référentiels, workflows, preuves et indicateurs ; les consultants OLING sécurisent le diagnostic, le cadrage, la configuration, l’audit, la formation et l’amélioration continue.</p><p>La chaîne fonctionnelle cible est simple : Référentiel → Risque → Contrôle → Audit → Écart → Action → Preuve → Indicateur.</p>',
            'description_short' => 'MAPSI relie référentiels, risques, contrôles, audits, écarts, actions, preuves et indicateurs.',
            'tags' => json_encode(['#MAPSI', '#GRC', '#RGPD', '#QSE', '#Risques', '#Conformité'], JSON_UNESCAPED_UNICODE),
        ], ['id' => $practiceId]);
        $this->upsertPracticeTranslation($practiceId);

        return $practiceId;
    }

    private function upsertPracticeTranslation(int $practiceId): void
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $row = [
            'designation' => 'MAPSI – Plateforme GRC',
            'slug' => 'mapsi',
            'designation_short' => 'MAPSI',
            'h1_title' => 'MAPSI – Plateforme GRC pour piloter conformité, risques et systèmes de management',
            'introduction' => 'MAPSI est la plateforme SaaS développée par OLING pour centraliser les référentiels, processus, risques, contrôles, audits, incidents, documents, actions et indicateurs des organisations.',
            'introduction_short' => 'Plateforme GRC développée par OLING pour piloter RGPD, QSE, cyber, risques, contrôle interne, PCA/PRA, SI et ITSM.',
            'description' => '<p>MAPSI associe un socle logiciel GRC à l’expertise métier OLING. La plateforme structure les référentiels, workflows, preuves et indicateurs ; les consultants OLING sécurisent le diagnostic, le cadrage, la configuration, l’audit, la formation et l’amélioration continue.</p><p>La chaîne fonctionnelle cible est simple : Référentiel → Risque → Contrôle → Audit → Écart → Action → Preuve → Indicateur.</p>',
            'description_short' => 'MAPSI relie référentiels, risques, contrôles, audits, écarts, actions, preuves et indicateurs.',
            'tags' => json_encode(['#MAPSI', '#GRC', '#RGPD', '#QSE', '#Risques', '#Conformité'], JSON_UNESCAPED_UNICODE),
            'translation_status' => SitePageTranslation::STATUS_PUBLISHED,
            'source_content_hash' => hash('sha256', 'mapsi-practice-finalized'),
            'source_updated_at' => $now,
            'updated_at' => $now,
        ];

        $translationId = $this->connection->fetchOne(
            'SELECT id FROM practice_translation WHERE practice_id = :practiceId AND locale = :locale',
            ['practiceId' => $practiceId, 'locale' => SitePageTranslation::LOCALE_FR]
        );
        if ($translationId) {
            $this->connection->update('practice_translation', $row, ['id' => $translationId]);
            return;
        }

        $row['practice_id'] = $practiceId;
        $row['locale'] = SitePageTranslation::LOCALE_FR;
        $row['created_at'] = $now;
        $this->connection->insert('practice_translation', $row);
    }

    private function updateNarrative(): void
    {
        $source = json_decode((string) file_get_contents($this->kernel->getProjectDir() . '/data/i18n/practice_narratives.fr.json'), true, 512, JSON_THROW_ON_ERROR);
        $payload = $source['narratives']['mapsi'] ?? null;
        if (!is_array($payload)) {
            throw new \RuntimeException('Narrative MAPSI source introuvable.');
        }

        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $contentId = $this->connection->fetchOne('SELECT id FROM site_global_content WHERE identifier = :identifier', ['identifier' => 'practice_narrative_mapsi']);
        if (!$contentId) {
            $this->connection->insert('site_global_content', [
                'identifier' => 'practice_narrative_mapsi',
                'enabled' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $contentId = (int) $this->connection->lastInsertId();
        }

        $translationId = $this->connection->fetchOne(
            'SELECT id FROM site_global_content_translation WHERE site_global_content_id = :contentId AND locale = :locale',
            ['contentId' => $contentId, 'locale' => SitePageTranslation::LOCALE_FR]
        );
        $row = [
            'title' => $payload['headline'],
            'body_html' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'translation_status' => SitePageTranslation::STATUS_PUBLISHED,
            'updated_at' => $now,
        ];
        if ($translationId) {
            $this->connection->update('site_global_content_translation', $row, ['id' => $translationId]);
            return;
        }

        $row['site_global_content_id'] = $contentId;
        $row['locale'] = SitePageTranslation::LOCALE_FR;
        $row['created_at'] = $now;
        $this->connection->insert('site_global_content_translation', $row);
    }

    /**
     * @return array<int>
     */
    private function upsertServices(int $practiceId): array
    {
        $targetSlugs = array_keys(self::MAPSI_SERVICES);
        $this->connection->executeStatement(
            'UPDATE services SET practice_id = NULL WHERE practice_id = :practiceId AND slug NOT IN (:slugs)',
            ['practiceId' => $practiceId, 'slugs' => $targetSlugs],
            ['slugs' => ArrayParameterType::STRING]
        );

        $ids = [];
        foreach (self::MAPSI_SERVICES as $slug => $service) {
            $id = $this->connection->fetchOne('SELECT id FROM services WHERE slug = :slug', ['slug' => $slug]);
            $row = [
                'Designation' => $service['title'],
                'designation_short' => $service['short'],
                'Introduction_short' => $service['intro'],
                'description' => $service['description'],
                'description_short' => $service['intro'],
                'practice_id' => $practiceId,
                'image1' => '/img/spe/practicemapsi.jpg',
                'image2' => '/img/spe/practicemapsi1.jpg',
            ];
            if ($id) {
                $this->connection->update('services', $row, ['id' => $id]);
                $serviceId = (int) $id;
                $this->upsertServiceTranslation($serviceId, $slug, $service);
                $ids[] = $serviceId;
                continue;
            }

            $row['slug'] = $slug;
            $this->connection->insert('services', $row);
            $serviceId = (int) $this->connection->lastInsertId();
            $this->upsertServiceTranslation($serviceId, $slug, $service);
            $ids[] = $serviceId;
        }

        return $ids;
    }

    /**
     * @param array{title: string, short: string, intro: string, description: string} $service
     */
    private function upsertServiceTranslation(int $serviceId, string $slug, array $service): void
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $row = [
            'designation' => $service['title'],
            'slug' => $slug,
            'designation_short' => $service['short'],
            'introduction_short' => $service['intro'],
            'description' => $service['description'],
            'description_short' => $service['intro'],
            'translation_status' => SitePageTranslation::STATUS_PUBLISHED,
            'source_content_hash' => hash('sha256', implode('|', [$slug, $service['title'], $service['intro'], $service['description']])),
            'source_updated_at' => $now,
            'updated_at' => $now,
        ];

        $translationId = $this->connection->fetchOne(
            'SELECT id FROM service_translation WHERE service_id = :serviceId AND locale = :locale',
            ['serviceId' => $serviceId, 'locale' => SitePageTranslation::LOCALE_FR]
        );
        if ($translationId) {
            $this->connection->update('service_translation', $row, ['id' => $translationId]);
            return;
        }

        $row['service_id'] = $serviceId;
        $row['locale'] = SitePageTranslation::LOCALE_FR;
        $row['created_at'] = $now;
        $this->connection->insert('service_translation', $row);
    }

    /**
     * @return array<int>
     */
    private function teamIds(): array
    {
        $ids = $this->connection->fetchFirstColumn(
            'SELECT id FROM team WHERE noncomplet IN (:names)',
            ['names' => self::TEAM_NAMES],
            ['names' => ArrayParameterType::STRING]
        );

        return array_map('intval', $ids);
    }

    /**
     * @return array<int>
     */
    private function projectIds(): array
    {
        $ids = $this->connection->fetchFirstColumn(
            'SELECT id FROM projet WHERE slug IN (:slugs)',
            ['slugs' => self::PROJECT_SLUGS],
            ['slugs' => ArrayParameterType::STRING]
        );

        return array_map('intval', $ids);
    }

    /**
     * @param array<int> $serviceIds
     * @param array<int> $teamIds
     */
    private function replaceServiceTeams(array $serviceIds, array $teamIds): void
    {
        if ($serviceIds === []) {
            return;
        }
        $this->connection->executeStatement('DELETE FROM services_team WHERE services_id IN (:ids)', ['ids' => $serviceIds], ['ids' => ArrayParameterType::INTEGER]);
        foreach ($serviceIds as $serviceId) {
            foreach ($teamIds as $teamId) {
                $this->connection->insert('services_team', ['services_id' => $serviceId, 'team_id' => $teamId]);
            }
        }
    }

    /**
     * @param array<int> $serviceIds
     * @param array<int> $projectIds
     */
    private function replaceServiceProjects(array $serviceIds, array $projectIds): void
    {
        if ($serviceIds === []) {
            return;
        }
        $this->connection->executeStatement('DELETE FROM projet_services WHERE services_id IN (:ids)', ['ids' => $serviceIds], ['ids' => ArrayParameterType::INTEGER]);
        foreach ($projectIds as $projectId) {
            foreach ($serviceIds as $serviceId) {
                $this->connection->insert('projet_services', ['projet_id' => $projectId, 'services_id' => $serviceId]);
            }
        }
    }
}
