<?php

namespace App\Command;

use App\Repository\ServicesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:content:refine-historical-service-html',
    description: 'Restructure quelques contenus HTML historiques de fiches service pour améliorer la lisibilité.',
)]
class RefineHistoricalServiceHtmlCommand extends Command
{
    public function __construct(
        private readonly ServicesRepository $servicesRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $updates = 0;

        foreach ($this->descriptions() as $slug => $html) {
            $service = $this->servicesRepository->findOneBy(['slug' => $slug]);
            if (!$service) {
                $io->warning(sprintf('Service introuvable: %s', $slug));
                continue;
            }

            $normalizedHtml = trim($html);
            if (trim((string) $service->getDescription()) === $normalizedHtml) {
                continue;
            }

            $service->setDescription($normalizedHtml);
            $this->entityManager->persist($service);
            ++$updates;
        }

        if ($updates === 0) {
            $io->success('Aucune mise à jour nécessaire.');

            return Command::SUCCESS;
        }

        $this->entityManager->flush();

        $io->success(sprintf('%d fiche(s) service mise(s) à jour.', $updates));

        return Command::SUCCESS;
    }

    /**
     * @return array<string, string>
     */
    private function descriptions(): array
    {
        return [
            'assistance-a-maitrise-douvrage' => <<<'HTML'
<h2>Assistance à maîtrise d’ouvrage ERP et métiers</h2>
<p>OLING mobilise une équipe pluridisciplinaire pour accompagner les organisations dans la transformation et la sécurisation de leur système d’information, avec une logique simple : rendre les décisions plus lisibles, les arbitrages plus solides et les déploiements plus tenables.</p>
<p>Nos interventions couvrent les principaux périmètres applicatifs et métiers : SI finance, SI RH, GMAO, négoce, CRM, SAV, gestion des interventions, facturation électronique, GED et infrastructures applicatives.</p>
<h3>Une mission AMOA conduite de bout en bout</h3>
<p>Nous intervenons dès le cadrage du besoin, puis nous restons présents jusqu’à la recette, la mise en production et le transfert de compétences.</p>
<h4>1. Cadrage, besoins et trajectoire</h4>
<ul>
  <li>Analyse des processus métiers, des irritants et des enjeux de transformation</li>
  <li>Animation des ateliers d’expression de besoins et d’analyse fonctionnelle</li>
  <li>Rédaction des cahiers des charges, CCTP et matrices d’exigences</li>
  <li>Définition des architectures cibles et de la trajectoire d’urbanisation SI</li>
  <li>Priorisation, lotissement et formalisation du plan de transformation</li>
</ul>
<h4>2. Consultation et choix de solution</h4>
<ul>
  <li>Préparation des DCE, RFP, CCTP, bordereaux de prix et grilles d’analyse</li>
  <li>Lancement de consultations conformes aux procédures applicables</li>
  <li>Assistance à l’analyse des réponses, soutenances, négociations et arbitrages</li>
  <li>Aide à la décision et appui à la contractualisation</li>
  <li>Coordination juridique, achats et technique sur les points sensibles</li>
</ul>
<h4>3. Conception, paramétrage et déploiement</h4>
<ul>
  <li>Organisation des ateliers de conception fonctionnelle et technique</li>
  <li>Validation des livrables de paramétrage, interfaces et spécifications</li>
  <li>Pilotage des intégrateurs, suivi des décisions et animation des instances</li>
  <li>Accompagnement au changement, à la formation et à la documentation utilisateur</li>
  <li>Préparation de l’assistance post-déploiement</li>
</ul>
<h4>4. Recette, qualité et passage en exploitation</h4>
<ul>
  <li>Mise en place des plans de tests unitaires, d’intégration et utilisateurs</li>
  <li>Suivi de la recette, gestion des anomalies et tableaux de bord qualité</li>
  <li>Validation d’aptitude, mise en production et VSR</li>
  <li>Suivi des engagements, reporting et clôture opérationnelle</li>
  <li>Transfert de compétences vers les équipes internes</li>
</ul>
<h3>Domaines couverts</h3>
<ul>
  <li>SI finances : budget, achats, comptabilité, facturation électronique</li>
  <li>SI RH : recrutement, carrière, paie, GTA, entretiens</li>
  <li>CRM, négoce, SAV et gestion des interventions</li>
  <li>GMAO, patrimoine, maintenance et stocks</li>
  <li>GED, dématérialisation et portails collaboratifs</li>
</ul>
<h3>Ce que nous apportons au pilotage</h3>
<ul>
  <li>Une méthode éprouvée sur plus de 100 projets</li>
  <li>Des outils de coordination partagés : Microsoft 365, Teams, Trello, MAPSI</li>
  <li>Des instances de suivi cadencées et un reporting utile pour décider</li>
  <li>Des indicateurs de charges, de risques, de planning et de qualité</li>
  <li>Une équipe senior engagée sur le résultat opérationnel</li>
</ul>
<p>OLING sécurise ainsi le passage entre intention stratégique, choix de solution et mise en service réelle.</p>
HTML,
            'pilotage-des-plans' => <<<'HTML'
<h2>Conduite de projet SI technologique et fonctionnelle</h2>
<p>La réussite d’un projet SI dépend moins d’un outil seul que de la qualité du cadrage, du rythme de décision, de la gouvernance et de la capacité à tenir les dépendances dans la durée.</p>
<p>OLING accompagne les projets technologiques comme les projets métiers avec une approche de pilotage structurée, outillée et directement exploitable par les directions, les équipes internes et les prestataires.</p>
<h3>Notre démarche de conduite de projet</h3>
<ul>
  <li>Diagnostic initial, analyse de l’existant et cadrage stratégique</li>
  <li>Définition des objectifs, livrables attendus et indicateurs de performance</li>
  <li>Élaboration du plan d’assurance qualité projet, des jalons et des instances</li>
  <li>Construction du macro-planning et du phasage opérationnel</li>
  <li>Mise en place de la gouvernance : COPIL, COPROJ, cellules techniques</li>
  <li>Animation des instances, suivi des décisions et gestion des arbitrages</li>
  <li>Suivi de l’avancement, alertes, actions correctives et gestion du changement</li>
  <li>Pilotage des risques, des opportunités et des demandes d’évolution</li>
  <li>Coordination des parties prenantes internes et externes</li>
  <li>Validation documentaire, reporting et sécurisation des livrables</li>
</ul>
<h3>Typologies de projets pilotés</h3>
<ul>
  <li>ERP, CRM, SIRH, GMAO, GED</li>
  <li>Migrations, cloud, cybersécurité, téléphonie et réseaux</li>
  <li>Intégration de solutions SaaS et chantiers d’interopérabilité SI</li>
</ul>
<h3>Livrables que nous structurons</h3>
<ul>
  <li>Plan projet, roadmap, WBS, Gantt et jalons validés</li>
  <li>PAQ complet et registre des engagements</li>
  <li>Registres de risques, décisions, actions et dépendances</li>
  <li>Supports COPIL, comptes rendus et tableaux de bord</li>
  <li>Kit de communication et d’accompagnement au changement</li>
</ul>
<h3>Pourquoi OLING</h3>
<ul>
  <li>Cabinet indépendant focalisé sur les projets numériques complexes</li>
  <li>Consultants expérimentés en AMOA et direction de projet</li>
  <li>Méthodologie alignée avec les référentiels de pilotage reconnus</li>
  <li>Souplesse d’intervention : assistance, pilotage global ou renfort ciblé</li>
</ul>
<h3>Un pilotage appuyé par MAPSI</h3>
<p>Nos consultants s’appuient sur MAPSI pour centraliser l’information utile au pilotage et tenir un suivi partagé dans le temps.</p>
<ul>
  <li>Fiches projets, programmes et portefeuilles avec jalons, acteurs et budgets</li>
  <li>Plans d’action, tâches, échéances et responsables</li>
  <li>Vues planning, commentaires, décisions et relances</li>
  <li>Traçabilité des arbitrages et des alertes projet</li>
</ul>
<p>OLING transforme ainsi des projets sensibles en trajectoires pilotables, arbitrables et livrables.</p>
HTML,
            'plan-strategique' => <<<'HTML'
<h2>Schéma directeur et plan stratégique numérique</h2>
<p>OLING accompagne les entreprises et les organisations dans la définition d’une trajectoire numérique claire, priorisée et réaliste. L’objectif n’est pas de produire un document de plus, mais un cadre de décision directement exploitable pour piloter les projets, les investissements et les arbitrages.</p>
<h3>Notre approche</h3>
<ul>
  <li>Évaluation de la maturité numérique et analyse de l’existant</li>
  <li>Identification des opportunités, des contraintes et des dépendances</li>
  <li>Définition des objectifs cibles et des priorités de transformation</li>
  <li>Construction du plan d’action et du schéma directeur digital</li>
  <li>Accompagnement à la mise en œuvre, au suivi et à la montée en compétence</li>
</ul>
<h3>Ce que cela permet</h3>
<p>Un schéma directeur utile doit aider à séquencer les chantiers, objectiver les choix et donner une vision commune aux directions métiers, à la DSI et aux partenaires externes.</p>
<h3>Retours d’expérience mobilisables</h3>
<p>Nos consultants ont accompagné des structures de tailles variées, par exemple sur la mise en place d’une plateforme e-commerce ou sur la digitalisation de processus métier au sein d’organisations de services publics. Dans chaque cas, la valeur vient d’une combinaison entre vision stratégique, priorisation réaliste et discipline d’exécution.</p>
<p>OLING apporte ce cadre pour transformer une ambition numérique en feuille de route lisible et tenable.</p>
HTML,
            'rgpd' => <<<'HTML'
<h2>DPO externalisé et accompagnement RGPD</h2>
<p>Depuis 2012, OLING accompagne les organisations publiques et privées dans la mise en conformité au RGPD avec une cellule DPO externalisée expérimentée, certifiée et opérationnelle.</p>
<h3>Une cellule experte et mobilisable</h3>
<ul>
  <li>Équipe dédiée de consultants RGPD certifiés</li>
  <li>Appui juridique spécialisé en droit du numérique</li>
  <li>Certification ISO 27001 et compétences DPO certifiées</li>
  <li>Réactivité renforcée en cas de crise, de violation ou de contrôle CNIL</li>
</ul>
<h3>Périmètre des missions prises en charge</h3>
<ul>
  <li>Diagnostic initial et construction du plan de conformité RGPD</li>
  <li>Tenue des registres de traitements, de sous-traitants et d’incidents</li>
  <li>Réalisation des analyses d’impact et accompagnement DPIA</li>
  <li>Gestion des demandes d’exercice de droits</li>
  <li>Assistance aux contrôles CNIL et organisation de la cellule de crise</li>
  <li>Rédaction des clauses, mentions, procédures et documents de conformité</li>
  <li>Sensibilisation, formation et animation des relais internes</li>
  <li>Veille réglementaire et support continu aux métiers</li>
</ul>
<h3>Notre méthodologie</h3>
<h4>Audit et cadrage</h4>
<p>Nous évaluons la maturité RGPD, consolidons la base documentaire existante et priorisons les actions selon les risques réels.</p>
<h4>Structuration opérationnelle</h4>
<p>Nous organisons les registres, les cartographies de traitements, les DPIA, les incidents et la gestion des sous-traitants dans une logique durable et audit-able.</p>
<h4>Accompagnement continu</h4>
<p>Nous assurons le suivi DPO au quotidien, les ateliers métiers, le reporting et les contrôles internes nécessaires pour tenir la conformité dans le temps.</p>
<h4>Gestion des situations sensibles</h4>
<p>En cas de crise ou de contrôle, nous activons un dispositif de réponse structuré, avec appui à la notification, à la coordination interne et à la communication.</p>
<h3>Formations et outils</h3>
<ul>
  <li>Sensibilisation des collaborateurs et formation des référents RGPD</li>
  <li>Ateliers spécialisés sur les droits, la sécurité, la sous-traitance et les transferts</li>
  <li>Registres dynamiques, plans d’action, cartographies et tableaux de bord</li>
  <li>MAPSI comme socle de pilotage, de traçabilité et de reporting</li>
</ul>
<h3>Pourquoi externaliser avec OLING</h3>
<ul>
  <li>Coût maîtrisé et équipe mutualisée à haut niveau d’expertise</li>
  <li>Indépendance du DPO et interlocuteur structurant pour la direction</li>
  <li>Accès à des compétences complémentaires en SI, cybersécurité, ISO et audit</li>
  <li>Traçabilité, veille et production de bilans exploitables</li>
</ul>
<p>OLING structure ainsi une conformité RGPD continue, documentée et réellement pilotable.</p>
HTML,
            'qse' => <<<'HTML'
<h2>Expertise QSE, direction qualité externalisée et audits complexes</h2>
<p>OLING intervient auprès des entreprises, collectivités et grands projets pour structurer, piloter et renforcer les dispositifs QSE avec une approche opérationnelle, certifiable et directement utile au management.</p>
<p>Nous assurons des missions de direction qualité externalisée, d’audit QSE, d’animation de systèmes de management ISO et d’appui à la certification.</p>
<h3>Domaines d’intervention</h3>
<ul>
  <li>Direction QSE externalisée pour entités multi-sites ou projets stratégiques</li>
  <li>Préparation et accompagnement aux certifications ISO 9001, 14001, 45001, 50001</li>
  <li>Systèmes intégrés qualité, sécurité et environnement</li>
  <li>Audits QSE selon référentiels ISO ou exigences sectorielles</li>
  <li>Construction d’indicateurs, de plans d’action et de dispositifs de veille</li>
  <li>Structuration documentaire : manuels, procédures, référentiels et preuves</li>
</ul>
<h3>Nos modes d’intervention</h3>
<h4>Direction déléguée</h4>
<p>Nous prenons en charge la fonction qualité, sécurité ou conformité avec responsabilité de pilotage, coordination des équipes, reporting consolidé et suivi des plans d’amélioration.</p>
<h4>Assistance technique</h4>
<p>Nous intervenons en appui des équipes métiers pour diagnostiquer, structurer, produire les livrables et mettre en place les bons outils de suivi.</p>
<h4>Audit et revue critique</h4>
<p>Nous réalisons des audits internes, des audits de conformité et des revues préparatoires à la certification pour objectiver les écarts et sécuriser les décisions.</p>
<h3>Valeur ajoutée OLING</h3>
<ul>
  <li>Consultants seniors avec double culture qualité et conformité</li>
  <li>Articulation possible entre QSE, RGPD, sécurité de l’information et risques</li>
  <li>Outils de pilotage collaboratifs, dont MAPSI et Power BI</li>
  <li>Approche structurée, pragmatique et orientée preuves</li>
  <li>Références sur des environnements publics, industriels et multi-acteurs</li>
</ul>
<h3>Digitalisation avec MAPSI</h3>
<ul>
  <li>Pilotage des audits, non-conformités et plans d’action</li>
  <li>Gestion documentaire centralisée, historisée et sécurisée</li>
  <li>Tableaux de bord, alertes et indicateurs personnalisés</li>
  <li>Fonctionnement multi-sites, multi-normes et multi-utilisateurs</li>
</ul>
<h3>Sécurité et conformité des interventions</h3>
<ul>
  <li>Pratiques alignées avec les exigences ISO, CNIL et ANSSI</li>
  <li>Confidentialité contractuelle et cloisonnement des accès</li>
  <li>Auditabilité complète des livrables et des actions menées</li>
</ul>
<p>OLING donne ainsi à la démarche QSE une structure plus lisible, plus pilotable et plus crédible face aux audits comme face au terrain.</p>
HTML,
        ];
    }
}
