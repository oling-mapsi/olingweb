<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260824225729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Clean editorial taxonomy wording in services and backfill missing public SEO meta descriptions';
    }

    public function up(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() !== 'mysql') {
            return;
        }

        foreach ($this->serviceUpdates() as $slug => $payload) {
            $this->connection->update('services', $payload, ['slug' => $slug]);
        }

        foreach ($this->sitePageMetaDescriptions() as $slug => $metaDescription) {
            $this->connection->update('site_page', [
                'meta_description' => $metaDescription,
            ], ['slug' => $slug]);
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->connection->getDatabasePlatform()->getName() !== 'mysql') {
            return;
        }
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function serviceUpdates(): array
    {
        return [
            'erp' => [
                'designation' => 'AMOA ERP, CRM, GMAO, SI Finance et SIRH',
                'designation_short' => 'AMOA ERP',
                'introduction_short' => 'AMOA progiciels métiers : cadrage, expression de besoin, choix de solution, pilotage, recette, reprise de données et déploiement.',
            ],
            'finance' => [
                'designation' => 'Conformité réglementaire et dispositifs de contrôle',
                'designation_short' => 'Conformité réglementaire',
                'introduction_short' => 'Diagnostic de conformité, cartographie des écarts, plan d’actions et accompagnement sur les obligations réglementaires et les contrôles associés.',
            ],
            'si' => [
                'designation' => 'Sécurité des SI, ISO 27001, DORA et NIS2',
                'designation_short' => 'Sécurité des SI',
                'introduction_short' => 'Audit, gouvernance sécurité, analyses de risques, SMSI, préparation ISO 27001 et mise sous contrôle des exigences DORA et NIS2.',
            ],
            'arborescence' => [
                'designation' => 'Conformité RGPD, registre et pilotage DPO',
                'designation_short' => 'Conformité RGPD',
                'introduction_short' => 'Registre, cartographie des traitements, DPIA, contrats, droits des personnes, incidents et pilotage opérationnel de la conformité RGPD.',
                'description' => $this->renderServiceDescription(
                    'Conformité RGPD, registre et pilotage DPO',
                    'OLING structure la conformité RGPD comme un dispositif opérationnel de responsabilité, de preuve et de pilotage, relié aux métiers, au SI et aux obligations du responsable de traitement.',
                    [
                        'Cadrage du dispositif RGPD, clarification des rôles et organisation de la gouvernance.',
                        'Registre des traitements, cartographie des flux, sous-traitants et bases légales.',
                        'DPIA, politiques, contrats, droits des personnes et gestion des incidents.',
                        'Suivi du plan d’actions, indicateurs, relances et préparation des contrôles.',
                    ]
                ),
            ],
            'automatisation-et-workflow' => [
                'designation' => 'QSE, qualité, sécurité et amélioration continue',
                'designation_short' => 'QSE',
                'introduction_short' => 'Structurer les référentiels QSE, les audits, les écarts, les plans d’actions et les indicateurs pour un pilotage exploitable par les équipes.',
                'description' => $this->renderServiceDescription(
                    'QSE, qualité, sécurité et amélioration continue',
                    'OLING accompagne les démarches QSE pour rendre les exigences qualité, sécurité et amélioration continue pilotables, documentées et tenables dans l’organisation.',
                    [
                        'Diagnostic du dispositif existant et clarification des exigences applicables.',
                        'Structuration des référentiels, procédures, preuves et circuits de validation.',
                        'Organisation des audits, non-conformités, actions correctives et suivi des échéances.',
                        'Construction des indicateurs et du pilotage utile pour la direction et les opérationnels.',
                    ]
                ),
            ],
            'integration-progiciel' => [
                'designation' => 'Gouvernance, risques et contrôle interne',
                'designation_short' => 'Gouvernance & risques',
                'introduction_short' => 'Cartographie des risques, plans de traitement, gouvernance, contrôles et suivi des actions pour sécuriser les décisions et l’exécution.',
                'description' => $this->renderServiceDescription(
                    'Gouvernance, risques et contrôle interne',
                    'OLING aide les directions à structurer leur gouvernance, objectiver les risques et mettre en place un dispositif de contrôle proportionné aux enjeux métier, SI et conformité.',
                    [
                        'Cartographie des risques, qualification des impacts et priorisation des traitements.',
                        'Définition des rôles, instances, règles d’arbitrage et circuits de décision.',
                        'Formalisation des contrôles, revues périodiques et preuves d’exécution.',
                        'Suivi des plans d’actions et reporting pour la direction, les métiers et la conformité.',
                    ]
                ),
            ],
            'mapsi-audit' => [
                'designation' => 'Audit interne, contrôle et plans d’actions',
                'designation_short' => 'Audit interne',
                'introduction_short' => 'Préparer, conduire et suivre des audits internes avec une traçabilité claire des constats, écarts, recommandations et actions.',
                'description' => $this->renderServiceDescription(
                    'Audit interne, contrôle et plans d’actions',
                    'OLING structure les dispositifs d’audit interne pour les rendre exploitables par les équipes, les managers et les fonctions de gouvernance.',
                    [
                        'Planification des audits, périmètres, objectifs et critères de contrôle.',
                        'Conduite des revues, formalisation des constats et qualification des écarts.',
                        'Recommandations, plans d’actions, échéances, responsables et suivi des clôtures.',
                        'Tableaux de bord d’avancement et préparation des revues de direction.',
                    ]
                ),
            ],
            'mapsi-pca' => [
                'designation' => 'Continuité d’activité, PCA et PRA',
                'designation_short' => 'PCA / PRA',
                'introduction_short' => 'Analyser les activités critiques, structurer les scénarios, documenter les plans de continuité et organiser les tests.',
                'description' => $this->renderServiceDescription(
                    'Continuité d’activité, PCA et PRA',
                    'OLING accompagne les organisations qui doivent structurer ou remettre sous contrôle leurs dispositifs de continuité d’activité et de reprise après incident.',
                    [
                        'Identification des activités critiques, dépendances et niveaux de service attendus.',
                        'Définition des scénarios de crise, rôles, procédures et plans de reprise.',
                        'Organisation des tests, retours d’expérience et mise à jour documentaire.',
                        'Pilotage du dispositif dans la durée avec indicateurs, preuves et arbitrages.',
                    ]
                ),
            ],
            'mapsi-portfolio' => [
                'designation' => 'Pilotage de portefeuille projets et PMO',
                'designation_short' => 'Portefeuille projets',
                'introduction_short' => 'Prioriser, arbitrer et suivre plusieurs projets avec une gouvernance lisible, des indicateurs consolidés et une charge maîtrisée.',
                'description' => $this->renderServiceDescription(
                    'Pilotage de portefeuille projets et PMO',
                    'OLING structure le pilotage multi-projets pour aider la direction, la DSI et les métiers à arbitrer, suivre les dépendances et sécuriser l’exécution.',
                    [
                        'Cartographie du portefeuille, statuts, enjeux, dépendances et ressources clés.',
                        'Mise en place des règles d’arbitrage, des instances et du reporting de gouvernance.',
                        'Suivi des risques, des décisions et des points bloquants à l’échelle multi-projets.',
                        'Vision consolidée utile pour la direction, la DSI, le PMO et les sponsors métier.',
                    ]
                ),
            ],
            'mapsi-qualiopi' => [
                'designation' => 'Conformité Qualiopi et pilotage des preuves',
                'designation_short' => 'Qualiopi',
                'introduction_short' => 'Préparer les audits Qualiopi, structurer les preuves, suivre les actions et fiabiliser la documentation de conformité.',
                'description' => $this->renderServiceDescription(
                    'Conformité Qualiopi et pilotage des preuves',
                    'OLING accompagne les organismes de formation qui doivent structurer leur dispositif Qualiopi, tenir les preuves et préparer les audits dans la durée.',
                    [
                        'Diagnostic du niveau de conformité sur les indicateurs applicables.',
                        'Organisation des preuves, responsabilités, échéances et circuits de validation.',
                        'Préparation des audits, traitement des écarts et suivi des actions correctives.',
                        'Pilotage des indicateurs et maintien du dispositif entre deux audits.',
                    ]
                ),
            ],
            'mapsi-risques' => [
                'designation' => 'Cartographie des risques et plans de traitement',
                'designation_short' => 'Cartographie des risques',
                'introduction_short' => 'Identifier, qualifier et piloter les risques métier, SI, conformité ou opérationnels avec des arbitrages et plans d’actions exploitables.',
                'description' => $this->renderServiceDescription(
                    'Cartographie des risques et plans de traitement',
                    'OLING aide à objectiver les risques, à partager un langage commun entre fonctions et à piloter les traitements en cohérence avec les priorités de l’organisation.',
                    [
                        'Identification des scénarios de risques, causes, impacts et mesures existantes.',
                        'Évaluation, priorisation, seuils d’acceptation et décisions de traitement.',
                        'Plans d’actions, responsables, échéances et suivi des risques résiduels.',
                        'Reporting de pilotage pour la direction, la conformité et les métiers.',
                    ]
                ),
            ],
            'mapsi-secu' => [
                'designation' => 'Sécurité des SI, audit et gouvernance ISO 27001',
                'designation_short' => 'Sécurité des SI',
                'introduction_short' => 'Structurer la sécurité des SI, les audits, le SMSI, les plans d’actions et la conformité ISO 27001, NIS2 et DORA.',
                'description' => $this->renderServiceDescription(
                    'Sécurité des SI, audit et gouvernance ISO 27001',
                    'OLING accompagne les démarches de sécurité des SI pour clarifier les responsabilités, prioriser les mesures utiles et mettre sous contrôle les exigences de gouvernance et de conformité.',
                    [
                        'Diagnostic de posture sécurité, analyses de risques et priorisation des écarts.',
                        'Structuration du SMSI, politiques, rôles, comités et indicateurs.',
                        'Préparation des audits, suivi des écarts et pilotage des plans d’actions.',
                        'Articulation avec ISO 27001, NIS2, DORA, PCA/PRA et exigences internes.',
                    ]
                ),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function sitePageMetaDescriptions(): array
    {
        return [
            'contact' => 'Contactez OLING pour un besoin de transformation SI, ERP, organisation, conformité, cybersécurité ou intelligence artificielle.',
            'services' => 'Découvrez les offres OLING : AMOA ERP, transformation SI, organisation, conformité, cybersécurité, risques, RGPD et IA.',
            'expertises-index' => 'OLING, cabinet de conseil pour PME, PMI et ETI : transformation SI, organisation, conformité, cybersécurité et intelligence artificielle.',
            'expertise-transformation-si-pme-eti' => 'OLING cadre, sécurise et pilote les transformations SI des PME, PMI et ETI : gouvernance, trajectoire, projets structurants et exécution.',
            'expertise-amoa-erp-applications-metiers' => 'OLING accompagne le choix, le cadrage, la mise en oeuvre et la sécurisation des projets ERP, CRM, GMAO et applications métiers.',
            'expertise-organisation-processus-conduite-du-changement' => 'OLING aide les PME, PMI et ETI à repenser leurs processus, structurer leurs responsabilités et conduire les changements liés aux transformations SI.',
            'expertise-data-automatisation-intelligence-artificielle' => 'OLING aide les entreprises à identifier des cas d’usage IA pertinents, structurer leur gouvernance, intégrer des agents et piloter des projets data et automatisation.',
            'expertise-cybersecurite-conformite-resilience' => 'OLING accompagne la sécurisation des organisations : cybersécurité, ISO 27001, NIS2, DORA, continuité et résilience.',
            'expertise-rgpd-dpo-gouvernance' => 'OLING accompagne la conformité RGPD, la gouvernance des données et les besoins de DPO externalisé pour PME, ETI et organisations régulées.',
            'secteurs-index' => 'Découvrez l’ensemble des secteurs servis par OLING et les points de vigilance connus selon chaque environnement métier.',
            'secteur-industrie' => 'OLING accompagne les industriels et PMI sur les enjeux ERP, GMAO, processus, données, continuité et conformité.',
            'secteur-services' => 'OLING aide les entreprises de services à structurer leurs processus, leur CRM, leur gouvernance data, leur conformité et leurs usages IA.',
            'secteur-secteur-public' => 'OLING accompagne les organisations régulées et les environnements publics sur les enjeux de conformité, cybersécurité, gouvernance et résilience.',
        ];
    }

    /**
     * @param list<string> $items
     */
    private function renderServiceDescription(string $title, string $intro, array $items): string
    {
        $html = sprintf("<h2>%s</h2>\n<p>%s</p>\n<h3>Ce que couvre l’intervention</h3>\n<ul>", $title, $intro);

        foreach ($items as $item) {
            $html .= sprintf("\n  <li>%s</li>", $item);
        }

        $html .= "\n</ul>";

        return $html;
    }
}
