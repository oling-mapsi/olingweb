<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260809150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create three new services attached to practices for IA AMOA, IA conformity and digital transformation';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();
        if ($platform !== 'mysql') {
            return;
        }

        $practiceIds = [
            'consulting' => $this->connection->fetchOne('SELECT id FROM practice WHERE slug = ?', ['consulting']),
            'expertises-audit' => $this->connection->fetchOne('SELECT id FROM practice WHERE slug = ?', ['expertises-audit']),
            'business-apps' => $this->connection->fetchOne('SELECT id FROM practice WHERE slug = ?', ['business-apps']),
        ];

        $rows = [
            [
                'slug' => 'amoa-ia-pilotage-de-projets-ia-et-agents-metier',
                'designation' => 'AMOA IA, pilotage de projets IA et agents métier',
                'introduction_short' => 'AMOA IA pour cadrer, co-construire et piloter des projets IA, depuis les grands chantiers jusqu’aux agents adaptés aux TPE, PME et ETI.',
                'description_short' => 'Cadrage AMOA IA, gouvernance, lotissement, pilotage et déploiement d’agents métier.',
                'designation_short' => 'AMOA IA',
                'ico' => 'bi bi-cpu',
                'image1' => '/img/spe/projet.png',
                'image2' => '/img/spe/projet.png',
                'image_hero' => '/img/spe/projet.png',
                'practice_id' => $practiceIds['consulting'],
                'description' => <<<'HTML'
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
<h3>Livrables attendus</h3>
<ul>
  <li>Note de cadrage IA et backlog priorisé.</li>
  <li>Gouvernance, RACI et comitologie de pilotage.</li>
  <li>Architecture fonctionnelle et plan de déploiement.</li>
  <li>Dispositif de validation, suivi et amélioration continue.</li>
</ul>
HTML,
            ],
            [
                'slug' => 'conformite-ia-gouvernance-et-ai-act',
                'designation' => 'Conformité IA, gouvernance et AI Act',
                'introduction_short' => 'Structurer la conformité IA, les contrôles, les preuves et la gouvernance des usages pour déployer sans angle mort réglementaire ou opérationnel.',
                'description_short' => 'AI Act, RGPD, gouvernance IA, contrôles, registres d’usage et supervision humaine.',
                'designation_short' => 'Conformité IA',
                'ico' => 'bi bi-shield-check',
                'image1' => '/img/spe/rgpd.png',
                'image2' => '/img/spe/rgpd.png',
                'image_hero' => '/img/spe/rgpd.png',
                'practice_id' => $practiceIds['expertises-audit'],
                'description' => <<<'HTML'
<h2>Conformité IA, gouvernance et AI Act</h2>
<p>OLING aide les organisations à déployer l’IA sans angle mort de responsabilité : AI Act, protection des données, supervision humaine, gestion des fournisseurs et preuves de conformité.</p>
<p>Nous mettons en cohérence gouvernance, sécurité, conformité, achats et contrôle interne pour rendre les usages IA auditable et tenables.</p>
<h3>Ce que couvre l’intervention</h3>
<ul>
  <li>Diagnostic de conformité IA et cartographie des usages.</li>
  <li>Qualification des risques, obligations et responsabilités.</li>
  <li>Définition des politiques, contrôles et principes de supervision humaine.</li>
  <li>Structuration du registre d’usage, des preuves et de la documentation fournisseur.</li>
  <li>Alignement AI Act, RGPD, sécurité et gouvernance interne.</li>
</ul>
<h3>Livrables attendus</h3>
<ul>
  <li>Cartographie des usages IA et matrice de risques.</li>
  <li>Politique IA et cadre de gouvernance.</li>
  <li>Registre de conformité et points de contrôle.</li>
  <li>Plan d’actions de mise en conformité.</li>
</ul>
HTML,
            ],
            [
                'slug' => 'transformation-digitale-automatisation-et-ia-utile',
                'designation' => 'Transformation digitale, automatisation et IA utile',
                'introduction_short' => 'Accélérer la transformation digitale avec des automatisations tenables, des agents utiles et une trajectoire réaliste pour TPE, PME et PMI.',
                'description_short' => 'Transformation digitale, simplification des processus, automatisation, copilotes et agents intégrés aux opérations.',
                'designation_short' => 'Transfo digitale',
                'ico' => 'bi bi-diagram-3',
                'image1' => '/img/spe/transition-digitale-1200x710.jpg',
                'image2' => '/img/spe/transition-digitale-1200x710.jpg',
                'image_hero' => '/img/spe/transition-digitale-1200x710.jpg',
                'practice_id' => $practiceIds['business-apps'],
                'description' => <<<'HTML'
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
            ],
        ];

        foreach ($rows as $row) {
            if (!$row['practice_id']) {
                continue;
            }

            $existingId = $this->connection->fetchOne('SELECT id FROM services WHERE slug = ?', [$row['slug']]);
            $payload = $row;

            if ($existingId !== false && $existingId !== null) {
                $this->connection->update('services', $payload, ['id' => $existingId]);
                continue;
            }

            $this->connection->insert('services', $payload);
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();
        if ($platform !== 'mysql') {
            return;
        }

        $this->addSql("DELETE FROM services WHERE slug IN ('amoa-ia-pilotage-de-projets-ia-et-agents-metier', 'conformite-ia-gouvernance-et-ai-act', 'transformation-digitale-automatisation-et-ia-utile')");
    }
}
