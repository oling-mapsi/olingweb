<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260824113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rewrite homepage editorial content from database and refresh featured practices copy';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() !== 'mysql') {
            return;
        }

        $homePayload = $this->buildHomePayload($this->fetchHomePayload());

        $homePage = $this->connection->fetchAssociative("SELECT id FROM site_page WHERE slug = 'home' LIMIT 1");
        if (is_array($homePage)) {
            $this->connection->update('site_page', [
                'title' => 'OLING | AMOA SI, ERP, CRM, GMAO & conformité',
                'meta_description' => 'Cabinet de conseil AMOA SI : cadrage et pilotage ERP, CRM, GMAO, SIRH, SI Finance, schéma directeur, RGPD, ISO 27001, NIS2/DORA. France et DROM.',
                'hero_badge' => 'Cabinet de conseil AMOA SI depuis 2012',
                'hero_title' => 'Cadrer et piloter|vos projets SI, progiciels|et conformité',
                'hero_intro' => 'OLING accompagne les directions générales, DSI et directions métiers dans le cadrage et le pilotage de projets ERP, CRM, GMAO, SIRH et SI Finance, ainsi que sur la gouvernance SI, le RGPD, les risques et les démarches ISO. Nous intervenons du diagnostic et du cahier des charges au choix de solution, à la recette, au déploiement et à la conduite du changement.',
                'body_html' => $this->encodeJson($homePayload),
            ], ['slug' => 'home']);
        } else {
            $this->connection->insert('site_page', [
                'slug' => 'home',
                'title' => 'OLING | AMOA SI, ERP, CRM, GMAO & conformité',
                'meta_description' => 'Cabinet de conseil AMOA SI : cadrage et pilotage ERP, CRM, GMAO, SIRH, SI Finance, schéma directeur, RGPD, ISO 27001, NIS2/DORA. France et DROM.',
                'hero_badge' => 'Cabinet de conseil AMOA SI depuis 2012',
                'hero_title' => 'Cadrer et piloter|vos projets SI, progiciels|et conformité',
                'hero_intro' => 'OLING accompagne les directions générales, DSI et directions métiers dans le cadrage et le pilotage de projets ERP, CRM, GMAO, SIRH et SI Finance, ainsi que sur la gouvernance SI, le RGPD, les risques et les démarches ISO. Nous intervenons du diagnostic et du cahier des charges au choix de solution, à la recette, au déploiement et à la conduite du changement.',
                'body_html' => $this->encodeJson($homePayload),
            ]);
        }

        $practicesSection = $this->connection->fetchAssociative("SELECT id FROM home_section WHERE slug = 'practices' LIMIT 1");
        if (is_array($practicesSection)) {
            $this->connection->update('home_section', [
                'eyebrow' => 'Expertises',
                'title' => 'Quatre expertises complémentaires pour cadrer, déployer et piloter vos projets',
                'intro' => 'OLING intervient à l’interface des directions métiers, des DSI, des intégrateurs et des fonctions conformité. Selon le besoin, nous prenons en charge le cadrage, la consultation, le pilotage, la recette, la conduite du changement ou le suivi opérationnel.',
                'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ], ['slug' => 'practices']);
        } else {
            $this->connection->insert('home_section', [
                'slug' => 'practices',
                'eyebrow' => 'Expertises',
                'title' => 'Quatre expertises complémentaires pour cadrer, déployer et piloter vos projets',
                'intro' => 'OLING intervient à l’interface des directions métiers, des DSI, des intégrateurs et des fonctions conformité. Selon le besoin, nous prenons en charge le cadrage, la consultation, le pilotage, la recette, la conduite du changement ou le suivi opérationnel.',
                'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]);
        }

        $practiceUpdates = [
            'consulting' => 'Diagnostic et schéma directeur SI, gouvernance, portefeuille projets, PMO et assistance à maîtrise d’ouvrage pour aligner les priorités métiers, les investissements et les projets.',
            'business-apps' => 'AMOA ERP, CRM, GMAO, SIRH, SI Finance, MES et BI : expression de besoin, cahier des charges, consultation, choix de solution, pilotage de l’intégrateur, reprise de données, recette et déploiement.',
            'expertises-audit' => 'RGPD et DPO externalisé, ISO 27001/9001, NIS2, DORA et continuité d’activité : audits, analyses de risques, plans d’action, documentation, contrôles et pilotage de la conformité.',
            'mapsi' => 'Plateforme GRC développée par OLING pour centraliser registres, audits, risques, plans d’action, incidents, documents de conformité et reporting.',
        ];

        foreach ($practiceUpdates as $slug => $introductionShort) {
            $this->connection->update('practice', [
                'introduction_short' => $introductionShort,
            ], ['slug' => $slug]);
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() !== 'mysql') {
            return;
        }

        $legacyPayload = $this->buildLegacyHomePayload($this->fetchHomePayload());
        $this->connection->update('site_page', [
            'title' => 'OLING | Conseil en transformation SI, ERP, conformité et IA',
            'meta_description' => 'OLING accompagne les PME, PMI et ETI sur les projets ERP, d\'organisation, de gouvernance SI, de conformité, de cybersécurité et d\'intelligence artificielle.',
            'hero_badge' => '',
            'hero_title' => 'FAIRE TENIR|VOS PROJETS SI|DANS LE REEL',
            'hero_intro' => 'Quand les flux se tendent, que les dépendances s\'empilent et que les projets SI cessent de tenir, OLING remet de la lisibilité, des responsables et une trajectoire exploitable. ERP, gouvernance, conformité, risques et IA sont traités comme un même système de pilotage.',
            'body_html' => $this->encodeJson($legacyPayload),
        ], ['slug' => 'home']);

        $this->connection->update('home_section', [
            'eyebrow' => '',
            'title' => 'Votre allié SI, organisation et conformité pour mener à bien vos initiatives stratégiques',
            'intro' => '<br>',
            'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ], ['slug' => 'practices']);

        $legacyPractices = [
            'consulting' => 'Cabinet AMOA SI : schéma directeur, gouvernance, pilotage de projets et optimisation des processus pour aligner SI et stratégie métier.',
            'business-apps' => 'ERP, CRM, BI et apps métier : conseil, déploiement et conduite du changement pour accélérer la transformation digitale.',
            'expertises-audit' => 'Conformité RGPD, ISO 27001/9001, audits et gestion des risques : sécurisez vos données et vos obligations réglementaires.',
            'mapsi' => 'MAPSI centralise RGPD, Qualiopi, qualité, risques et continuité d’activité avec plans d’actions, preuves et reporting.',
        ];

        foreach ($legacyPractices as $slug => $introductionShort) {
            $this->connection->update('practice', [
                'introduction_short' => $introductionShort,
            ], ['slug' => $slug]);
        }
    }

    private function fetchHomePayload(): array
    {
        $raw = $this->connection->fetchOne("SELECT body_html FROM site_page WHERE slug = 'home' LIMIT 1");
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function buildHomePayload(array $existing): array
    {
        $payload = [
            'hero' => [
                'badge' => 'OLING certifiée ISO 27001:2022',
                'secondaryCta' => ['label' => 'Découvrir nos expertises', 'route' => 'expertises_index'],
                'tags' => ['AMOA SI', 'ERP · CRM · GMAO', 'RGPD · ISO 27001'],
                'statement' => [
                    'eyebrow' => 'AMOA et pilotage',
                    'title' => 'Du cadrage à la mise en service',
                    'text' => 'Diagnostic, expression de besoin, consultation, choix de solution, pilotage, recette et conduite du changement.',
                ],
                'signal' => [
                    'eyebrow' => 'Métier adressé',
                    'title' => '',
                    'text' => '',
                ],
                'metierSlug' => $existing['hero']['metierSlug'] ?? null,
                'metierIntro' => $existing['hero']['metierIntro'] ?? null,
            ],
            'kpisSection' => [
                'eyebrow' => 'Repères',
                'title' => 'Des repères utiles avant de lancer ou reprendre un projet',
            ],
            'kpis' => [
                ['variant' => 'dark', 'label' => '2012', 'text' => 'Création d’OLING'],
                ['variant' => 'blue', 'label' => '70+', 'text' => 'Projets SI et conformité référencés'],
                ['variant' => 'pink', 'label' => 'ISO 27001:2022', 'text' => 'Certification OLING'],
                ['variant' => 'dark', 'label' => 'Hexagone & DROM', 'text' => 'Interventions sur site et à distance'],
            ],
            'practices' => [
                'eyebrow' => 'Expertises',
                'title' => 'Quatre expertises complémentaires pour cadrer, déployer et piloter vos projets',
            ],
            'accompaniments' => [
                'eyebrow' => 'Accompagnements',
                'title' => 'Nos principaux accompagnements',
                'items' => [
                    [
                        'title' => 'AMOA ERP',
                        'text' => 'Cadrage, cahier des charges, consultation, choix, intégration, reprise de données, recette et déploiement.',
                        'route' => 'seo_erp_progiciel',
                    ],
                    [
                        'title' => 'AMOA CRM',
                        'text' => 'Besoins métier, parcours client, choix de solution, intégration, données et adoption.',
                        'route' => 'seo_crm',
                    ],
                    [
                        'title' => 'AMOA GMAO',
                        'text' => 'Processus de maintenance, équipements, interventions, stocks, mobilité, choix et déploiement de la solution.',
                        'route' => 'seo_gmao',
                    ],
                    [
                        'title' => 'AMOA SIRH',
                        'text' => 'Paie, gestion des temps, processus RH, interfaces, reprise et recette.',
                        'route' => 'service',
                        'routeParams' => ['practice' => 'business-apps', 'slug' => 'erp'],
                    ],
                    [
                        'title' => 'AMOA SI Finance',
                        'text' => 'Comptabilité, budget, engagements, facturation, reporting et réforme de la facturation électronique.',
                        'route' => 'seo_si_finance',
                    ],
                    [
                        'title' => 'Schéma directeur SI',
                        'text' => 'Diagnostic, cartographie, architecture cible, trajectoire, budget et gouvernance.',
                        'route' => 'service',
                        'routeParams' => ['practice' => 'consulting', 'slug' => 'plan-strategique'],
                    ],
                    [
                        'title' => 'RGPD / DPO',
                        'text' => 'Audit, registre, DPIA, contrats, incidents, CNIL et pilotage du plan d’action.',
                        'route' => 'seo_rgpd',
                    ],
                    [
                        'title' => 'ISO 27001 / NIS2 / DORA / PCA',
                        'text' => 'Diagnostic, risques, exigences, contrôles, preuves et plan de mise en conformité.',
                        'route' => 'service',
                        'routeParams' => ['practice' => 'expertises-audit', 'slug' => 'si'],
                    ],
                ],
            ],
            'proof' => [
                'eyebrow' => 'Méthode d’intervention',
                'title' => 'Un accompagnement structuré autour des décisions et des livrables du projet',
                'items' => [
                    [
                        'title' => 'Cadrage et diagnostic',
                        'text' => 'Périmètre, besoins métiers, processus, données, risques, gouvernance, planning et critères de réussite.',
                    ],
                    [
                        'title' => 'Choix et pilotage',
                        'text' => 'Cahier des charges, consultation, analyse des offres, contractualisation, gouvernance projet, pilotage de l’intégrateur et arbitrages.',
                    ],
                    [
                        'title' => 'Recette et déploiement',
                        'text' => 'Reprise des données, interfaces, stratégie de tests, recette, formation, conduite du changement et suivi du démarrage.',
                    ],
                ],
                'image' => $existing['proof']['image'] ?? '/img/spe/bureau.jpg',
                'imageAlt' => $existing['proof']['imageAlt'] ?? 'Equipe OLING au travail',
            ],
            'projects' => [
                'eyebrow' => 'Références',
                'title' => 'Quelques réalisations sélectionnées pour illustrer le contexte, le périmètre et la mission OLING',
                'intro' => 'La homepage ne présente que les projets mis en avant dans l’administration.',
            ],
            'resources' => [
                'eyebrow' => 'Ressources',
                'title' => 'Articles, retours d’expérience et contenus utiles pour préparer un projet ou une mise en conformité',
                'intro' => 'Les dernières ressources publiées restent dynamiques et sont affichées plus bas dans la page.',
                'cta' => ['label' => 'Voir toutes les ressources', 'route' => 'seo_resources_index'],
            ],
            'finalCta' => [
                'eyebrow' => 'Diagnostic',
                'title' => 'Vous avez un projet à cadrer ou à remettre en trajectoire ?',
                'text' => 'Nous pouvons qualifier le périmètre, les risques, les parties prenantes et les prochaines étapes d’un projet ERP, CRM, GMAO, SIRH, SI Finance, RGPD ou gouvernance SI.',
                'primaryCta' => ['label' => 'Demander un diagnostic', 'route' => 'contact'],
            ],
        ];

        foreach (['portraitImage', 'portraitAlt'] as $field) {
            if (isset($existing['hero'][$field])) {
                $payload['hero'][$field] = $existing['hero'][$field];
            }
        }

        return $payload;
    }

    private function buildLegacyHomePayload(array $existing): array
    {
        return [
            'seoTitle' => 'OLING | Conseil en transformation SI, ERP, conformité et IA',
            'metaDescription' => 'OLING accompagne les PME, PMI et ETI sur les projets ERP, d\'organisation, de gouvernance SI, de conformité, de cybersécurité et d\'intelligence artificielle.',
            'hero' => [
                'eyebrow' => '',
                'badge' => 'OLING certifiée ISO 27001:2022',
                'titleLines' => ['FAIRE TENIR', 'VOS PROJETS SI', 'DANS LE REEL'],
                'intro' => 'Quand les flux se tendent, que les dépendances s\'empilent et que les projets SI cessent de tenir, OLING remet de la lisibilité, des responsables et une trajectoire exploitable. ERP, gouvernance, conformité, risques et IA sont traités comme un même système de pilotage.',
                'secondaryCta' => ['label' => 'Voir les expertises', 'url' => '/expertises'],
                'tags' => ['AMOA ERP', 'Flux & continuite', 'RGPD & gouvernance', 'IA utile'],
                'portraitImage' => $existing['hero']['portraitImage'] ?? '/uploads/metiers/hero/transport-intermodal-69a3a08f1beb31.17401526.jpg',
                'portraitAlt' => $existing['hero']['portraitAlt'] ?? 'Port, logistique et flux intermodaux',
                'statement' => [
                    'eyebrow' => 'Flux, arbitrages, execution',
                    'title' => 'Clarifier. Arbitrer. Tenir.',
                    'text' => 'Nous ne commentons pas la transformation à distance. Nous tenons la chaîne entre enjeux métier, flux critiques, outils et exécution.',
                ],
                'signal' => [
                    'eyebrow' => 'Métier adressé',
                    'title' => 'Transport intermodal',
                    'text' => 'Pilotage, dépendances, exécution et continuité sur un métier exposé aux flux critiques.',
                ],
                'metierSlug' => $existing['hero']['metierSlug'] ?? null,
                'metierIntro' => $existing['hero']['metierIntro'] ?? null,
            ],
            'introCards' => [
                ['variant' => 'blue', 'eyebrow' => 'AMOA ERP', 'title' => 'Choisir sans subir. Cadrer les flux. Tenir jusqu\'à l\'adoption.'],
                ['variant' => 'pink', 'eyebrow' => 'Risques & conformité', 'title' => 'Remettre les points de rupture sous contrôle, avec preuve et responsabilité.'],
                ['variant' => 'image', 'image' => '/img/spe/human.jpeg', 'alt' => 'Equipe en travail collaboratif'],
            ],
            'practices' => [
                'eyebrow' => 'Practices',
                'title' => 'Des practices pour reprendre la main sur les flux, les risques et l\'exécution.',
                'quoteEyebrow' => 'Positionnement',
                'quoteTitle' => 'Quand les flux se bloquent entre métiers, fournisseurs et comités, nous remettons une chaîne de décision claire.',
            ],
            'proof' => [
                'eyebrow' => 'Cabinet',
                'title' => 'Un cabinet engagé dans la transformation, mais suffisamment ferme pour tenir les arbitrages qui comptent.',
                'items' => [
                    ['title' => 'Même cadre de décision', 'text' => 'Métier, SI, données, sécurité, conformité et fournisseurs sont traités dans une seule logique d\'arbitrage.'],
                    ['title' => 'Livrables de pilotage', 'text' => 'Nous ne produisons pas des livrables de décor. Chaque sortie doit aider à décider, engager, déployer ou prouver.'],
                    ['title' => 'IA sous gouvernance', 'text' => 'Les usages IA sont traités comme des choix de responsabilité, de données et de contrôle, pas comme une vitrine.'],
                ],
                'image' => $existing['proof']['image'] ?? '/img/spe/bureau.jpg',
                'imageAlt' => $existing['proof']['imageAlt'] ?? 'Equipe OLING au travail',
                'editorialEyebrow' => 'Editorial',
                'editorialTitle' => 'Des contenus pour orienter une décision, pas pour meubler une communication.',
            ],
            'kpis' => [
                ['variant' => 'dark', 'label' => 'ERP', 'text' => 'Sécuriser l\'avant-projet, la consultation et la mise en œuvre'],
                ['variant' => 'blue', 'label' => 'RISK', 'text' => 'Reprendre la main sur les dépendances critiques et les arbitrages faibles'],
                ['variant' => 'pink', 'label' => 'RGPD', 'text' => 'Sortir du déclaratif pour installer des preuves, des rôles et des contrôles'],
                ['variant' => 'image', 'image' => $existing['kpis'][3]['image'] ?? '/uploads/metiers/hero/transport-intermodal-69a3a08f1beb31.17401526.jpg', 'alt' => $existing['kpis'][3]['alt'] ?? 'Environnement operationnel et logistique'],
            ],
            'finalCta' => [
                'title' => 'Obtenir une lecture exploitable de votre transformation',
                'text' => 'En quelques semaines, OLING remet à plat les enjeux, les risques, les rôles et les décisions pour remettre un projet SI, ERP, conformité ou IA sous contrôle.',
                'primaryCta' => ['label' => 'Demander un diagnostic', 'route' => 'contact'],
            ],
        ];
    }

    private function encodeJson(array $payload): string
    {
        return (string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
