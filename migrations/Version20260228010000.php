<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260228010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add site pages for about sections and seed existing content';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('site_page')) {
            $this->addSql('CREATE TABLE site_page (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(100) NOT NULL, title VARCHAR(255) NOT NULL, meta_description LONGTEXT DEFAULT NULL, hero_badge VARCHAR(255) DEFAULT NULL, hero_title VARCHAR(255) DEFAULT NULL, hero_intro LONGTEXT DEFAULT NULL, hero_side_html LONGTEXT DEFAULT NULL, body_html LONGTEXT DEFAULT NULL, hero_image VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8E131EF2989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        $aboutHeroSide = <<<HTML
<div class="d-grid gap-4">
  <div class="d-flex">
    <div class="flex-shrink-0">
      <i class="bi-file-earmark-text fs-2 text-light"></i>
    </div>
    <div class="flex-grow-1 ms-5">
      <h5 class="text-white">Expertise SI, métiers et conformité</h5>
      <p class="text-white-70">OLING conçoit et pilote des projets liés aux ERP, à la gouvernance des données, à la cybersécurité, au RGPD, à la qualité (ISO 27001, Qualiopi), ainsi qu’à la transformation des processus métiers et à l’assistance à maîtrise d’ouvrage.</p>
    </div>
  </div>

  <div class="d-flex">
    <div class="flex-shrink-0">
      <i class="bi-chat-right-dots fs-2 text-light"></i>
    </div>
    <div class="flex-grow-1 ms-5">
      <h5 class="text-white">Innovation & excellence opérationnelle</h5>
      <p class="text-white-70">Notre approche intègre une veille active, une R&D continue et des outils innovants (dont MAPSI) pour anticiper les évolutions réglementaires et technologiques et garantir une efficacité durable à nos clients.</p>
    </div>
  </div>

  <div class="d-flex">
    <div class="flex-shrink-0">
      <i class="bi-hand-thumbs-up fs-2 text-light"></i>
    </div>
    <div class="flex-grow-1 ms-5">
      <h5 class="text-white">Performance et maîtrise budgétaire</h5>
      <p class="text-white-70">Nos offres sont conçues pour offrir un retour sur investissement rapide grâce à une approche modulaire, personnalisée et pragmatique, parfaitement adaptée aux contraintes budgétaires de chaque organisation.</p>
    </div>
  </div>
</div>
HTML;

        $aboutBody = <<<HTML
<div class="row mb-3">
  <div class="col-10 col-md-7 col-lg-7 offset-1 offset-md-4">
    <h2>Notre mission</h2>
    <p>Être un acteur de confiance dans la transformation digitale, réglementaire et organisationnelle de nos clients, en leur fournissant des solutions innovantes, personnalisées et conformes aux standards les plus exigeants.</p>
  </div>
</div>

<div class="row mb-3">
  <div class="col-10 col-md-7 col-lg-7 offset-1 offset-md-4">
    <h3>Nos domaines d’expertise</h3>
    <p>Transformation numérique, AMOA ERP, cybersécurité, RGPD, audit ISO, QSE, conformité DORA/NIS2, gouvernance des données, solutions collaboratives Microsoft 365, infrastructures hybrides, gestion des risques et PCA.</p>
  </div>
</div>

<div class="row mb-3">
  <div class="col-10 col-md-7 col-lg-7 offset-1 offset-md-4">
    <h3>Notre approche</h3>
    <p>Une démarche collaborative fondée sur l’écoute, la proximité terrain, l’agilité, et une méthodologie éprouvée issue des meilleures pratiques ISO, CNIL, ITIL et DevOps. Nos livrables sont orientés résultats et appropriation utilisateur.</p>
  </div>
</div>

<div class="row mb-5">
  <div class="col-md-9 col-lg-8 offset-md-3">
  </div>
</div>

<div class="row mb-3">
  <div class="col-10 col-md-7 col-lg-7 offset-1 offset-md-4">
    <h3>Notre engagement qualité</h3>
    <p>OLING est certifiée ISO 27001:2022. Nos processus internes sont alignés avec les normes qualité et sécurité les plus strictes pour garantir un haut niveau d’exigence dans chaque projet mené.</p>
  </div>
</div>

<div class="row mt-7 mb-5">
  <div class="col-md-9 col-lg-8 offset-md-3">
    <figure class="blockquote-lg text-center">
      <blockquote class="blockquote">Accompagner nos clients vers l’excellence en intégrant la technologie, la conformité et l’humain dans une même dynamique de performance durable.</blockquote>
    </figure>
  </div>
</div>

<div class="row mb-3">
  <div class="col-10 col-md-7 col-lg-7 offset-1 offset-md-4">
    <h3>Une équipe pluridisciplinaire certifiée</h3>
    <p>Consultants DPO certifiés, auditeurs ISO, chefs de projet AMOA, ingénieurs systèmes et cloud, experts en cybersécurité et formateurs Qualiopi. OLING mobilise une équipe agile et qualifiée sur chaque mission.</p>
  </div>
</div>

<div class="row mb-3">
  <div class="col-10 col-md-7 col-lg-7 offset-1 offset-md-4">
    <h3>Nous rejoindre</h3>
    <p>Envie de participer à la transformation numérique et réglementaire de demain ? Rejoignez OLING, cabinet en pleine croissance, pour évoluer dans un environnement stimulant, exigeant et bienveillant.</p>
  </div>
</div>
HTML;

        $metiersBody = <<<HTML
<div class="row mb-3">
  <div class="col-10 col-md-7 col-lg-7 offset-1 offset-md-4">
    <h3>Expertise adaptée à divers secteurs d'activité</h3>
    <p>Notre approche multisectorielle nous permet d'appliquer des principes robustes et standardisés tout en prenant en compte les particularités et les besoins spécifiques de chaque secteur. Cela nous permet d'offrir à nos clients des solutions sur mesure et adaptées à leurs défis et à leurs objectifs.</p>

    <ul class="list-unstyled list-timeline list-py-3">
      <li class="list-timeline-item">
        <div class="card card-shadow">
          <div class="card-body">
            <div class="d-sm-flex">
              <div class="flex-grow-1 ms-sm-4">
                <h4>Secteur public, Grands Ports et Aéroports</h4>
                <figure class="blockquote-sm">
                  <blockquote class="blockquote"><em>Dans le secteur des transports et collectivités, nous avons contribué à la création d'un catalogue de services pour une collectivité de 2000 agents, optimisant ainsi leur organisation. Nous avons mis en œuvre des ERP pour améliorer la performance opérationnelle et financières dans des grands ports et au sein d'aéroports. Nos actions ont aussi porté sur l'élaboration de schémas d'orientations SI et digitalisation, la mise en conformité aux normes ISO et RGPD, ainsi que l'évaluation des systèmes d'information en termes de continuité.</em></blockquote>
                </figure>
              </div>
            </div>
          </div>
        </div>
      </li>

      <li class="list-timeline-item ms-auto">
        <div class="card card-shadow">
          <div class="card-body">
            <div class="d-sm-flex">
              <div class="flex-grow-1 ms-sm-4">
                <h4>Négoce et distribution</h4>
                <figure class="blockquote-sm">
                  <blockquote class="blockquote"><em>Pour les acteurs du négoce et de la distribution, nous mettons en avant notre expertise en matière d'ERP (tels que X3, Divalto, SAP, etc.), CRM, BI, comptabilité analytique, processus d'achats et stock, de gestion commerciale et de finance. Grâce à cette expertise, nous accompagnons nos clients tout au long du processus d'intégration des solutions applicatives et les aidons à tirer le meilleur parti des outils de collaboration Office 365. Notre objectif est de permettre aux entreprises de moderniser leur fonctionnement opérationnelle et d'optimiser leur organisation.</em></blockquote>
                </figure>
              </div>
            </div>
          </div>
        </div>
      </li>

      <li class="list-timeline-item ms-auto">
        <div class="card card-shadow">
          <div class="card-body">
            <div class="d-sm-flex">
              <div class="flex-grow-1 ms-sm-4">
                <h4>Sociétés des eaux</h4>
                <figure class="blockquote-sm">
                  <blockquote class="blockquote"><em>Pour les sociétés des eaux, nous avons travaillé sur la modernisation des infrastructures de traitement et de distribution, l'optimisation des processus de gestion de l'eau et la mise en œuvre de solutions numériques pour une meilleure surveillance et un meilleur contrôle des réseaux d'eau. Nous avons également aidé ces entreprises à s'adapter aux défis environnementaux et réglementaires.</em></blockquote>
                </figure>
              </div>
            </div>
          </div>
        </div>
      </li>

      <li class="list-timeline-item">
        <div class="card card-shadow">
          <div class="card-body">
            <div class="d-sm-flex">
              <div class="flex-grow-1 ms-sm-4">
                <h4>Bailleurs sociaux et aménageurs</h4>
                <figure class="blockquote-sm">
                  <blockquote class="blockquote"><em>Dans le domaine des bailleurs sociaux et des aménageurs, nous avons accompagné nos clients dans la transformation de leurs processus métier et la mise en place de systèmes d'information adaptés aux besoins spécifiques du secteur. Nous avons également contribué à la mise en œuvre de solutions numériques pour la gestion des logements sociaux, la maintenance des bâtiments et l'amélioration de la qualité de vie des résidents.</em></blockquote>
                </figure>
              </div>
            </div>
          </div>
        </div>
      </li>

      <li class="list-timeline-item ms-auto">
        <div class="card card-shadow">
          <div class="card-body">
            <div class="d-sm-flex">
              <div class="flex-grow-1 ms-sm-4">
                <h4>Formation professionnelle</h4>
                <figure class="blockquote-sm">
                  <blockquote class="blockquote"><em>Enfin, pour les organisations d'apprentissage et de formation, nous avons assuré la conformité QSE, Qualiopi et RGPD. Nous avons mis en place des solutions de formation en ligne, des outils de gestion des compétences et des plateformes de collaboration pour faciliter l'échange de connaissances et le développement des compétences. Nous avons également accompagné ces organisations dans la mise en conformité avec les normes de qualité et les réglementations applicables.</em></blockquote>
                </figure>
              </div>
            </div>
          </div>
        </div>
      </li>
    </ul>
  </div>
</div>
HTML;

        $clientHeroSide = <<<HTML
<div class="d-grid gap-4">
  <div class="d-flex">
    <div class="flex-shrink-0">
      <i class="bi-file-earmark-text fs-2 text-light"></i>
    </div>
    <div class="flex-grow-1 ms-5">
      <h5 class="text-white">Comprendre les besoins, précisement.</h5>
      <p class="text-white-70">En écoutant attentivement les enjeux spécifiques de chaque client, nous adaptons nos solutions pour répondre au mieux à leurs attentes.</p>
    </div>
  </div>

  <div class="d-flex">
    <div class="flex-shrink-0">
      <i class="bi-chat-right-dots fs-2 text-light"></i>
    </div>
    <div class="flex-grow-1 ms-5">
      <h5 class="text-white">Personnaliser l'offre pour un accompagnement sur-mesure</h5>
      <p class="text-white-70">Notre approche sur-mesure permet de proposer des stratégies et des solutions adaptées à chaque contexte organisationnel et technologique.</p>
    </div>
  </div>

  <div class="d-flex">
    <div class="flex-shrink-0">
      <i class="bi-hand-thumbs-up fs-2 text-light"></i>
    </div>
    <div class="flex-grow-1 ms-5">
      <h5 class="text-white">Cultiver des relations durables</h5>
      <p class="text-white-70">Nous mettons tout en œuvre pour créer un climat de confiance avec nos clients, en restant à leur écoute et en nous engageant à leurs côtés pour assurer leur réussite.</p>
    </div>
  </div>
</div>
HTML;

        $clientBody = <<<HTML
<div class="row mb-3">
  <div class="col-10 col-md-7 col-lg-8 offset-1 offset-md-4">
    <a data-fslightbox="html5-video" data-video-poster="../img/spe/clients.png" href="../img/spe/clients.png">
      <img class="img-fluid" src="/img/spe/clients.png" alt="Image Description">
    </a>
  </div>
</div>
HTML;

        $teamBody = <<<HTML
<h4>Expertise et passion au service de vos projets !</h4>
<p>Au sein d'OLING, notre équipe dynamique et passionnée est prête à relever tous les défis. Composée d'experts chevronnés et de talents prometteurs, nous sommes animés par la soif de réussite et le désir d'accompagner nos clients vers l’excellence.
<br />Nous mettons notre expertise, notre savoir-faire et notre dévouement au service de chaque projet, transformant les ambitions de nos clients en réalités concrètes. Ensemble, nous cultivons la réussite et savourons chaque victoire.</p>
<blockquote class="blockquote m-6">Rejoignez-nous et laissez-vous porter par notre élan d'enthousiasme et de professionnalisme !</blockquote>
HTML;

        $this->insertPageIfMissing('apropos', [
            'title' => 'À propos | Cabinet de conseil AMOA SI - OLING',
            'meta_description' => 'OLING, cabinet de conseil AMOA SI et transformation digitale. Gouvernance, conformité, ERP et accompagnement des organisations.',
            'hero_badge' => 'À propos de OLING',
            'hero_title' => 'Conseil en transformation digitale, SI et conformité',
            'hero_intro' => 'Depuis 2012, OLING accompagne les organisations publiques et privées dans leur modernisation digitale, leur conformité réglementaire et la performance de leurs systèmes d’information.',
            'hero_side_html' => $aboutHeroSide,
            'body_html' => $aboutBody,
        ]);

        $this->insertPageIfMissing('metiers', [
            'title' => 'Secteurs d’activité | Conseil AMOA SI - OLING',
            'meta_description' => 'Expertise multisectorielle en AMOA SI, transformation digitale et conformité pour acteurs publics et privés.',
            'hero_badge' => 'Expertises métier',
            'hero_title' => 'Un partenaire expert pour une transformation adaptée à votre métier',
            'hero_intro' => null,
            'hero_side_html' => null,
            'body_html' => $metiersBody,
        ]);

        $this->insertPageIfMissing('client', [
            'title' => 'Références clients | Conseil AMOA SI - OLING',
            'meta_description' => 'Découvrez nos références clients en AMOA SI, transformation digitale, conformité RGPD et gouvernance, en France et Outre-mer.',
            'hero_badge' => 'Nos références',
            'hero_title' => 'Nos clients sont au cœur de notre démarche',
            'hero_intro' => "Nous travaillons avec une grande variété d'organisations, allant des PME aux grands groupes, dans des secteurs aussi divers que les infrastructures, les applications, les systèmes, la finance, les ressources humaines, le négoce et la normalisation",
            'hero_side_html' => $clientHeroSide,
            'body_html' => $clientBody,
        ]);

        $this->insertPageIfMissing('team', [
            'title' => "L'équipe | Experts AMOA SI - OLING",
            'meta_description' => "Une équipe d'experts AMOA SI, transformation digitale, conformité et cybersécurité pour accompagner vos projets stratégiques.",
            'hero_badge' => 'Expertises métier',
            'hero_title' => "Une équipe d'experts pluridisciplinaires",
            'hero_intro' => null,
            'hero_side_html' => null,
            'body_html' => $teamBody,
        ]);
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('site_page')) {
            $this->addSql('DROP TABLE site_page');
        }
    }

    private function insertPageIfMissing(string $slug, array $data): void
    {
        $title = $this->q($data['title'] ?? null);
        $meta = $this->q($data['meta_description'] ?? null);
        $heroBadge = $this->q($data['hero_badge'] ?? null);
        $heroTitle = $this->q($data['hero_title'] ?? null);
        $heroIntro = $this->q($data['hero_intro'] ?? null);
        $heroSide = $this->q($data['hero_side_html'] ?? null);
        $bodyHtml = $this->q($data['body_html'] ?? null);
        $heroImage = $this->q($data['hero_image'] ?? null);

        $this->addSql(
            "INSERT INTO site_page (slug, title, meta_description, hero_badge, hero_title, hero_intro, hero_side_html, body_html, hero_image)
            SELECT " . $this->q($slug) . ", $title, $meta, $heroBadge, $heroTitle, $heroIntro, $heroSide, $bodyHtml, $heroImage
            WHERE NOT EXISTS (SELECT 1 FROM site_page WHERE slug = " . $this->q($slug) . ")"
        );
    }

    private function q(?string $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        return "'" . str_replace("'", "''", $value) . "'";
    }
}
