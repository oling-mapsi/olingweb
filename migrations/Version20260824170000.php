<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260824170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align editorial database content with validated preprod AMOA wording';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() !== 'mysql') {
            return;
        }

        $this->updateHomePage();
        $this->updateProjectsPage();
        $this->updateHomeSections();
        $this->updatePractices();
        $this->updateServices();
    }

    public function down(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() !== 'mysql') {
            return;
        }

        $this->restoreHomePage();
        $this->restoreProjectsPage();
        $this->restoreHomeSections();
        $this->restorePractices();
        $this->restoreServices();
    }

    private function updateHomePage(): void
    {
        $payload = $this->decodeJson((string) $this->connection->fetchOne("SELECT body_html FROM site_page WHERE slug = 'home' LIMIT 1"));

        $payload['hero']['badge'] = 'OLING certifiée ISO 27001:2022';
        $payload['hero']['secondaryCta'] = ['label' => 'Découvrir nos expertises', 'route' => 'expertises_index'];
        $payload['hero']['tags'] = ['AMOA SI', 'ERP · CRM · GMAO', 'RGPD · ISO 27001', 'MAPSI'];
        $payload['hero']['statement'] = [
            'eyebrow' => 'AMOA et pilotage',
            'title' => 'Du cadrage à la mise en service',
            'text' => 'Diagnostic, expression de besoin, consultation, choix de solution, pilotage, recette et conduite du changement.',
        ];
        $payload['hero']['portraitAlt'] = 'Projet SI métier';
        $payload['projects']['intro'] = 'Une sélection de missions menées par OLING en AMOA SI, progiciels métiers, transformation des organisations, conformité et gouvernance.';

        $this->connection->update('site_page', [
            'title' => 'OLING | AMOA SI, ERP, CRM, GMAO & conformité',
            'meta_description' => 'Cabinet de conseil AMOA SI : cadrage et pilotage ERP, CRM, GMAO, SIRH, SI Finance, schéma directeur, RGPD, ISO 27001, NIS2/DORA. France & DROM.',
            'hero_badge' => 'Cabinet de conseil AMOA SI depuis 2012',
            'hero_title' => 'Cadrer et piloter|vos projets SI, progiciels|et conformité',
            'hero_intro' => "OLING accompagne les directions générales, DSI et directions métiers dans le cadrage et le pilotage de projets ERP, CRM, GMAO, SIRH et SI Finance, ainsi que sur la gouvernance SI, le RGPD, les risques et les démarches ISO.\n\nNous intervenons du diagnostic et du cahier des charges au choix de solution, à la recette, au déploiement et à la conduite du changement.",
            'body_html' => $this->encodeJson($payload),
        ], ['slug' => 'home']);
    }

    private function updateProjectsPage(): void
    {
        $payload = [
            'highlights' => ['AMOA SI', 'Progiciels métiers', 'Conformité et gouvernance'],
            'cards' => [
                'kicker' => 'Réalisations',
                'title' => 'Des missions menées sur des contextes variés, avec un même niveau d’exigence',
                'intro' => 'Les projets publiés conservent leur contenu détaillé et illustrent des missions réelles menées par OLING.',
                'archiveKicker' => 'Autres missions',
                'archiveTitle' => 'Autres réalisations à parcourir',
                'emptyText' => 'Aucune réalisation n’est publiée pour le moment.',
                'fallbackDescription' => 'Mission OLING en AMOA SI, progiciels métiers, conformité ou gouvernance.',
            ],
            'mapSection' => [
                'kicker' => 'Présence terrain',
                'title' => 'Des missions conduites en métropole et dans les DROM',
                'text' => 'OLING intervient là où les équipes, les projets et les contraintes opérationnelles exigent une présence de terrain ou un pilotage rapproché.',
                'label' => 'France métropolitaine & DROM',
            ],
        ];

        $this->connection->update('site_page', [
            'title' => 'Réalisations et missions OLING',
            'meta_description' => 'Sélection de missions menées par OLING en AMOA SI, progiciels métiers, transformation des organisations, conformité et gouvernance.',
            'hero_badge' => 'Réalisations OLING',
            'hero_title' => 'Réalisations et missions OLING',
            'hero_intro' => 'Une sélection de missions menées par OLING en AMOA SI, progiciels métiers, transformation des organisations, conformité et gouvernance.',
            'body_html' => $this->encodeJson($payload),
        ], ['slug' => 'projets']);
    }

    private function updateHomeSections(): void
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        $this->connection->update('home_section', [
            'eyebrow' => 'AMOA SI',
            'title' => 'Cadrer et piloter des projets SI, progiciels et conformité',
            'intro' => 'OLING intervient auprès des directions générales, DSI et métiers pour cadrer les projets, coordonner les parties prenantes et sécuriser les décisions.',
            'cta_label' => 'Demander un diagnostic',
            'cta_url' => '/contact',
            'updated_at' => $now,
        ], ['slug' => 'hero']);

        $this->connection->update('home_section', [
            'eyebrow' => 'Réalisations',
            'title' => 'Réalisations et missions OLING',
            'intro' => 'Une sélection de missions menées en AMOA SI, progiciels métiers, conformité et gouvernance.',
            'updated_at' => $now,
        ], ['slug' => 'projects']);
    }

    private function updatePractices(): void
    {
        $this->connection->update('practice', [
            'description' => '<p>OLING intervient en assistance à maîtrise d’ouvrage SI pour cadrer les besoins, organiser la gouvernance, piloter les consultations, coordonner métiers, DSI et intégrateurs, puis sécuriser la recette, le déploiement et la conduite du changement.</p>',
        ], ['slug' => 'consulting']);

        $this->connection->update('practice', [
            'description' => '<p>OLING accompagne les projets ERP, CRM, GMAO, SIRH, SI Finance, BI et applications métiers depuis l’expression de besoin jusqu’au déploiement, avec un pilotage indépendant et une attention forte aux processus, aux données et à l’adoption.</p>',
        ], ['slug' => 'business-apps']);

        $this->connection->update('practice', [
            'description' => '<p>OLING structure les démarches RGPD, ISO 27001, NIS2, DORA, contrôle interne et continuité d’activité à partir d’audits, d’analyses de risques, de plans d’action, de dispositifs de preuve et de pilotage opérationnel.</p>',
        ], ['slug' => 'expertises-audit']);
    }

    private function updateServices(): void
    {
        $this->connection->update('services', [
            'designation' => 'Data, automatisation et IA',
            'designation_short' => 'Data & IA',
            'introduction_short' => 'OLING accompagne l’automatisation des processus, l’intégration de solutions IA et le développement d’applications métiers, depuis l’identification des usages jusqu’au déploiement.',
            'description' => <<<HTML
<h2>Data, automatisation et IA</h2>
<p>OLING accompagne les TPE, PME et PMI sur l’automatisation des processus, l’intégration d’usages IA et l’évolution des applications métiers, avec une approche progressive, pilotable et adaptée aux contraintes opérationnelles.</p>
<p>L’intervention peut couvrir l’identification des usages, la priorisation des chantiers, l’intégration aux outils existants, la conduite du changement et le suivi des gains.</p>
<h3>Ce que couvre l’intervention</h3>
<ul>
  <li>Diagnostic des usages, des irritants et des priorités d’automatisation.</li>
  <li>Refonte de processus, simplification des circuits d’information et outillage.</li>
  <li>Intégration d’assistants, d’agents ou de composants IA dans l’existant.</li>
  <li>Déploiement, accompagnement des équipes et mesure des résultats.</li>
</ul>
<h3>Livrables attendus</h3>
<ul>
  <li>Feuille de route de transformation.</li>
  <li>Cartographie des processus et des gains attendus.</li>
  <li>Plan d’automatisation, d’intégration et d’adoption.</li>
  <li>Tableau de bord de priorités et de suivi.</li>
</ul>
HTML,
        ], ['slug' => 'transformation-digitale-automatisation-et-ia-utile']);

        $this->connection->update('services', [
            'description' => <<<HTML
<h2>AMOA IA, pilotage de projets IA et agents métier</h2>
<p>OLING accompagne les directions et équipes projet dans le cadrage, le pilotage et le déploiement de projets IA, depuis les programmes structurants jusqu’aux agents métier intégrés aux processus existants.</p>
<p>L’intervention vise à clarifier les objectifs, répartir les responsabilités et organiser un déploiement cohérent entre métiers, DSI, data, sécurité et partenaires.</p>
<h3>Ce que couvre l’intervention</h3>
<ul>
  <li>Cadrage AMOA IA et qualification des cas d’usage.</li>
  <li>Priorisation, lotissement et planification des déploiements.</li>
  <li>Coordination entre métiers, DSI, data, sécurité et partenaires.</li>
  <li>Pilotage projet, recette, mise en production et suivi.</li>
  <li>Conception d’agents métier adaptés aux outils et processus existants.</li>
</ul>
HTML,
        ], ['slug' => 'amoa-ia-pilotage-de-projets-ia-et-agents-metier']);
    }

    private function restoreHomePage(): void
    {
        $payload = $this->decodeJson((string) $this->connection->fetchOne("SELECT body_html FROM site_page WHERE slug = 'home' LIMIT 1"));

        $payload['hero']['badge'] = 'OLING certifiée ISO 27001:2022';
        $payload['hero']['tags'] = ['AMOA SI', 'ERP · CRM · GMAO', 'RGPD · ISO 27001'];
        $payload['hero']['statement'] = [
            'eyebrow' => 'AMOA et pilotage',
            'title' => 'Du cadrage à la mise en service',
            'text' => 'Diagnostic, expression de besoin, consultation, choix de solution, pilotage, recette et conduite du changement.',
        ];
        $payload['hero']['portraitAlt'] = 'Port, logistique et flux intermodaux';
        $payload['projects']['intro'] = 'La homepage ne présente que les projets mis en avant dans l’administration.';

        $this->connection->update('site_page', [
            'title' => 'OLING | AMOA SI, ERP, CRM, GMAO & conformité',
            'meta_description' => 'Cabinet de conseil AMOA SI : cadrage et pilotage ERP, CRM, GMAO, SIRH, SI Finance, schéma directeur, RGPD, ISO 27001, NIS2/DORA. France et DROM.',
            'hero_badge' => 'Cabinet de conseil AMOA SI depuis 2012',
            'hero_title' => 'Cadrer et piloter|vos projets SI|et conformité',
            'hero_intro' => 'OLING accompagne les directions générales, DSI et directions métiers dans le cadrage et le pilotage de projets ERP, CRM, GMAO, SIRH et SI Finance, ainsi que sur la gouvernance SI, le RGPD, les risques et les démarches ISO. Nous intervenons du diagnostic et du cahier des charges au choix de solution, à la recette, au déploiement et à la conduite du changement.',
            'body_html' => $this->encodeJson($payload),
        ], ['slug' => 'home']);
    }

    private function restoreProjectsPage(): void
    {
        $this->connection->update('site_page', [
            'title' => 'Projets',
            'meta_description' => null,
            'hero_badge' => null,
            'hero_title' => null,
            'hero_intro' => null,
            'body_html' => null,
        ], ['slug' => 'projets']);
    }

    private function restoreHomeSections(): void
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        $this->connection->update('home_section', [
            'eyebrow' => null,
            'title' => 'Transformation des organisations maîtrisée de bout en bout.',
            'intro' => 'OLING Management est un cabinet de conseil indépendant, expert depuis 2012 de l’AMOA en Systèmes d’Information,  avec une spécialisation forte en conformité et la valorisation des données métier.',
            'cta_label' => 'Contactez-nous',
            'cta_url' => '/contact',
            'updated_at' => $now,
        ], ['slug' => 'hero']);

        $this->connection->update('home_section', [
            'eyebrow' => null,
            'title' => 'Chaque jour, nous nous emparons du changement et générons des résultats concrets.',
            'intro' => 'Votre allié tactique et organisationnel pour mener à bien vos initiatives stratégiques.',
            'updated_at' => $now,
        ], ['slug' => 'projects']);
    }

    private function restorePractices(): void
    {
        $this->connection->update('practice', [
            'description' => 'Accompagnement personnalisé pour une transformation des outils et des processus réussie et performante.',
        ], ['slug' => 'consulting']);

        $this->connection->update('practice', [
            'description' => 'Assistance à l\'intégration de solutions applicatives innovantes, déploiement efficace, soutien aux opérations, adaptabilité, propulsant votre entreprise vers l\'avenir.',
        ], ['slug' => 'business-apps']);

        $this->connection->update('practice', [
            'description' => 'Analyse rigoureuse, amélioration continue, conformité réglementaire, renforcement des processus, soutenant votre croissance durable.',
        ], ['slug' => 'expertises-audit']);
    }

    private function restoreServices(): void
    {
        $this->connection->update('services', [
            'designation' => 'Transformation digitale, automatisation et IA utile',
            'designation_short' => 'Transfo digitale',
            'introduction_short' => 'Accélérer la transformation digitale avec des automatisations tenables, des agents utiles et une trajectoire réaliste pour TPE, PME et PMI.',
            'description' => <<<HTML
<h2>Transformation digitale, automatisation et IA utile</h2>
<p>OLING relie transformation digitale, automatisation et intelligence artificielle à la réalité des opérations : gains concrets, adoption, outillage tenable et agents intégrés aux usages quotidiens.</p>
<p>Nous intervenons auprès des TPE, PME et PMI qui veulent moderniser sans empiler les outils ni lancer des chantiers disproportionnés.</p>
<h3>Ce que couvre l’intervention</h3>
<ul>
  <li>Diagnostic de maturité digitale et identification des priorités.</li>
  <li>Refonte de processus, simplification des flux et automatisation.</li>
  <li>Intégration d’agents, copilotes et usages IA dans l’existant.</li>
  <li>Conduite du changement et mesure des gains réels.</li>
  <li>Alignement entre collaboration, données, reporting et opérations.</li>
</ul>
<h3>Livrables attendus</h3>
<ul>
  <li>Feuille de route transformation digitale.</li>
  <li>Cartographie des irritants et flux cibles.</li>
  <li>Plan d’automatisation et d’adoption.</li>
  <li>Tableau de bord de priorités et de gains.</li>
</ul>
HTML,
        ], ['slug' => 'transformation-digitale-automatisation-et-ia-utile']);

        $this->connection->update('services', [
            'description' => <<<HTML
<h2>AMOA IA, pilotage de projets IA et agents métier</h2>
<p>OLING accompagne les directions et équipes projet sur toutes les facettes des projets IA : cadrer, construire, co-construire, arbitrer et faire tenir des déploiements utiles.</p>
<p>Nous intervenons aussi bien sur des chantiers IA structurants que sur des agents métier adaptés aux TPE, PME et ETI, avec une logique constante : rendre les décisions lisibles, les responsabilités claires et l’exécution pilotable.</p>
<h3>Ce que couvre l’intervention</h3>
<ul>
  <li>Cadrage AMOA IA et qualification des cas d’usage.</li>
  <li>Priorisation, lotissement et trajectoire de déploiement.</li>
  <li>Co-construction avec les métiers, la DSI, la data, la sécurité et les partenaires.</li>
  <li>Pilotage des grands chantiers IA, recette et mise en production.</li>
  <li>Conception d’agents métier utiles, intégrés aux outils et processus existants.</li>
</ul>
HTML,
        ], ['slug' => 'amoa-ia-pilotage-de-projets-ia-et-agents-metier']);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(string $value): array
    {
        if (trim($value) === '') {
            return [];
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function encodeJson(array $payload): string
    {
        return json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
