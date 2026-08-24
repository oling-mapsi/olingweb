<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260824174000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Force SQL alignment of remaining IA expertise site_page rows';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<'SQL'
UPDATE site_page
SET
  title = ?,
  meta_description = ?,
  hero_badge = ?,
  hero_title = ?,
  hero_intro = ?,
  body_html = JSON_SET(
    COALESCE(NULLIF(body_html, ''), '{}'),
    '$.title', ?,
    '$.metaDescription', ?,
    '$.eyebrow', ?,
    '$.intro', ?,
    '$.interventions[0]', ?,
    '$.interventions[1]', ?,
    '$.interventions[2]', ?,
    '$.interventions[3]', ?
  )
WHERE slug = 'expertise-amoa-ia-pilotage-projets-agents'
SQL,
            [
                'AMOA IA, pilotage de projets IA et agents métier | OLING',
                'OLING cadre et pilote les projets IA, organise la gouvernance et déploie des agents métier adaptés au contexte de l’entreprise.',
                'AMOA IA et pilotage',
                'AMOA IA, pilotage de projets IA et agents metier',
                'OLING accompagne les directions et équipes projet dans le cadrage, le pilotage et le déploiement de projets IA, depuis les programmes structurants jusqu’aux agents métier intégrés aux processus existants.',
                'AMOA IA, pilotage de projets IA et agents metier',
                'OLING cadre et pilote les projets IA, organise la gouvernance et déploie des agents métier adaptés au contexte de l’entreprise.',
                'AMOA IA et pilotage',
                'OLING accompagne les directions et équipes projet dans le cadrage, le pilotage et le déploiement de projets IA, depuis les programmes structurants jusqu’aux agents métier intégrés aux processus existants.',
                'Cadrage AMOA IA, priorisation des cas d\'usage et construction de la feuille de route.',
                'Coordination avec les métiers, la DSI, la data, la sécurité et les partenaires.',
                'Pilotage des chantiers IA : gouvernance, lots, arbitrages, dépendances et recette.',
                'Conception et intégration d\'agents métier adaptés aux processus cibles.',
            ]
        );

        $this->addSql(
            <<<'SQL'
UPDATE site_page
SET
  title = ?,
  meta_description = ?,
  hero_badge = ?,
  hero_title = ?,
  hero_intro = ?,
  body_html = JSON_SET(
    COALESCE(NULLIF(body_html, ''), '{}'),
    '$.title', ?,
    '$.metaDescription', ?,
    '$.intro', ?,
    '$.interventions[0]', ?,
    '$.interventions[1]', ?,
    '$.interventions[2]', ?,
    '$.interventions[3]', ?
  )
WHERE slug = 'expertise-transformation-digitale-ia-pme-pmi'
SQL,
            [
                'Transformation digitale, automatisation et IA pour TPE, PME et PMI | OLING',
                'OLING accompagne la transformation digitale des TPE, PME et PMI avec des automatisations, des usages IA et des applications métiers adaptés aux opérations.',
                'Transformation digitale et performance',
                'Transformation digitale, automatisation et IA',
                'OLING accompagne l’automatisation des processus, l’intégration de solutions IA et l’évolution des applications métiers, depuis l’identification des usages jusqu’au déploiement.',
                'Transformation digitale, automatisation et IA',
                'OLING accompagne la transformation digitale des TPE, PME et PMI avec des automatisations, des usages IA et des applications métiers adaptés aux opérations.',
                'OLING accompagne l’automatisation des processus, l’intégration de solutions IA et l’évolution des applications métiers, depuis l’identification des usages jusqu’au déploiement.',
                'Diagnostic de maturité digitale et sélection des chantiers prioritaires.',
                'Refonte de processus, automatisation et simplification des circuits d’information.',
                'Intégration d’agents, d’assistants et d’outils IA dans les environnements déjà en place.',
                'Pilotage de transformation, conduite du changement et mesure des gains réels.',
            ]
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            <<<'SQL'
UPDATE site_page
SET
  title = ?,
  meta_description = ?,
  hero_badge = ?,
  hero_title = ?,
  hero_intro = ?,
  body_html = JSON_SET(
    COALESCE(NULLIF(body_html, ''), '{}'),
    '$.title', ?,
    '$.metaDescription', ?,
    '$.eyebrow', ?,
    '$.intro', ?,
    '$.interventions[0]', ?,
    '$.interventions[1]', ?,
    '$.interventions[2]', ?,
    '$.interventions[3]', ?
  )
WHERE slug = 'expertise-amoa-ia-pilotage-projets-agents'
SQL,
            [
                'AMOA IA, pilotage de projets IA et agents métier | OLING',
                'OLING cadre, co-construit et pilote les projets IA: AMOA IA, grands chantiers de transformation, agents adaptes aux TPE, PME et ETI.',
                'AMOA IA et execution terrain',
                'AMOA IA, pilotage de programmes et agents metier',
                'OLING accompagne les directions et equipes projet sur toutes les facettes des programmes IA: cadrer, construire, co-construire, arbitrer et faire tenir des deploiements utiles, du chantier strategique aux agents metier pour TPE et PME.',
                'AMOA IA, pilotage de programmes et agents metier',
                'OLING cadre, co-construit et pilote les projets IA: AMOA IA, grands chantiers de transformation, agents adaptes aux TPE, PME et ETI.',
                'AMOA IA et execution terrain',
                'OLING accompagne les directions et equipes projet sur toutes les facettes des programmes IA: cadrer, construire, co-construire, arbitrer et faire tenir des deploiements utiles, du chantier strategique aux agents metier pour TPE et PME.',
                'Cadrage AMOA IA, priorisation des cas d\'usage et construction de la trajectoire.',
                'Co-construction avec les metiers, la DSI et les partenaires pour tenir valeur, delais et responsabilites.',
                'Pilotage de grands chantiers IA: gouvernance, lots, arbitrages, dependances et recettes.',
                'Conception et integration d\'agents adaptes aux TPE, PME et ETI sur leurs processus cibles.',
            ]
        );

        $this->addSql(
            <<<'SQL'
UPDATE site_page
SET
  title = ?,
  meta_description = ?,
  hero_badge = ?,
  hero_title = ?,
  hero_intro = ?,
  body_html = JSON_SET(
    COALESCE(NULLIF(body_html, ''), '{}'),
    '$.title', ?,
    '$.metaDescription', ?,
    '$.intro', ?,
    '$.interventions[0]', ?,
    '$.interventions[1]', ?,
    '$.interventions[2]', ?,
    '$.interventions[3]', ?
  )
WHERE slug = 'expertise-transformation-digitale-ia-pme-pmi'
SQL,
            [
                'Transformation digitale, automatisation et IA pour TPE, PME et PMI | OLING',
                'OLING accelere la transformation digitale des TPE, PME et PMI avec des projets IA utiles, des automatisations tenables et des agents integres aux operations.',
                'Transformation digitale et performance',
                'Transformation digitale, automatisation et IA utile',
                'OLING relie transformation digitale, automatisation et intelligence artificielle a la realite des operations: gains concrets, adoption, outillage tenable et agents integres aux usages quotidiens.',
                'Transformation digitale, automatisation et IA utile',
                'OLING accelere la transformation digitale des TPE, PME et PMI avec des projets IA utiles, des automatisations tenables et des agents integres aux operations.',
                'OLING relie transformation digitale, automatisation et intelligence artificielle a la realite des operations: gains concrets, adoption, outillage tenable et agents integres aux usages quotidiens.',
                'Diagnostic de maturite digitale et selection des chantiers prioritaires.',
                'Refonte de processus, automatisation et simplification des circuits d\'information.',
                'Integration d\'agents, copilotes et outils IA dans les environnements deja en place.',
                'Pilotage de transformation, conduite du changement et mesure des gains reels.',
            ]
        );
    }
}
