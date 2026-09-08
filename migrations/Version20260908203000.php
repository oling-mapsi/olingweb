<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260908203000 extends AbstractMigration
{
    private const LEGAL_NOTE = "<p>Note : Ces mentions légales sont données à titre d'exemple et peuvent nécessiter des modifications pour s'adapter à la situation spécifique d'OLING. Il est recommandé de consulter un conseiller juridique pour adapter ces mentions légales à la réalité de l'entreprise.</p>";

    public function getDescription(): string
    {
        return 'Apply OLING Lot 1 legal, RFE, and service heading hygiene corrections';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE legal_page SET body = REPLACE(body, '".str_replace("'", "''", self::LEGAL_NOTE)."', ''), updated_at = NOW() WHERE slug = 'mentions-legales' AND body LIKE '%Ces mentions légales sont données à titre d''exemple%'");

        $this->replaceExact('site_page', 'title', 'facturation-electronique-amoa', 'AMOA facturation electronique | Trajectoire PPF PDP et deploiement | OLING', 'AMOA facturation électronique | Plateforme agréée et déploiement | OLING');
        $this->replaceExact('site_page', 'meta_description', 'facturation-electronique-amoa', 'Facturation electronique AMOA : cadrage des impacts metier et SI, choix PPF ou PDP, trajectoire e invoicing e reporting, gouvernance et deploiement.', 'Facturation électronique AMOA : cadrage des impacts métier et SI, choix de plateforme agréée, trajectoire e-invoicing/e-reporting, gouvernance et déploiement.');
        $this->replaceExact('site_page', 'hero_title', 'facturation-electronique-amoa', 'Facturation electronique AMOA : convertir obligation en execution', "Facturation électronique AMOA : convertir l'obligation en exécution");
        $this->replaceExact('site_page', 'hero_intro', 'facturation-electronique-amoa', 'OLING accompagne les directions financieres, metiers et SI pour cadrer, prioriser et deployer la facturation electronique avec une gouvernance claire, des impacts anticipes et une execution securisee.', 'Depuis le 1er septembre 2026, OLING accompagne les directions financières, métiers et SI pour cadrer, prioriser et déployer la facturation électronique avec une gouvernance claire, des impacts anticipés et une exécution sécurisée.');

        $this->replaceFragment('site_page', 'hero_side_html', 'facturation-electronique-amoa', 'Choix de trajectoire PPF/PDP et articulation avec les partenaires.', 'Choix de trajectoire plateforme agréée (PA) et articulation avec les partenaires.');
        $this->replaceFragment('site_page', 'hero_side_html', 'facturation-electronique-amoa', 'Cadrage impacts metier, SI et gouvernance PDP/PPF avant deploiement.', 'Cadrage impacts métier, SI et gouvernance PA avant déploiement.');
        $this->replaceFragment('site_page', 'hero_side_html', 'facturation-electronique-amoa', 'Plan de bascule e-invoicing/e-reporting pilote par jalons et risques.', 'Plan de bascule e-invoicing/e-reporting piloté par jalons et risques.');
        $this->replaceFragment('site_page', 'body_html', 'facturation-electronique-amoa', 'Comment choisir entre PPF et PDP ?', 'Comment choisir sa plateforme agréée ?');
        $this->replaceFragment('site_page', 'body_html', 'facturation-electronique-amoa', "Le choix depend des volumes, des cas d'usage, de l'ecosysteme partenaires et du niveau d'integration souhaite avec le SI.", "Le choix dépend des volumes, des cas d'usage, de l'écosystème partenaires, des formats attendus et du niveau d'intégration souhaité avec le SI.");
        $this->replaceFragment('site_page', 'body_html', 'facturation-electronique-amoa', 'Le plus tot possible pour planifier les lots, traiter les dependances applicatives et eviter une mise en conformite subie.', 'Le cadrage doit être engagé dès la phase applicable afin de planifier les lots, traiter les dépendances applicatives et éviter une mise en conformité subie.');

        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', 'PPF (portail public), PDP (plateformes partenaires)', 'Plateforme agréée (PA), annuaire et échanges de statuts');
        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', '1) Écosystème : PPF, PDP &amp; annuaire', '1) Écosystème : PA, annuaire &amp; statuts');
        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', '<li><strong>PDP</strong> : contrôle, routage, échanges de statuts et transmission des données.</li>', '<li><strong>Plateforme agréée (PA)</strong> : contrôle, routage, échanges de statuts et transmission des données.</li>');
        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', 'Routage via PDP', 'Routage via PA');
        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', 'Phase B – Choix PDP &amp; architecture', 'Phase B – Choix PA &amp; architecture');
        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', 'Dossier de choix PDP &amp; pré-architecture', 'Dossier de choix PA &amp; pré-architecture');
        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', 'DCE &amp; grille d’analyse (PDP)', 'DCE &amp; grille d’analyse (PA)');
        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', 'transmises via PDP/PPF', 'transmises via une plateforme agréée');
        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', 'PPF ou PDP : que choisir ?', 'Quelle plateforme agréée choisir ?');
        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', 'Le <strong>PPF</strong> centralise et collecte les données ; la <strong>PDP</strong> opère les flux au quotidien et gère les échanges inter-plateformes. Une PDP est recommandée pour piloter des SI/filiales multiples.', 'La <strong>plateforme agréée</strong> opère les flux au quotidien, gère les échanges de statuts et transmet les données attendues. Le choix dépend des volumes, des intégrations SI et des contraintes multi-entités.');

        $this->replaceFragment('services', 'description', 'ms365', '<h1 class="mb-4">Practice Office 365 : Intégration, Paramétrage & Accompagnement</h1>', '<h2 class="mb-4">Practice Office 365 : Intégration, Paramétrage & Accompagnement</h2>');
        $this->replaceFragment('services', 'description', 'msbi', '<h1 class="mb-4">Practice Power BI : Connecteurs, Modélisation &amp; Tableaux de bord</h1>', '<h2 class="mb-4">Practice Power BI : Connecteurs, Modélisation &amp; Tableaux de bord</h2>');
        $this->replaceFragment('services', 'description', 'assistance-a-maitrise-douvrage', '<h2 class="mb-5">Assistance à Maîtrise d’Ouvrage (AMOA) & Pilotage SI</h1>', '<h2 class="mb-5">Assistance à Maîtrise d’Ouvrage (AMOA) & Pilotage SI</h2>');
        $this->replaceFragment('services', 'description', 'qse', '<h1 class="mb-5 text-center">Expertise QSE – Direction Qualité Externalisée & Audits Complexes</h1>', '<h2 class="mb-5 text-center">Expertise QSE – Direction Qualité Externalisée & Audits Complexes</h2>');
        $this->replaceFragment('services', 'description', 'rgpd', '<h1 class="mb-5 text-center">DPO Externalisé &amp; Accompagnement RGPD</h1>', '<h2 class="mb-5 text-center">DPO Externalisé &amp; Accompagnement RGPD</h2>');
        $this->replaceFragment('services', 'description', 'rse', '<h1 class="mb-4">Expertise QSE – Direction Qualité Externalisée & Audits Complexes</h1>', '<h2 class="mb-4">Certification RSE</h2>');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE legal_page SET body = REPLACE(body, '<p>Dernière mise à jour le jeudi 28 février 2026</p>', '<p>Dernière mise à jour le jeudi 28 février 2026</p>".str_replace("'", "''", self::LEGAL_NOTE)."'), updated_at = NOW() WHERE slug = 'mentions-legales' AND body NOT LIKE '%Ces mentions légales sont données à titre d''exemple%'");

        $this->replaceExact('site_page', 'title', 'facturation-electronique-amoa', 'AMOA facturation électronique | Plateforme agréée et déploiement | OLING', 'AMOA facturation electronique | Trajectoire PPF PDP et deploiement | OLING');
        $this->replaceExact('site_page', 'meta_description', 'facturation-electronique-amoa', 'Facturation électronique AMOA : cadrage des impacts métier et SI, choix de plateforme agréée, trajectoire e-invoicing/e-reporting, gouvernance et déploiement.', 'Facturation electronique AMOA : cadrage des impacts metier et SI, choix PPF ou PDP, trajectoire e invoicing e reporting, gouvernance et deploiement.');
        $this->replaceExact('site_page', 'hero_title', 'facturation-electronique-amoa', "Facturation électronique AMOA : convertir l'obligation en exécution", 'Facturation electronique AMOA : convertir obligation en execution');
        $this->replaceExact('site_page', 'hero_intro', 'facturation-electronique-amoa', 'Depuis le 1er septembre 2026, OLING accompagne les directions financières, métiers et SI pour cadrer, prioriser et déployer la facturation électronique avec une gouvernance claire, des impacts anticipés et une exécution sécurisée.', 'OLING accompagne les directions financieres, metiers et SI pour cadrer, prioriser et deployer la facturation electronique avec une gouvernance claire, des impacts anticipes et une execution securisee.');

        $this->replaceFragment('site_page', 'hero_side_html', 'facturation-electronique-amoa', 'Choix de trajectoire plateforme agréée (PA) et articulation avec les partenaires.', 'Choix de trajectoire PPF/PDP et articulation avec les partenaires.');
        $this->replaceFragment('site_page', 'hero_side_html', 'facturation-electronique-amoa', 'Cadrage impacts métier, SI et gouvernance PA avant déploiement.', 'Cadrage impacts metier, SI et gouvernance PDP/PPF avant deploiement.');
        $this->replaceFragment('site_page', 'hero_side_html', 'facturation-electronique-amoa', 'Plan de bascule e-invoicing/e-reporting piloté par jalons et risques.', 'Plan de bascule e-invoicing/e-reporting pilote par jalons et risques.');
        $this->replaceFragment('site_page', 'body_html', 'facturation-electronique-amoa', 'Comment choisir sa plateforme agréée ?', 'Comment choisir entre PPF et PDP ?');
        $this->replaceFragment('site_page', 'body_html', 'facturation-electronique-amoa', "Le choix dépend des volumes, des cas d'usage, de l'écosystème partenaires, des formats attendus et du niveau d'intégration souhaité avec le SI.", "Le choix depend des volumes, des cas d'usage, de l'ecosysteme partenaires et du niveau d'integration souhaite avec le SI.");
        $this->replaceFragment('site_page', 'body_html', 'facturation-electronique-amoa', 'Le cadrage doit être engagé dès la phase applicable afin de planifier les lots, traiter les dépendances applicatives et éviter une mise en conformité subie.', 'Le plus tot possible pour planifier les lots, traiter les dependances applicatives et eviter une mise en conformite subie.');

        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', 'Plateforme agréée (PA), annuaire et échanges de statuts', 'PPF (portail public), PDP (plateformes partenaires)');
        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', '1) Écosystème : PA, annuaire &amp; statuts', '1) Écosystème : PPF, PDP &amp; annuaire');
        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', '<li><strong>Plateforme agréée (PA)</strong> : contrôle, routage, échanges de statuts et transmission des données.</li>', '<li><strong>PDP</strong> : contrôle, routage, échanges de statuts et transmission des données.</li>');
        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', 'Routage via PA', 'Routage via PDP');
        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', 'Phase B – Choix PA &amp; architecture', 'Phase B – Choix PDP &amp; architecture');
        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', 'Dossier de choix PA &amp; pré-architecture', 'Dossier de choix PDP &amp; pré-architecture');
        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', 'DCE &amp; grille d’analyse (PA)', 'DCE &amp; grille d’analyse (PDP)');
        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', 'transmises via une plateforme agréée', 'transmises via PDP/PPF');
        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', 'Quelle plateforme agréée choisir ?', 'PPF ou PDP : que choisir ?');
        $this->replaceFragment('services', 'description', 'reforme-facturation-electronique-amoa', 'La <strong>plateforme agréée</strong> opère les flux au quotidien, gère les échanges de statuts et transmet les données attendues. Le choix dépend des volumes, des intégrations SI et des contraintes multi-entités.', 'Le <strong>PPF</strong> centralise et collecte les données ; la <strong>PDP</strong> opère les flux au quotidien et gère les échanges inter-plateformes. Une PDP est recommandée pour piloter des SI/filiales multiples.');

        $this->replaceFragment('services', 'description', 'ms365', '<h2 class="mb-4">Practice Office 365 : Intégration, Paramétrage & Accompagnement</h2>', '<h1 class="mb-4">Practice Office 365 : Intégration, Paramétrage & Accompagnement</h1>');
        $this->replaceFragment('services', 'description', 'msbi', '<h2 class="mb-4">Practice Power BI : Connecteurs, Modélisation &amp; Tableaux de bord</h2>', '<h1 class="mb-4">Practice Power BI : Connecteurs, Modélisation &amp; Tableaux de bord</h1>');
        $this->replaceFragment('services', 'description', 'assistance-a-maitrise-douvrage', '<h2 class="mb-5">Assistance à Maîtrise d’Ouvrage (AMOA) & Pilotage SI</h2>', '<h2 class="mb-5">Assistance à Maîtrise d’Ouvrage (AMOA) & Pilotage SI</h1>');
        $this->replaceFragment('services', 'description', 'qse', '<h2 class="mb-5 text-center">Expertise QSE – Direction Qualité Externalisée & Audits Complexes</h2>', '<h1 class="mb-5 text-center">Expertise QSE – Direction Qualité Externalisée & Audits Complexes</h1>');
        $this->replaceFragment('services', 'description', 'rgpd', '<h2 class="mb-5 text-center">DPO Externalisé &amp; Accompagnement RGPD</h2>', '<h1 class="mb-5 text-center">DPO Externalisé &amp; Accompagnement RGPD</h1>');
        $this->replaceFragment('services', 'description', 'rse', '<h2 class="mb-4">Certification RSE</h2>', '<h1 class="mb-4">Expertise QSE – Direction Qualité Externalisée & Audits Complexes</h1>');
    }

    private function replaceExact(string $table, string $field, string $slug, string $before, string $after): void
    {
        $this->addSql(sprintf(
            "UPDATE %s SET %s = '%s' WHERE slug = '%s' AND %s = '%s'",
            $table,
            $field,
            str_replace("'", "''", $after),
            str_replace("'", "''", $slug),
            $field,
            str_replace("'", "''", $before)
        ));
    }

    private function replaceFragment(string $table, string $field, string $slug, string $before, string $after): void
    {
        $this->addSql(sprintf(
            "UPDATE %s SET %s = REPLACE(%s, '%s', '%s') WHERE slug = '%s' AND %s LIKE '%%%s%%'",
            $table,
            $field,
            $field,
            str_replace("'", "''", $before),
            str_replace("'", "''", $after),
            str_replace("'", "''", $slug),
            $field,
            str_replace(['\\', '%', '_', "'"], ['\\\\', '\\%', '\\_', "''"], $before)
        ));
    }
}
