<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260824173000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Clean remaining IA editorial overrides stored in site_page rows';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() !== 'mysql') {
            return;
        }

        $this->updateAmoaIaPage();
        $this->updateTransformationIaPage();
    }

    public function down(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() !== 'mysql') {
            return;
        }

        $this->restoreAmoaIaPage();
        $this->restoreTransformationIaPage();
    }

    private function updateAmoaIaPage(): void
    {
        $payload = $this->decodeJson((string) $this->connection->fetchOne("SELECT body_html FROM site_page WHERE slug = 'expertise-amoa-ia-pilotage-projets-agents' LIMIT 1"));
        $payload['title'] = 'AMOA IA, pilotage de projets IA et agents metier';
        $payload['metaDescription'] = 'OLING cadre et pilote les projets IA, organise la gouvernance et déploie des agents métier adaptés au contexte de l’entreprise.';
        $payload['eyebrow'] = 'AMOA IA et pilotage';
        $payload['intro'] = 'OLING accompagne les directions et équipes projet dans le cadrage, le pilotage et le déploiement de projets IA, depuis les programmes structurants jusqu’aux agents métier intégrés aux processus existants.';
        $payload['interventions'] = [
            'Cadrage AMOA IA, priorisation des cas d\'usage et construction de la feuille de route.',
            'Coordination avec les métiers, la DSI, la data, la sécurité et les partenaires.',
            'Pilotage des chantiers IA : gouvernance, lots, arbitrages, dépendances et recette.',
            'Conception et intégration d\'agents métier adaptés aux processus cibles.',
        ];

        $this->connection->update('site_page', [
            'title' => 'AMOA IA, pilotage de projets IA et agents métier | OLING',
            'meta_description' => 'OLING cadre et pilote les projets IA, organise la gouvernance et déploie des agents métier adaptés au contexte de l’entreprise.',
            'hero_badge' => 'AMOA IA et pilotage',
            'hero_title' => 'AMOA IA, pilotage de projets IA et agents metier',
            'hero_intro' => 'OLING accompagne les directions et équipes projet dans le cadrage, le pilotage et le déploiement de projets IA, depuis les programmes structurants jusqu’aux agents métier intégrés aux processus existants.',
            'body_html' => $this->encodeJson($payload),
        ], ['slug' => 'expertise-amoa-ia-pilotage-projets-agents']);
    }

    private function updateTransformationIaPage(): void
    {
        $payload = $this->decodeJson((string) $this->connection->fetchOne("SELECT body_html FROM site_page WHERE slug = 'expertise-transformation-digitale-ia-pme-pmi' LIMIT 1"));
        $payload['title'] = 'Transformation digitale, automatisation et IA';
        $payload['metaDescription'] = 'OLING accompagne la transformation digitale des TPE, PME et PMI avec des automatisations, des usages IA et des applications métiers adaptés aux opérations.';
        $payload['intro'] = 'OLING accompagne l’automatisation des processus, l’intégration de solutions IA et l’évolution des applications métiers, depuis l’identification des usages jusqu’au déploiement.';
        $payload['interventions'] = [
            'Diagnostic de maturité digitale et sélection des chantiers prioritaires.',
            'Refonte de processus, automatisation et simplification des circuits d’information.',
            'Intégration d’agents, d’assistants et d’outils IA dans les environnements déjà en place.',
            'Pilotage de transformation, conduite du changement et mesure des gains réels.',
        ];

        $this->connection->update('site_page', [
            'title' => 'Transformation digitale, automatisation et IA pour TPE, PME et PMI | OLING',
            'meta_description' => 'OLING accompagne la transformation digitale des TPE, PME et PMI avec des automatisations, des usages IA et des applications métiers adaptés aux opérations.',
            'hero_badge' => 'Transformation digitale et performance',
            'hero_title' => 'Transformation digitale, automatisation et IA',
            'hero_intro' => 'OLING accompagne l’automatisation des processus, l’intégration de solutions IA et l’évolution des applications métiers, depuis l’identification des usages jusqu’au déploiement.',
            'body_html' => $this->encodeJson($payload),
        ], ['slug' => 'expertise-transformation-digitale-ia-pme-pmi']);
    }

    private function restoreAmoaIaPage(): void
    {
        $payload = $this->decodeJson((string) $this->connection->fetchOne("SELECT body_html FROM site_page WHERE slug = 'expertise-amoa-ia-pilotage-projets-agents' LIMIT 1"));
        $payload['title'] = 'AMOA IA, pilotage de programmes et agents metier';
        $payload['metaDescription'] = 'OLING cadre, co-construit et pilote les projets IA: AMOA IA, grands chantiers de transformation, agents adaptes aux TPE, PME et ETI.';
        $payload['eyebrow'] = 'AMOA IA et execution terrain';
        $payload['intro'] = 'OLING accompagne les directions et equipes projet sur toutes les facettes des programmes IA: cadrer, construire, co-construire, arbitrer et faire tenir des deploiements utiles, du chantier strategique aux agents metier pour TPE et PME.';
        $payload['interventions'] = [
            'Cadrage AMOA IA, priorisation des cas d\'usage et construction de la trajectoire.',
            'Co-construction avec les metiers, la DSI et les partenaires pour tenir valeur, delais et responsabilites.',
            'Pilotage de grands chantiers IA: gouvernance, lots, arbitrages, dependances et recettes.',
            'Conception et integration d\'agents adaptes aux TPE, PME et ETI sur leurs processus cibles.',
        ];

        $this->connection->update('site_page', [
            'title' => 'AMOA IA, pilotage de projets IA et agents métier | OLING',
            'meta_description' => 'OLING cadre, co-construit et pilote les projets IA: AMOA IA, grands chantiers de transformation, agents adaptes aux TPE, PME et ETI.',
            'hero_badge' => 'AMOA IA et execution terrain',
            'hero_title' => 'AMOA IA, pilotage de programmes et agents metier',
            'hero_intro' => 'OLING accompagne les directions et equipes projet sur toutes les facettes des programmes IA: cadrer, construire, co-construire, arbitrer et faire tenir des deploiements utiles, du chantier strategique aux agents metier pour TPE et PME.',
            'body_html' => $this->encodeJson($payload),
        ], ['slug' => 'expertise-amoa-ia-pilotage-projets-agents']);
    }

    private function restoreTransformationIaPage(): void
    {
        $payload = $this->decodeJson((string) $this->connection->fetchOne("SELECT body_html FROM site_page WHERE slug = 'expertise-transformation-digitale-ia-pme-pmi' LIMIT 1"));
        $payload['title'] = 'Transformation digitale, automatisation et IA utile';
        $payload['metaDescription'] = 'OLING accelere la transformation digitale des TPE, PME et PMI avec des projets IA utiles, des automatisations tenables et des agents integres aux operations.';
        $payload['intro'] = 'OLING relie transformation digitale, automatisation et intelligence artificielle a la realite des operations: gains concrets, adoption, outillage tenable et agents integres aux usages quotidiens.';
        $payload['interventions'] = [
            'Diagnostic de maturite digitale et selection des chantiers prioritaires.',
            'Refonte de processus, automatisation et simplification des circuits d\'information.',
            'Integration d\'agents, copilotes et outils IA dans les environnements deja en place.',
            'Pilotage de transformation, conduite du changement et mesure des gains reels.',
        ];

        $this->connection->update('site_page', [
            'title' => 'Transformation digitale, automatisation et IA pour TPE, PME et PMI | OLING',
            'meta_description' => 'OLING accelere la transformation digitale des TPE, PME et PMI avec des projets IA utiles, des automatisations tenables et des agents integres aux operations.',
            'hero_badge' => 'Transformation digitale et performance',
            'hero_title' => 'Transformation digitale, automatisation et IA utile',
            'hero_intro' => 'OLING relie transformation digitale, automatisation et intelligence artificielle a la realite des operations: gains concrets, adoption, outillage tenable et agents integres aux usages quotidiens.',
            'body_html' => $this->encodeJson($payload),
        ], ['slug' => 'expertise-transformation-digitale-ia-pme-pmi']);
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
