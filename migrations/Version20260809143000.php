<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260809143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Inject three new expertise site pages for IA-focused AMOA, conformity and digital transformation';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();
        if ($platform !== 'mysql') {
            return;
        }

        $rows = [
            [
                'slug' => 'expertise-amoa-ia-pilotage-projets-agents',
                'title' => 'AMOA IA, pilotage de projets IA et agents métier | OLING',
                'meta_description' => 'OLING cadre, co-construit et pilote les projets IA: AMOA IA, grands chantiers de transformation, agents adaptes aux TPE, PME et ETI.',
                'hero_badge' => 'AMOA IA et execution terrain',
                'hero_title' => 'AMOA IA, pilotage de programmes et agents metier',
                'hero_intro' => 'OLING accompagne les directions et equipes projet sur toutes les facettes des programmes IA: cadrer, construire, co-construire, arbitrer et faire tenir des deploiements utiles, du chantier strategique aux agents metier pour TPE et PME.',
                'hero_image' => '/img/spe/projet.png',
                'body_html' => json_encode([
                    'nav' => 'AMOA IA',
                    'title' => 'AMOA IA, pilotage de programmes et agents metier',
                    'seoTitle' => 'AMOA IA, pilotage de projets IA et agents metier | OLING',
                    'metaDescription' => 'OLING cadre, co-construit et pilote les projets IA: AMOA IA, grands chantiers de transformation, agents adaptes aux TPE, PME et ETI.',
                    'eyebrow' => 'AMOA IA et execution terrain',
                    'intro' => 'OLING accompagne les directions et equipes projet sur toutes les facettes des programmes IA: cadrer, construire, co-construire, arbitrer et faire tenir des deploiements utiles, du chantier strategique aux agents metier pour TPE et PME.',
                    'heroImage' => '/img/spe/projet.png',
                    'situations' => [
                        'Programme IA lance sans cadrage robuste entre metiers, DSI, data, securite et direction.',
                        'Besoin d\'une AMOA independante pour transformer une intention IA en feuille de route, lots et decisions pilotables.',
                        'Entreprise qui veut deployer des agents metier concrets sans passer par un dispositif trop lourd ou trop experimental.',
                    ],
                    'interventions' => [
                        'Cadrage AMOA IA, priorisation des cas d\'usage et construction de la trajectoire.',
                        'Co-construction avec les metiers, la DSI et les partenaires pour tenir valeur, delais et responsabilites.',
                        'Pilotage de grands chantiers IA: gouvernance, lots, arbitrages, dependances et recettes.',
                        'Conception et integration d\'agents adaptes aux TPE, PME et ETI sur leurs processus cibles.',
                    ],
                    'deliverables' => [
                        'Cadrage de programme IA',
                        'Backlog priorise de cas d\'usage',
                        'Dispositif de gouvernance et RACI',
                        'Architecture d\'agents et plan de deploiement',
                    ],
                    'linkedServices' => [
                        ['practice' => 'consulting', 'service' => 'pilotage-des-plans'],
                        ['practice' => 'business-apps', 'service' => 'developpements-specifiques'],
                    ],
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ],
            [
                'slug' => 'expertise-conformite-ia-gouvernance-ai-act',
                'title' => 'Conformite IA, AI Act et gouvernance des usages | OLING',
                'meta_description' => 'OLING structure la conformite IA, la gouvernance des usages, les controles et les preuves pour des projets IA robustes et auditable.',
                'hero_badge' => 'Conformite, preuves et responsabilites',
                'hero_title' => 'Conformite IA, gouvernance et AI Act',
                'hero_intro' => 'Nous aidons les organisations a deployer l\'IA sans angle mort de responsabilite: AI Act, protection des donnees, supervision humaine, gestion des fournisseurs et preuves de conformite.',
                'hero_image' => '/img/spe/rgpd.png',
                'body_html' => json_encode([
                    'nav' => 'Conformite IA',
                    'title' => 'Conformite IA, gouvernance et AI Act',
                    'seoTitle' => 'Conformite IA, AI Act et gouvernance des usages | OLING',
                    'metaDescription' => 'OLING structure la conformite IA, la gouvernance des usages, les controles et les preuves pour des projets IA robustes et auditable.',
                    'eyebrow' => 'Conformite, preuves et responsabilites',
                    'intro' => 'Nous aidons les organisations a deployer l\'IA sans angle mort de responsabilite: AI Act, protection des donnees, supervision humaine, gestion des fournisseurs et preuves de conformite.',
                    'heroImage' => '/img/spe/rgpd.png',
                    'situations' => [
                        'Direction qui veut deployer des usages IA sans exposer l\'entreprise a des risques juridiques, contractuels ou reputationnels.',
                        'Projet IA avec donnees sensibles, exigences de tracabilite ou demandes de preuve croissantes.',
                        'Besoin d\'aligner AI Act, RGPD, cybers securite, achats et gouvernance interne.',
                    ],
                    'interventions' => [
                        'Diagnostic de conformite IA et cartographie des usages, risques et obligations.',
                        'Definition du cadre de gouvernance, des roles, des controles et des principes d\'escalade.',
                        'Structuration des preuves: registre d\'usage, evaluation, supervision humaine et documentation fournisseur.',
                        'Mise en coherence des politiques IA avec RGPD, securite, qualite et controle interne.',
                    ],
                    'deliverables' => [
                        'Cartographie des usages IA et risques',
                        'Politique IA et cadre de gouvernance',
                        'Registre de conformite et controles',
                        'Plan d\'actions AI Act / RGPD / cyber',
                    ],
                    'linkedServices' => [
                        ['practice' => 'expertises-audit', 'service' => 'rgpd'],
                        ['practice' => 'expertises-audit', 'service' => 'si'],
                    ],
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ],
            [
                'slug' => 'expertise-transformation-digitale-ia-pme-pmi',
                'title' => 'Transformation digitale, automatisation et IA pour TPE, PME et PMI | OLING',
                'meta_description' => 'OLING accelere la transformation digitale des TPE, PME et PMI avec des projets IA utiles, des automatisations tenables et des agents integres aux operations.',
                'hero_badge' => 'Transformation digitale et performance',
                'hero_title' => 'Transformation digitale, automatisation et IA utile',
                'hero_intro' => 'OLING relie transformation digitale, automatisation et intelligence artificielle a la realite des operations: gains concrets, adoption, outillage tenable et agents integres aux usages quotidiens.',
                'hero_image' => '/img/spe/transition-digitale-1200x710.jpg',
                'body_html' => json_encode([
                    'nav' => 'Transformation digitale',
                    'title' => 'Transformation digitale, automatisation et IA utile',
                    'seoTitle' => 'Transformation digitale, automatisation et IA pour TPE, PME et PMI | OLING',
                    'metaDescription' => 'OLING accelere la transformation digitale des TPE, PME et PMI avec des projets IA utiles, des automatisations tenables et des agents integres aux operations.',
                    'eyebrow' => 'Transformation digitale et performance',
                    'intro' => 'OLING relie transformation digitale, automatisation et intelligence artificielle a la realite des operations: gains concrets, adoption, outillage tenable et agents integres aux usages quotidiens.',
                    'heroImage' => '/img/spe/transition-digitale-1200x710.jpg',
                    'situations' => [
                        'PME ou PMI qui veut moderniser ses processus sans empiler les outils ni casser les habitudes de travail.',
                        'Direction qui cherche des gains rapides par l\'automatisation, le reporting et des assistants ou agents metier.',
                        'Besoin d\'une trajectoire pragmatique pour faire converger applications, donnees, collaboration et IA.',
                    ],
                    'interventions' => [
                        'Diagnostic de maturite digitale et selection des chantiers prioritaires.',
                        'Refonte de processus, automatisation et simplification des circuits d\'information.',
                        'Integration d\'agents, copilotes et outils IA dans les environnements deja en place.',
                        'Pilotage de transformation, conduite du changement et mesure des gains reels.',
                    ],
                    'deliverables' => [
                        'Feuille de route transformation digitale',
                        'Cartographie des flux et irritants',
                        'Plan d\'automatisation et d\'adoption',
                        'Tableau de bord de gains et de priorites',
                    ],
                    'linkedServices' => [
                        ['practice' => 'business-apps', 'service' => 'ms365'],
                        ['practice' => 'business-apps', 'service' => 'msbi'],
                    ],
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ],
        ];

        foreach ($rows as $row) {
            $existingId = $this->connection->fetchOne('SELECT id FROM site_page WHERE slug = ?', [$row['slug']]);
            $payload = [
                'slug' => $row['slug'],
                'title' => $row['title'],
                'meta_description' => $row['meta_description'],
                'hero_badge' => $row['hero_badge'],
                'hero_title' => $row['hero_title'],
                'hero_intro' => $row['hero_intro'],
                'body_html' => $row['body_html'],
                'hero_image' => $row['hero_image'],
            ];

            if ($existingId !== false && $existingId !== null) {
                $this->connection->update('site_page', $payload, ['id' => $existingId]);
                continue;
            }

            $this->connection->insert('site_page', $payload);
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();
        if ($platform !== 'mysql') {
            return;
        }

        $this->addSql("DELETE FROM site_page WHERE slug IN ('expertise-amoa-ia-pilotage-projets-agents', 'expertise-conformite-ia-gouvernance-ai-act', 'expertise-transformation-digitale-ia-pme-pmi')");
    }
}
