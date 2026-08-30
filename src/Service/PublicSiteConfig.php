<?php

namespace App\Service;

use App\Entity\Practice;
use App\Entity\Services;

class PublicSiteConfig
{
    public function getHome(): array
    {
        return [
            'seoTitle' => 'OLING | AMOA SI, ERP, CRM, GMAO & conformité',
            'metaDescription' => 'Cabinet de conseil AMOA SI : cadrage et pilotage ERP, CRM, GMAO, SIRH, SI Finance, schéma directeur, RGPD, ISO 27001, NIS2/DORA. France et DROM.',
            'hero' => [
                'badge' => '',
                'eyebrow' => 'Cabinet de conseil AMOA SI depuis 2012',
                'titleLines' => ['Cadrer et piloter', 'vos projets SI, progiciels', 'et conformité'],
                'intro' => 'OLING accompagne les directions générales, DSI et directions métiers dans le cadrage et le pilotage de projets ERP, CRM, GMAO, SIRH et SI Finance, ainsi que sur la gouvernance SI, le RGPD, les risques et les démarches ISO.',
                'secondaryCta' => ['label' => 'Découvrir nos expertises', 'route' => 'expertises_index'],
                'tags' => ['AMOA SI', 'ERP · CRM · GMAO', 'RGPD · ISO 27001', 'MAPSI'],
                'portraitImage' => '/uploads/metiers/hero/transport-intermodal-69a3a08f1beb31.17401526.jpg',
                'portraitAlt' => 'Projet SI métier',
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
                'metierSlug' => null,
                'metierIntro' => null,
            ],
            'kpisSection' => [
                'eyebrow' => 'Repères',
                'title' => 'Des repères simples sur le périmètre OLING',
            ],
            'kpis' => [
                ['variant' => 'dark', 'label' => '2012', 'text' => 'Création d’OLING'],
                ['variant' => 'blue', 'label' => 'ISO 27001:2022', 'text' => 'Certification OLING'],
                ['variant' => 'pink', 'label' => 'Hexagone & DROM', 'text' => 'Interventions sur site et à distance'],
                ['variant' => 'image', 'image' => '/img/spe/bureau.jpg', 'alt' => 'Equipe OLING'],
            ],
            'practices' => [
                'eyebrow' => 'Expertises',
                'title' => '',
            ],
            'accompaniments' => [
                'eyebrow' => 'Accompagnements',
                'title' => '',
                'items' => [],
            ],
            'proof' => [
                'eyebrow' => 'Méthode d’intervention',
                'title' => '',
                'items' => [],
                'image' => '/img/spe/bureau.jpg',
                'imageAlt' => 'Equipe OLING au travail',
            ],
            'projects' => [
                'eyebrow' => 'Références',
                'title' => '',
                'intro' => '',
            ],
            'resources' => [
                'eyebrow' => 'Ressources',
                'title' => '',
                'intro' => '',
                'cta' => ['label' => 'Voir toutes les ressources', 'route' => 'seo_resources_index'],
            ],
            'finalCta' => [
                'eyebrow' => 'Diagnostic',
                'title' => '',
                'text' => '',
                'primaryCta' => ['label' => 'Demander un diagnostic', 'route' => 'contact'],
            ],
        ];
    }

    public function getEditorialPages(): array
    {
        return [
            'apropos' => [
                'seoTitle' => 'Cabinet | OLING',
                'metaDescription' => 'OLING, cabinet de conseil pour PME, PMI et ETI : transformation SI, organisation, conformité, cybersécurité et intelligence artificielle.',
                'eyebrow' => 'Cabinet OLING',
                'title' => 'Un cabinet de conseil engagé sur les projets qui structurent l\'entreprise',
                'intro' => 'OLING accompagne les PME, PMI et ETI sur les projets ERP, SI, conformité, RGPD et IA qui demandent un cadrage solide, une gouvernance claire et un pilotage constant.',
                'highlights' => ['Transformation et AMOA ERP', 'Risques, conformité et résilience', 'IA cadrée et gouvernée'],
                'primaryCta' => ['route' => 'contact', 'label' => 'Parler de votre projet'],
                'secondaryCta' => ['route' => 'team', 'label' => 'Voir l\'équipe'],
                'sections' => [
                    [
                        'layout' => 'two_col',
                        'items' => [
                            [
                                'kicker' => 'Positionnement',
                                'title' => 'Deux fronts, une seule logique de maîtrise',
                                'text' => 'OLING intervient sur la transformation des opérations et des systèmes d\'information, ainsi que sur les risques, la conformité et la résilience. Dans les deux cas, le travail porte sur les décisions, les dépendances et les conditions d\'exécution.',
                            ],
                            [
                                'kicker' => 'Différence',
                                'title' => 'Une lecture transverse, pas un silo de plus',
                                'list' => [
                                    'Vision métier, organisation, systèmes, données et conformité.',
                                    'Capacité à intervenir du diagnostic au pilotage opérationnel.',
                                    'Usage maîtrisé de l\'IA, traité comme un sujet de gouvernance et d\'intégration.',
                                ],
                            ],
                        ],
                    ],
                ],
                'teamSection' => [
                    'kicker' => 'Équipe',
                    'title' => 'Des profils conseil, terrain et conformité déjà engagés sur les missions',
                    'intro' => 'Nous avons remis en avant la présentation équipe plutôt que de la laisser dans un parcours secondaire uniquement.',
                    'primaryCta' => ['route' => 'team', 'label' => 'Voir toute l\'équipe'],
                    'secondaryCta' => ['route' => 'contact', 'label' => 'Parler à un consultant'],
                ],
                'bodySection' => [
                    'kicker' => 'Cabinet',
                    'title' => 'Une posture engagée, mais d\'abord utile à la décision',
                ],
                'linksSection' => [
                    'kicker' => 'Accès utiles',
                    'title' => 'MAPSI, politiques, équipe et parcours cabinet restent accessibles',
                    'links' => [
                        [
                            'kicker' => 'Équipe',
                            'title' => 'Présentation équipe',
                            'text' => 'Retrouver les profils, expertises et parcours des consultants OLING.',
                            'route' => 'team',
                            'moreLabel' => 'Voir l\'équipe',
                        ],
                        [
                            'kicker' => 'Cabinet',
                            'title' => 'Démarche RSE',
                            'text' => 'Nos engagements, notre gouvernance et notre cadre de responsabilité.',
                            'route' => 'rse',
                            'moreLabel' => 'Voir la démarche',
                        ],
                        [
                            'kicker' => 'MAPSI',
                            'title' => 'Plateforme et écosystème',
                            'text' => 'Accès à l\'univers MAPSI et aux dispositifs produits reliés au cabinet.',
                            'url' => 'https://mapsi.fr/',
                            'moreLabel' => 'Ouvrir MAPSI',
                        ],
                    ],
                ],
                'cta' => [
                    'title' => 'Vérifier si OLING est le bon cadre d\'intervention pour votre sujet',
                    'text' => 'Nous pouvons qualifier rapidement votre situation, dire où nous apportons de la valeur et où un autre dispositif serait plus adapté.',
                    'primaryCta' => ['route' => 'contact', 'label' => 'Demander un échange'],
                ],
            ],
            'contact' => [
                'seoTitle' => 'Contact | OLING',
                'metaDescription' => 'Contactez OLING pour un besoin de transformation SI, ERP, organisation, conformité, cybersécurité ou intelligence artificielle.',
                'eyebrow' => 'Contact',
                'title' => 'Exposez votre contexte. Nous clarifions la suite, les priorités et le bon niveau d\'intervention',
                'intro' => 'OLING intervient sur les projets qui demandent un regard indépendant, une trajectoire claire et une exécution pilotable sur des sujets ERP, conformité, cyber, RGPD ou IA.',
                'highlights' => ['Diagnostic rapide', 'Échange de cadrage', 'Réponse exploitable'],
                'requestSection' => [
                    'kicker' => 'Nous solliciter',
                    'title' => 'Ce que vous pouvez nous confier en priorité',
                    'items' => [
                        'Projet ERP, CRM ou GMAO à cadrer ou remettre en trajectoire.',
                        'Transformation SI, organisationnelle ou gouvernance à structurer.',
                        'Question de conformité, cybersécurité, RGPD, résilience ou IA.',
                    ],
                ],
                'presenceSection' => [
                    'title' => 'Présence',
                    'text' => 'Hexagone et DROM. OLING intervient pour des structures multisites, des PME, des PMI, des ETI et des organisations régulées.',
                ],
                'formSection' => [
                    'kicker' => 'Prise de contact',
                    'title' => 'Envoyez-nous un message',
                    'loadingText' => 'Envoi en cours...',
                    'errorText' => 'Une erreur est survenue. Merci de réessayer.',
                    'successTitle' => 'Merci, votre message est bien envoyé.',
                    'successText' => 'Nous revenons vers vous rapidement pour un échange de cadrage.',
                    'fields' => [
                        'firstName' => ['label' => 'Prénom', 'placeholder' => 'Prénom'],
                        'lastName' => ['label' => 'Nom', 'placeholder' => 'Nom'],
                        'company' => ['label' => 'Société', 'placeholder' => 'Société'],
                        'workEmail' => ['label' => 'Email professionnel', 'placeholder' => 'email@domaine.com'],
                        'details' => ['label' => 'Votre message', 'placeholder' => 'Enjeux, projet, blocages, attentes, contexte...'],
                    ],
                    'consentText' => 'J\'accepte que les données de ce formulaire soient collectées dans le cadre de ma demande.',
                    'legalText' => 'Conformément au RGPD, les informations recueillies via ce formulaire sont nécessaires pour traiter votre demande.',
                    'legalLinkLabel' => 'accéder au formulaire dédié',
                    'legalLinkUrl' => 'https://v6.mapsi.fr/public-form/rgpdaccess/eyJ2IjoxLCJjIjo4LCJlIjoicmdwZGFjY2VzcyJ9.e041dfbf740ee072196f1117efc9b6cbcd69f074510a3e31216784d64f5f6965',
                    'submitLabel' => 'Envoyer le message',
                ],
                'cta' => [
                    'eyebrow' => 'Échange initial',
                    'title' => 'Un premier retour utile, même quand le sujet n\'est pas encore complètement cadré',
                    'text' => 'Vous pouvez nous contacter avec un besoin partiellement formulé. Nous vous aidons à qualifier le niveau d\'urgence, les risques et la prochaine bonne décision.',
                    'primaryCta' => ['route' => 'contact', 'label' => 'Nous écrire'],
                ],
            ],
            'client' => [
                'seoTitle' => 'Références clients | Conseil AMOA SI - OLING',
                'metaDescription' => 'Découvrez nos références clients en AMOA SI, progiciels, conformité RGPD et gouvernance, en France et Outre-mer.',
                'eyebrow' => 'Cas clients OLING',
                'title' => 'Des cas clients pour comprendre les contextes, missions et résultats accompagnés par OLING',
                'intro' => 'OLING intervient auprès de PME, PMI, ETI et organisations multisites sur des projets ERP, SI, conformité et gouvernance qui demandent cadrage, coordination et suivi.',
                'highlights' => ['ERP, SI, conformité', 'Hexagone et DROM', 'Cadrage, pilotage, résultats'],
                'primaryCta' => ['route' => 'projets', 'label' => 'Voir les projets'],
                'secondaryCta' => ['route' => 'contact', 'label' => 'Parler de votre contexte'],
                'methodSection' => [
                    'left' => [
                        'kicker' => 'Méthode client',
                        'title' => 'Ce que nous cherchons à produire dans la relation',
                        'html' => '',
                    ],
                    'right' => [
                        'kicker' => 'Position',
                        'title' => 'Un accompagnement qui reste utile à la décision',
                        'text' => 'Nous adaptons le dispositif au contexte du client et restons concentrés sur les décisions à prendre, la maîtrise des risques et l’appropriation par les équipes.',
                    ],
                ],
                'referencesSection' => [
                    'kicker' => 'Références',
                    'title' => 'Des contextes différents, une même exigence de clarté',
                    'text' => 'OLING intervient pour des organisations qui doivent articuler contraintes métier, exigences réglementaires, transformation des systèmes et mise en œuvre opérationnelle.',
                    'image' => 'img/spe/clients.png',
                    'imageAlt' => 'Références clients OLING',
                ],
            ],
            'rse' => [
                'seoTitle' => 'Démarche RSE | OLING',
                'metaDescription' => 'Nos engagements RSE : gouvernance, éthique, impact environnemental et responsabilité sociétale chez OLING.',
                'eyebrow' => 'Responsabilité sociétale',
                'title' => 'Une démarche RSE intégrée à notre gouvernance, à nos pratiques et à nos choix d’exécution',
                'intro' => 'OLING inscrit ses choix de gouvernance, ses pratiques internes et ses relations de travail dans une logique de responsabilité durable, concrète et pilotable.',
                'highlights' => ['Éthique et intégrité', 'Gouvernance responsable', 'Impact environnemental'],
                'primaryCta' => ['route' => 'contact', 'label' => 'Nous contacter'],
                'secondaryCta' => ['route' => 'apropos', 'label' => 'À propos d’OLING'],
                'sections' => [
                    [
                        'items' => [
                            [
                                'kicker' => 'Cadre d’engagement',
                                'title' => 'Une charte appliquée à nos activités et à nos parties prenantes',
                                'text' => 'La responsabilité sociétale des entreprises est au cœur des préoccupations d’OLING. Cette charte formalise les engagements que nous prenons pour contribuer à un développement économique, social et environnemental durable, en cohérence avec nos valeurs, notre mission et nos pratiques de terrain.',
                            ],
                        ],
                    ],
                    [
                        'items' => [
                            [
                                'kicker' => '01',
                                'title' => 'Éthique et intégrité',
                                'list' => [
                                    'Respect des lois et des réglementations en vigueur dans les pays où nous opérons.',
                                    'Culture d’intégrité, de transparence et d’honnêteté dans l’ensemble du cabinet.',
                                    'Lutte active contre la corruption, les discriminations et le harcèlement.',
                                ],
                            ],
                            [
                                'kicker' => '02',
                                'title' => 'Gouvernance et management responsable',
                                'list' => [
                                    'Gouvernance transparente, efficace et respectueuse des droits des parties prenantes.',
                                    'Intégration des enjeux RSE dans la stratégie et la gestion des risques.',
                                    'Innovation et créativité mobilisées pour répondre aux enjeux sociétaux et environnementaux.',
                                ],
                            ],
                            [
                                'kicker' => '03',
                                'title' => 'Respect des droits humains',
                                'list' => [
                                    'Protection des droits humains et des droits des travailleurs selon les standards internationaux.',
                                    'Vigilance sur l’alignement de nos partenaires commerciaux avec ces principes.',
                                ],
                            ],
                            [
                                'kicker' => '04',
                                'title' => 'Équité et diversité',
                                'list' => [
                                    'Égalité des chances dans le recrutement, la formation et la promotion interne.',
                                    'Valorisation de la diversité, de l’inclusion et de la mixité au sein de l’entreprise.',
                                ],
                            ],
                            [
                                'kicker' => '05',
                                'title' => 'Dialogue avec les parties prenantes',
                                'list' => [
                                    'Dialogue constructif et transparent avec clients, fournisseurs, partenaires et collaborateurs.',
                                    'Contribution au développement des communautés locales et à des projets d’intérêt général.',
                                ],
                            ],
                            [
                                'kicker' => '06',
                                'title' => 'Protection de l’environnement',
                                'list' => [
                                    'Réduction de l’empreinte environnementale et promotion de pratiques durables.',
                                    'Sensibilisation des collaborateurs et partenaires aux comportements écoresponsables.',
                                    'Recherche de solutions pour économiser les ressources, réduire les déchets et limiter les émissions.',
                                ],
                            ],
                        ],
                    ],
                    [
                        'items' => [
                            [
                                'kicker' => 'Pilotage',
                                'title' => 'Suivi et évaluation',
                                'list' => [
                                    'Mise en place d’indicateurs et de processus de reporting pour suivre les progrès.',
                                    'Évaluation régulière de la conformité à la charte et ajustement des pratiques si nécessaire.',
                                ],
                            ],
                            [
                                'kicker' => 'Positionnement',
                                'title' => 'Un engagement assumé dans la durée',
                                'text' => 'En portant cette charte RSE, OLING confirme sa volonté de contribuer à un développement durable et responsable, au service de ses clients, de ses collaborateurs, de ses partenaires et de son écosystème.',
                            ],
                        ],
                    ],
                ],
                'linksSection' => [
                    'kicker' => 'Pages liées',
                    'title' => 'Autres engagements et politiques',
                    'links' => [
                        [
                            'kicker' => 'Sécurité',
                            'title' => 'Politique de sécurité de l’information',
                            'text' => 'Mesures, principes et responsabilités relatifs à la protection de l’information chez OLING.',
                            'route' => 'polsecurite',
                            'moreLabel' => 'Lire la page',
                        ],
                        [
                            'kicker' => 'Données personnelles',
                            'title' => 'Politique RGPD',
                            'text' => 'Cadre de traitement, de protection et de conformité appliqué aux données personnelles.',
                            'route' => 'polrgpd',
                            'moreLabel' => 'Lire la page',
                        ],
                        [
                            'kicker' => 'Échange',
                            'title' => 'Nous contacter',
                            'text' => 'Pour échanger sur vos enjeux de gouvernance, de conformité, de transformation et d’impact.',
                            'route' => 'contact',
                            'moreLabel' => 'Prendre contact',
                        ],
                    ],
                ],
                'cta' => [
                    'eyebrow' => 'Parlons de vos priorités',
                    'title' => 'Besoin d’un accompagnement structuré sur vos enjeux RSE, gouvernance ou conformité ?',
                    'text' => 'Nous intervenons pour cadrer, structurer et prioriser des démarches concrètes, adaptées à votre contexte et à vos contraintes d’exécution.',
                    'primaryCta' => ['route' => 'contact', 'label' => 'Parler à OLING'],
                ],
            ],
            'services' => [
                'seoTitle' => 'Services | OLING',
                'metaDescription' => 'Découvrez les offres OLING : AMOA ERP, transformation SI, organisation, conformité, cybersécurité, risques, RGPD et IA.',
                'eyebrow' => 'Services et offres',
                'title' => 'Des offres structurées pour cadrer, sécuriser et faire avancer les transformations',
                'intro' => 'Les services OLING traduisent les expertises en interventions concrètes. Les expertises aident à qualifier le sujet, les services précisent le niveau d’intervention, et les secteurs permettent de relire le tout à partir du terrain métier.',
                'highlights' => ['AMOA ERP et applications', 'Conformité, risques et résilience', 'Data, automatisation et IA'],
                'primaryCta' => ['route' => 'contact', 'label' => 'Parler de votre projet'],
                'secondaryCta' => ['route' => 'expertises_index', 'label' => 'Voir les expertises'],
                'bodySection' => [
                    'enabled' => true,
                ],
                'listing' => [
                    'defaultPracticeLabel' => 'Practice',
                    'defaultPracticeIntro' => 'Offres reliées à cette practice.',
                    'defaultServiceIntro' => 'Voir le détail de cette offre.',
                    'moreLabel' => 'Voir l\'offre',
                ],
                'cta' => [
                    'eyebrow' => 'Qualification',
                    'title' => 'Choisir le bon niveau d’intervention plutôt que multiplier les chantiers',
                    'text' => 'OLING peut vous aider à qualifier rapidement si votre sujet relève d’une expertise, d’une practice complète ou d’une intervention ciblée.',
                    'primaryCta' => ['route' => 'contact', 'label' => 'Demander un échange'],
                ],
            ],
            'metiers' => [
                'seoTitle' => 'Secteurs d’activité | Conseil AMOA SI - OLING',
                'metaDescription' => 'Expertise multisectorielle en AMOA SI, progiciels et conformité pour acteurs publics et privés.',
                'eyebrow' => 'Secteurs servis',
                'title' => 'Les secteurs métiers accompagnés par OLING',
                'intro' => 'OLING intervient dans des environnements variés avec une même exigence : comprendre les contraintes métier, clarifier les priorités et rendre les transformations pilotables.',
                'highlights' => ['Secteurs réellement couverts', 'Références publiées', 'Contraintes métier, SI et conformité'],
                'primaryCta' => ['route' => 'contact', 'label' => 'Parler de votre contexte'],
                'secondaryCta' => ['route' => 'sectors_index', 'label' => 'Voir les secteurs'],
                'sections' => [
                    [
                        'kicker' => 'Approche',
                        'title' => 'Des interventions adaptées au terrain',
                        'text' => 'OLING ne présente ici que les secteurs effectivement servis. Pour chacun, la page met en avant les contraintes récurrentes déjà rencontrées dans les missions publiées et les contextes de transformation les plus fréquents.',
                    ],
                    [
                        'kicker' => 'Secteur public, grands ports et aéroports',
                        'text' => 'Dans le secteur des transports et collectivités, nous avons contribué à la création d\'un catalogue de services pour une collectivité de 2000 agents, optimisé leur organisation, mis en œuvre des ERP pour améliorer la performance opérationnelle et financière dans des grands ports et au sein d\'aéroports. Nos actions ont aussi porté sur l\'élaboration de schémas d\'orientations SI et digitalisation, la mise en conformité aux normes ISO et RGPD, ainsi que l\'évaluation des systèmes d\'information en termes de continuité.',
                    ],
                    [
                        'kicker' => 'Négoce et distribution',
                        'text' => 'Pour les acteurs du négoce et de la distribution, nous mettons en avant notre expertise en matière d\'ERP, CRM, BI, comptabilité analytique, processus d\'achats et stock, gestion commerciale et finance. Nous accompagnons nos clients tout au long du processus d\'intégration des solutions applicatives et les aidons à tirer le meilleur parti des outils de collaboration Office 365.',
                    ],
                    [
                        'kicker' => 'Sociétés des eaux',
                        'text' => 'Pour les sociétés des eaux, nous avons travaillé sur la modernisation des infrastructures de traitement et de distribution, l\'optimisation des processus de gestion de l\'eau et la mise en œuvre de solutions numériques pour une meilleure surveillance et un meilleur contrôle des réseaux.',
                    ],
                    [
                        'kicker' => 'Bailleurs sociaux et aménageurs',
                        'text' => 'Dans le domaine des bailleurs sociaux et des aménageurs, nous avons accompagné nos clients dans la transformation de leurs processus métier et la mise en place de systèmes d\'information adaptés aux besoins spécifiques du secteur, avec des solutions numériques utiles à la gestion et à l\'exploitation.',
                    ],
                    [
                        'kicker' => 'Formation professionnelle',
                        'text' => 'Pour les organisations d\'apprentissage et de formation, nous avons assuré la conformité QSE, Qualiopi et RGPD, déployé des solutions de formation en ligne, des outils de gestion des compétences et des plateformes de collaboration, tout en accompagnant la mise en conformité avec les normes de qualité applicables.',
                    ],
                ],
            ],
            'team' => [
                'seoTitle' => 'L\'équipe | Experts AMOA SI - OLING',
                'metaDescription' => 'Une équipe d\'experts AMOA SI, progiciels, conformité et cybersécurité pour accompagner vos projets stratégiques.',
                'eyebrow' => 'Équipe OLING',
                'title' => 'Une équipe d\'experts pluridisciplinaires',
                'intro' => 'Conseil, AMO ERP, risques, conformité, cybersécurité et transformation : les profils visibles ici restent reliés à l\'admin et conservent leur contenu existant.',
                'highlights' => ['Profils seniors', 'Exécution terrain', 'Vision métier et SI'],
                'primaryCta' => ['route' => 'contact', 'label' => 'Parler à un consultant'],
                'secondaryCta' => ['route' => 'apropos', 'label' => 'Voir le cabinet'],
                'bodySection' => [
                    'enabled' => true,
                ],
            ],
            'projets' => [
                'seoTitle' => 'Nos projets | OLING',
                'metaDescription' => 'Nos projets de transformation SI, AMOA et conformité réalisés par OLING.',
                'eyebrow' => 'Cas clients OLING',
                'title' => 'Réalisations et missions OLING',
                'intro' => 'Une sélection de missions menées par OLING en AMOA SI, progiciels métiers, transformation des organisations, conformité et gouvernance.',
                'highlights' => ['AMOA SI', 'Progiciels métiers', 'Conformité et gouvernance'],
                'primaryCta' => ['route' => 'contact', 'label' => 'Parler de votre contexte'],
                'secondaryCta' => ['route' => 'expertises_index', 'label' => 'Voir les expertises'],
                'cards' => [
                    'emptyText' => 'Aucun projet n’est publié pour le moment.',
                    'fallbackDescription' => 'Pilotage, transformation et conformité sur des projets SI structurants.',
                ],
                'bodySection' => [
                    'enabled' => true,
                ],
                'mapSection' => [
                    'kicker' => 'Présence terrain',
                    'title' => 'Des projets conduits sur l’ensemble de nos territoires.',
                    'text' => 'France & Outre‑mer : nous déployons, sécurisons et accompagnons vos programmes là où vous opérez.',
                    'label' => 'France métropolitaine & DROM',
                ],
            ],
        ];
    }

    public function getExpertisePages(): array
    {
        return [
            'transformation-si-pme-eti' => [
                'nav' => 'Transformation SI',
                'title' => 'Conseil en transformation des operations et des systemes d\'information',
                'seoTitle' => 'Conseil en transformation SI pour PME, PMI et ETI | OLING',
                'metaDescription' => 'OLING cadre, securise et pilote les transformations SI des PME, PMI et ETI : gouvernance, trajectoire, projets structurants et execution.',
                'eyebrow' => 'Transformation des operations et des SI',
                'intro' => 'OLING accompagne les directions qui doivent faire evoluer leurs systemes, leur organisation et leurs modes de pilotage sans perdre la maitrise des risques, des delais et des arbitrages.',
                'situations' => [
                    'Projet de transformation disperse entre plusieurs chantiers et plusieurs fournisseurs.',
                    'Direction generale qui a besoin d\'une vue independante avant de relancer un projet sensible.',
                    'DSI ou direction de programme qui doit remettre de la lisibilite, de la gouvernance et des decisions.',
                ],
                'interventions' => [
                    'Diagnostic de transformation et schema directeur pragmatique.',
                    'Cadrage de programme, gouvernance et trajectoire cible.',
                    'Direction de projet, PMO, AMOA et redressement de projets en difficulte.',
                    'Arbitrage entre outillage, organisation, data, cybersecurite et conformite.',
                ],
                'deliverables' => [
                    'Note de cadrage',
                    'Feuille de route priorisee',
                    'Gouvernance et RACI',
                    'Plan de risques et indicateurs',
                ],
                'linkedServices' => [
                    ['practice' => 'consulting', 'service' => 'tech-refresh'],
                    ['practice' => 'business-apps', 'service' => 'erp'],
                ],
            ],
            'amoa-erp-applications-metiers' => [
                'nav' => 'AMOA ERP',
                'title' => 'AMOA ERP et applications metiers',
                'seoTitle' => 'AMOA ERP et applications metiers pour PME et ETI | OLING',
                'metaDescription' => 'OLING accompagne le choix, le cadrage, la mise en oeuvre et la securisation des projets ERP, CRM, GMAO et applications metiers.',
                'eyebrow' => 'ERP, CRM, GMAO et applications metiers',
                'intro' => 'OLING intervient en amont des projets ERP et applicatifs pour clarifier les besoins, arbitrer les scenarios et piloter des trajectoires credibles jusqu\'a la recette et l\'adoption.',
                'situations' => [
                    'Choix ou remplacement d\'un ERP devenu limitant.',
                    'Projet CRM ou GMAO mal cadre, mal specifie ou mal approprie.',
                    'Besoin de neutralite entre les metiers, les editeurs, les integrateurs et la DSI.',
                ],
                'interventions' => [
                    'Cadrage AMOA, expression de besoin et strategie de consultation.',
                    'Analyse des processus et scenarios de cible.',
                    'Selection de solutions et pilotage des integrateurs.',
                    'Recette, conduite du changement et suivi de mise en production.',
                ],
                'deliverables' => [
                    'Cartographie des processus cibles',
                    'Dossier de consultation',
                    'Grille de choix solution',
                    'Plan de recette et d\'adoption',
                ],
                'linkedServices' => [
                    ['practice' => 'business-apps', 'service' => 'erp'],
                ],
            ],
            'organisation-processus-conduite-du-changement' => [
                'nav' => 'Organisation',
                'title' => 'Organisation, processus et conduite du changement',
                'seoTitle' => 'Conseil en organisation, processus et conduite du changement | OLING',
                'metaDescription' => 'OLING aide les PME, PMI et ETI a repenser leurs processus, structurer leurs responsabilites et conduire les changements lies aux transformations SI.',
                'eyebrow' => 'Organisation et changement',
                'intro' => 'La transformation reussit quand les decisions, les roles, les processus et les usages sont clarifies. OLING traite les outils comme une consequence d\'une organisation mieux tenue.',
                'situations' => [
                    'Processus heterogenes entre sites, filiales ou equipes.',
                    'Projet SI bloque par des responsabilites floues ou des arbitrages non tenus.',
                    'Besoin de faire adopter une nouvelle facon de travailler sans rupture brutale.',
                ],
                'interventions' => [
                    'Diagnostic des processus et des interfaces entre fonctions.',
                    'Redefinition des responsabilites, flux et points de controle.',
                    'Plan de conduite du changement, support managers et adoption.',
                    'Synchronisation entre projet, metiers et direction.',
                ],
                'deliverables' => [
                    'Cartographie de processus',
                    'RACI cible',
                    'Plan d\'adoption',
                    'Supports d\'animation et de decision',
                ],
                'linkedServices' => [
                    ['practice' => 'consulting', 'service' => 'tech-refresh'],
                ],
            ],
            'data-automatisation-intelligence-artificielle' => [
                'nav' => 'Data et IA',
                'title' => 'Donnees, automatisation et intelligence artificielle',
                'seoTitle' => 'Conseil en data, automatisation et intelligence artificielle | OLING',
                'metaDescription' => 'OLING aide les entreprises a identifier des cas d’usage IA pertinents, structurer leur gouvernance, integrer des agents et piloter des projets data et automatisation.',
                'eyebrow' => 'Transformation des usages et de la gouvernance',
                'intro' => 'L\'IA doit etre reliee aux processus, aux donnees, aux responsabilites et aux risques. OLING structure des usages utiles, controles et integrables dans l\'organisation.',
                'situations' => [
                    'Direction qui veut identifier les cas d\'usage IA a forte valeur sans effet de mode.',
                    'Besoin de cadrer des agents IA relies aux donnees et outils autorises.',
                    'Questionnement sur la gouvernance, la confidentialite, l\'AI Act et la validation humaine.',
                ],
                'interventions' => [
                    'Diagnostic des opportunites IA, automatisation et donnees.',
                    'Politique d\'usage IA, gouvernance et cadre de decision.',
                    'Construction et integration d\'agents IA dans les environnements existants.',
                    'Pilotage de projet IA du cadrage au passage en production.',
                ],
                'deliverables' => [
                    'Cartographie des cas d\'usage',
                    'Matrice valeur / faisabilite / risques',
                    'Politique IA et registre d\'usage',
                    'Architecture fonctionnelle et plan de deploiement',
                ],
                'linkedServices' => [
                    ['practice' => 'consulting', 'service' => 'reforme-facturation-electronique-amoa'],
                    ['practice' => 'expertises-audit', 'service' => 'si'],
                ],
            ],
            'cybersecurite-conformite-resilience' => [
                'nav' => 'Cyber et resilience',
                'title' => 'Cybers securite, conformite et resilience',
                'seoTitle' => 'Cybers securite, conformite et resilience pour PME et ETI | OLING',
                'metaDescription' => 'OLING accompagne la securisation des organisations : cybersecurite, ISO 27001, NIS2, DORA, continuite et resilience.',
                'eyebrow' => 'Risques, conformite et resilience',
                'intro' => 'OLING aide les organisations a mettre en coherence securite, continuite, gouvernance et exigences reglementaires pour proteger leur activite et leurs donnees.',
                'situations' => [
                    'Besoin de structurer la gouvernance de securite avant une croissance ou une transformation.',
                    'Exposition croissante aux exigences NIS2, DORA, ISO 27001 ou aux demandes clients.',
                    'PCA/PRA ou trajectoire de resilience a formaliser et tester.',
                ],
                'interventions' => [
                    'Evaluation de posture, feuille de route et gouvernance securite.',
                    'Preparation et mise en coherence de dispositifs ISO, NIS2, DORA.',
                    'PCA, PRA, organisation de crise et continuite d\'activite.',
                    'Pilotage de la convergence entre SI, operationnel, conformite et fournisseurs.',
                ],
                'deliverables' => [
                    'Feuille de route cyber',
                    'Plan de traitement des risques',
                    'Politique et dispositifs de gouvernance',
                    'Dispositif PCA / PRA',
                ],
                'linkedServices' => [
                    ['practice' => 'expertises-audit', 'service' => 'si'],
                    ['practice' => 'expertises-audit', 'service' => 'qse'],
                ],
            ],
            'rgpd-dpo-gouvernance' => [
                'nav' => 'RGPD et DPO',
                'title' => 'RGPD, gouvernance et DPO externalise',
                'seoTitle' => 'RGPD, gouvernance et DPO externalise | OLING',
                'metaDescription' => 'OLING accompagne la conformite RGPD, la gouvernance des donnees et les besoins de DPO externalise pour PME, ETI et organisations regulees.',
                'eyebrow' => 'Protection des donnees et responsabilites',
                'intro' => 'OLING structure la conformite RGPD comme un dispositif de gouvernance, de responsabilisation et de securisation des traitements, au service des directions et des equipes.',
                'situations' => [
                    'Entreprise qui doit remettre sa conformite RGPD en ordre ou la rendre operationnelle.',
                    'Besoin d\'un DPO externalise capable de parler aussi bien aux directions, aux metiers et a la DSI.',
                    'Questionnement sur l\'articulation entre RGPD, IA, fournisseurs, securite et preuves de conformite.',
                ],
                'interventions' => [
                    'Diagnostic RGPD, cartographie des traitements et feuille de route.',
                    'DPO externalise, gouvernance et accompagnement des directions.',
                    'AIPD, clauses, sensibilisation, gestion des demandes et relation sous-traitants.',
                    'Traitement des enjeux croises entre donnees personnelles, cybersecurite et usages IA.',
                ],
                'deliverables' => [
                    'Registre et feuille de route RGPD',
                    'Dispositif DPO externalise',
                    'Politiques et procedures',
                    'Supports de preuve et de sensibilisation',
                ],
                'linkedServices' => [
                    ['practice' => 'expertises-audit', 'service' => 'rgpd'],
                ],
            ],
            'amoa-ia-pilotage-projets-agents' => [
                'nav' => 'AMOA IA',
                'title' => 'AMOA IA, pilotage de programmes et agents metier',
                'seoTitle' => 'AMOA IA, pilotage de projets IA et agents metier | OLING',
                'metaDescription' => 'OLING cadre, co-construit et pilote les projets IA: AMOA IA, grands chantiers de transformation, agents adaptes aux TPE, PME et ETI.',
                'eyebrow' => 'AMOA IA et execution terrain',
                'intro' => 'OLING accompagne les directions et equipes projet dans le cadrage, le pilotage et le deploiement de programmes IA, du chantier structurant aux agents metier integres aux processus existants.',
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
            ],
            'conformite-ia-gouvernance-ai-act' => [
                'nav' => 'Conformite IA',
                'title' => 'Conformite IA, gouvernance et AI Act',
                'seoTitle' => 'Conformite IA, AI Act et gouvernance des usages | OLING',
                'metaDescription' => 'OLING structure la conformite IA, la gouvernance des usages, les controles et les preuves pour des projets IA robustes et auditable.',
                'eyebrow' => 'Conformite, preuves et responsabilites',
                'intro' => 'Nous aidons les organisations a deployer l\'IA sans angle mort de responsabilite: AI Act, protection des donnees, supervision humaine, gestion des fournisseurs et preuves de conformite.',
                'heroImage' => '/img/spe/rgpd.png',
                'situations' => [
                    'Direction qui veut deployer des usages IA sans exposer l\'entreprise a des risques juridiques, contractuels ou reputionnels.',
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
            ],
            'transformation-digitale-ia-pme-pmi' => [
                'nav' => 'Transformation digitale',
                'title' => 'Transformation digitale, automatisation et IA',
                'seoTitle' => 'Transformation digitale, automatisation et IA pour TPE, PME et PMI | OLING',
                'metaDescription' => 'OLING cadre les projets d’automatisation et d’IA des TPE, PME et PMI avec des usages cibles et des agents integres aux operations.',
                'eyebrow' => 'Transformation digitale et performance',
                'intro' => 'OLING relie automatisation et intelligence artificielle a la realite des operations: gains concrets, adoption, outillage tenable et agents integres aux usages quotidiens.',
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
                    'Feuille de route automatisation et IA',
                    'Cartographie des flux et irritants',
                    'Plan d\'automatisation et d\'adoption',
                    'Tableau de bord de gains et de priorites',
                ],
                'linkedServices' => [
                    ['practice' => 'business-apps', 'service' => 'ms365'],
                    ['practice' => 'business-apps', 'service' => 'msbi'],
                ],
            ],
        ];
    }

    public function getSectorPages(): array
    {
        return [
            'industrie' => [
                'title' => 'Conseil en transformation pour l\'industrie et les PMI',
                'seoTitle' => 'Conseil industrie, PMI, ERP, GMAO et resilience | OLING',
                'metaDescription' => 'OLING accompagne les industriels et PMI sur les enjeux ERP, GMAO, processus, donnees, continuite et conformite.',
                'eyebrow' => 'Secteurs',
                'intro' => 'OLING accompagne les industriels qui doivent faire evoluer leurs processus, leurs outils et leur gouvernance sans fragiliser leur production ni leurs obligations.',
                'issues' => [
                    'ERP, GMAO, flux atelier et supply chain',
                    'Continuité d\'activite, cyber et dependances fournisseurs',
                    'Structuration des responsabilites entre production, qualite, DSI et direction',
                ],
                'linkedExpertises' => [
                    'amoa-erp-applications-metiers',
                    'organisation-processus-conduite-du-changement',
                    'cybersecurite-conformite-resilience',
                ],
            ],
            'services' => [
                'title' => 'Conseil en transformation pour les entreprises de services',
                'seoTitle' => 'Conseil organisation, CRM, data et gouvernance pour services B2B | OLING',
                'metaDescription' => 'OLING aide les entreprises de services a structurer leurs processus, leur CRM, leur gouvernance data, leur conformite et leurs usages IA.',
                'eyebrow' => 'Secteurs',
                'intro' => 'Pour les entreprises de services, la performance depend de la qualite des processus, de la circulation de l\'information, de la relation client et de l\'adoption des outils.',
                'issues' => [
                    'Structuration du CRM et du pilotage commercial',
                    'Gouvernance des donnees, automatisation et IA',
                    'Harmonisation des processus entre fonctions support et operationnelles',
                ],
                'linkedExpertises' => [
                    'transformation-si-pme-eti',
                    'organisation-processus-conduite-du-changement',
                    'data-automatisation-intelligence-artificielle',
                ],
            ],
            'secteur-public' => [
                'title' => 'Conseil en conformite, gouvernance et resilience pour organisations regulees',
                'seoTitle' => 'Conseil conformite, cybersecurite et gouvernance pour organisations regulees | OLING',
                'metaDescription' => 'OLING accompagne les organisations regulees et les environnements publics sur les enjeux de conformite, cybersecurite, gouvernance et resilience.',
                'eyebrow' => 'Secteurs',
                'intro' => 'OLING intervient aupres des structures exposees a des exigences fortes de responsabilite, de tracabilite, de conformite et de continuite.',
                'issues' => [
                    'Cadres reglementaires et attentes de preuve',
                    'Gouvernance de securite, protection des donnees et resilience',
                    'Coordination entre metiers, directions, DSI et prestataires',
                ],
                'linkedExpertises' => [
                    'rgpd-dpo-gouvernance',
                    'cybersecurite-conformite-resilience',
                    'data-automatisation-intelligence-artificielle',
                ],
            ],
        ];
    }

    public function getPracticeNarrative(Practice $practice): array
    {
        $defaults = [
            'eyebrow' => $practice->getDesignationShort() ?: 'Practice OLING',
            'headline' => $practice->getH1Title() ?: $practice->getDesignation(),
            'intro' => $practice->getIntroductionShort() ?: $practice->getIntroduction() ?: '',
            'promise' => $practice->getDescription() ?: 'Une pratique structuree pour relier enjeux operationnels, outils et execution.',
            'focus' => [],
        ];

        return match ($practice->getSlug()) {
            'consulting' => [
                'eyebrow' => 'AMOA SI et pilotage de transformation',
                'headline' => 'AMOA SI, cadrage et pilotage de projets de transformation',
                'metaTitle' => 'AMOA SI et pilotage de projets de transformation | OLING',
                'metaDescription' => 'Cabinet AMOA SI indépendant : cadrage, expression des besoins, choix de solution, pilotage projet, recette, migration et conduite du changement.',
                'intro' => 'OLING accompagne les directions métier, DSI et sponsors de projet pour cadrer, arbitrer et piloter les transformations SI, depuis l’expression des besoins jusqu’au déploiement.',
                'promise' => 'Cabinet indépendant, OLING intervient en AMOA SI pour structurer les décisions, sécuriser les choix de solution, piloter les parties prenantes et organiser l’exécution du projet.',
                'focus' => [
                    'Cadrage, expression des besoins et cahier des charges',
                    'Choix de solution, consultation et pilotage fournisseurs',
                    'Recette, migration, déploiement et conduite du changement',
                    'Schéma directeur, gouvernance SI et PMO de transformation',
                ],
                'missionPhases' => [
                    'Cadrage stratégique, schéma directeur et priorisation des chantiers',
                    'Expression des besoins, ateliers métier et formalisation du cahier des charges',
                    'Consultation, aide au choix de solution et pilotage des intégrateurs',
                    'Recette, migration, conduite du changement et déploiement',
                ],
                'projectContexts' => [
                    'Refonte d’un SI métier, d’un ERP, d’un CRM ou d’une GMAO',
                    'Programme de transformation impliquant plusieurs directions et prestataires',
                    'Projet de secteur public, PME, ETI ou groupe avec enjeux de gouvernance',
                    'Mise sous contrôle d’un projet en dérive, d’une migration ou d’un lot sensible',
                ],
                'deliverables' => [
                    'Note de cadrage, macro-planning et gouvernance projet',
                    'Expression des besoins, cahier des charges et critères de choix',
                    'RACI, dispositifs de pilotage, supports de comité et plan d’actions',
                    'Stratégie de recette, plan de migration et plan de conduite du changement',
                ],
                'clientTypes' => [
                    'Directions générales et directions métier',
                    'DSI, responsables applicatifs et PMO',
                    'PME, ETI, grands comptes et organisations du secteur public',
                    'Contextes multi-sites, multi-outils ou multi-prestataires',
                ],
                'supportLinks' => [
                    ['href' => '/business-apps/erp', 'label' => 'AMOA ERP et pilotage applicatif', 'description' => 'Pour les projets ERP, interfaces, reprise de données et pilotage intégrateur.'],
                    ['href' => '/crm', 'label' => 'AMOA CRM', 'description' => 'Pour les projets CRM, processus commerciaux, données et adoption.'],
                    ['href' => '/gmao', 'label' => 'Conseil et AMOA GMAO', 'description' => 'Pour les projets maintenance, actifs, mobilité et processus de terrain.'],
                    ['href' => '/si-finance', 'label' => 'AMOA SI Finance', 'description' => 'Pour le reporting, la clôture, le contrôle de gestion et les interfaces finance.'],
                    ['href' => '/expertises-audit/rgpd', 'label' => 'RGPD et gouvernance des données', 'description' => 'Pour articuler transformation SI, conformité et responsabilité.'],
                    ['href' => '/consulting/assistance-a-maitrise-douvrage', 'label' => 'Assistance à maîtrise d’ouvrage', 'description' => 'Page complémentaire pour détailler le cadrage AMOA SI et ses livrables.'],
                    ['href' => '/ressources/cadrage-projet-amoa-si', 'label' => 'Ressource cadrage projet AMOA SI', 'description' => 'Contenu support pour structurer un cadrage utile avant consultation.'],
                    ['href' => '/projets', 'label' => 'Cas clients et projets', 'description' => 'Preuves, contextes d’intervention et réalisations publiées.'],
                    ['href' => '/a-propos/team', 'label' => 'Experts OLING', 'description' => 'Profils mobilisables sur les projets de transformation et de gouvernance SI.'],
                ],
            ],
            'expertises-audit' => [
                'eyebrow' => $practice->getDesignationShort() ?: 'Conformité & Risques',
                'headline' => $practice->getH1Title() ?: $practice->getDesignation(),
                'intro' => $practice->getIntroductionShort() ?: $practice->getIntroduction() ?: '',
                'promise' => $practice->getDescription() ?: 'Faire converger exigences reglementaires, securite, donnees et pilotage.',
                'focus' => [
                    'Audit de conformité réglementaire',
                    'Contrôle de gestion et évaluation des risques',
                    'Conformité RGPD DPO, registre, DPIA',
                    'Sécurité des SI ISO 27001, DORA, NIS2',
                ],
            ],
            'business-apps' => [
                'eyebrow' => $practice->getDesignationShort() ?: 'Opérations et technologie',
                'headline' => $practice->getH1Title() ?: $practice->getDesignation(),
                'intro' => $practice->getIntroductionShort() ?: $practice->getIntroduction() ?: '',
                'promise' => $practice->getDescription() ?: 'Faire des applications metiers un levier de performance plutot qu\'une source de friction.',
                'focus' => [
                    'Assistance au déploiement Progiciel (ERP, CRM, GMAO, SI Finance, SIRH)',
                    'Business Intelligence et analytique décisionnelle',
                    'Systèmes d’information géographique (SIG)',
                    'Microsoft 365 et productivité collaborative',
                ],
            ],
            default => $defaults,
        };
    }

    public function getServiceNarrative(Services $service): array
    {
        $practiceSlug = $service->getPractice()?->getSlug();
        $serviceSlug = $service->getSlug();

        $defaults = [
            'eyebrow' => 'Offre OLING',
            'headline' => $service->getDesignation(),
            'intro' => $service->getIntroductionShort() ?: '',
            'outcomes' => [
                'Des decisions plus claires',
                'Un dispositif plus robuste',
                'Une mise en oeuvre mieux pilotee',
            ],
        ];

        $key = sprintf('%s/%s', $practiceSlug, $serviceSlug);

        return match ($key) {
            'business-apps/erp' => [
                'metaTitle' => 'AMOA ERP : cadrage, choix et pilotage de projet ERP | OLING',
                'metaDescription' => 'Cabinet AMOA ERP indépendant : cadrage, choix de solution, pilotage intégrateur, reprise de données, recette, migration et conduite du changement.',
                'eyebrow' => 'Cabinet AMOA ERP independant',
                'headline' => 'AMOA ERP : cadrage, choix et pilotage de votre projet ERP',
                'intro' => 'OLING accompagne les directions metier, DSI et sponsors de projet pour cadrer, choisir et piloter un projet ERP, depuis l\'expression des besoins jusqu\'a la recette, la migration et le deploiement.',
                'promise' => 'OLING n\'est ni editeur, ni revendeur, ni integrateur ERP. Le cabinet intervient en AMOA pour structurer les decisions, tenir la gouvernance projet, objectiver les choix et securiser l\'execution.',
                'outcomes' => [
                    'Cadrage ERP plus clair et decisions mieux arbitrees',
                    'Choix de solution et pilotage integrateur objectivés',
                    'Recette, migration et adoption mieux maitrisées',
                ],
                'focus' => [
                    'Cadrage strategique et diagnostic de l\'existant',
                    'Expression des besoins, processus et cahier des charges',
                    'Aide au choix ERP, consultation et comparaison des solutions',
                    'Pilotage integrateur, recette, reprise de donnees et conduite du changement',
                ],
                'missionPhases' => [
                    'Cadrage du projet ERP, objectifs, perimetre, priorites et gouvernance',
                    'Cartographie des processus, ateliers metier et expression des besoins',
                    'Consultation, analyse des offres, evaluation des solutions et alignement des parties prenantes',
                    'Pilotage de l\'integrateur, interfaces, reprise de donnees, recette, formation et deploiement',
                ],
                'deliverables' => [
                    'Note de cadrage, macro-planning et gouvernance projet',
                    'Cartographie des processus, expression des besoins et cahier des charges',
                    'Grille d\'evaluation, matrice de choix, dossier de consultation et synthese d\'analyse des offres',
                    'Strategie de recette, interfaces, strategie de reprise, plan de formation, conduite du changement et suivi des risques',
                ],
                'projectContexts' => [
                    'Refonte ou remplacement d\'un ERP devenu limitant pour les operations',
                    'Projet ERP multi-metiers avec enjeux finance, achats, approvisionnement, stocks, production, maintenance ou RH',
                    'Pilotage d\'un integrateur dans un contexte PME, ETI, industrie, services ou secteur public',
                    'Projet en derive, migration sensible ou besoin de remettre la trajectoire sous controle',
                ],
                'clientTypes' => [
                    'Directions generales, directions metier et DSI',
                    'PME, ETI et organisations multi-sites',
                    'Equipes finance, achats, supply chain, production et maintenance',
                    'Sponsors de projet ayant besoin d\'un tiers independant pour arbitrer et piloter l\'execution',
                ],
                'supportLinks' => [
                    ['href' => '/amoa-si', 'label' => 'AMOA des systemes d\'information', 'description' => 'Pour le cadrage transverse, la gouvernance SI et le pilotage des transformations.'],
                    ['href' => '/crm', 'label' => 'Projet CRM', 'description' => 'Pour les enjeux relation client, processus commerciaux, donnees et adoption.'],
                    ['href' => '/gmao', 'label' => 'Projet GMAO', 'description' => 'Pour la maintenance, les actifs, les interventions terrain et la performance operationnelle.'],
                    ['href' => '/si-finance', 'label' => 'SI Finance', 'description' => 'Pour les interfaces finance, le reporting, le controle de gestion et la cloture.'],
                    ['href' => '/projets', 'label' => 'Nos projets', 'description' => 'Pour voir des contextes publies de transformation SI, ERP et AMOA.'],
                    ['href' => '/a-propos/team', 'label' => 'Nos consultants', 'description' => 'Pour identifier les profils OLING mobilisables sur un projet ERP.'],
                    ['href' => '/ressources', 'label' => 'Ressources AMOA et ERP', 'description' => 'Pour approfondir le cadrage, les risques et les points de vigilance de transformation.'],
                ],
                'schemaServiceType' => 'AMOA ERP',
            ],
            'expertises-audit/rgpd' => [
                'eyebrow' => 'Protection des donnees et gouvernance',
                'headline' => 'Rendre la conformite RGPD operationnelle et pilotable',
                'intro' => 'OLING structure la conformite RGPD comme un dispositif de responsabilite et de preuve, au service de l\'organisation.',
                'outcomes' => [
                    'Vision claire des traitements et des risques',
                    'Dispositif DPO et gouvernance activables',
                    'Meilleure coordination metiers, SI et conformite',
                ],
            ],
            'expertises-audit/si' => [
                'eyebrow' => 'Cybers securite et resilience',
                'headline' => 'Mieux tenir les risques cyber, SI et de continuite',
                'intro' => 'OLING aide a prioriser les mesures utiles, structurer la gouvernance et relier la securite aux operations.',
                'outcomes' => [
                    'Posture de securite plus lisible',
                    'Risques priorises et traites',
                    'Dispositif de resilience activable',
                ],
            ],
            'consulting/amoa-ia-pilotage-de-projets-ia-et-agents-metier' => [
                'eyebrow' => 'AMOA IA et programmes structurants',
                'headline' => 'Cadrer, piloter et déployer un projet IA ou un dispositif d’agents',
                'intro' => 'OLING intervient en AMOA IA pour transformer une intention en programme pilotable, du chantier transverse jusqu’aux agents métier adaptés aux TPE et PME.',
                'outcomes' => [
                    'Programme IA cadré et loti',
                    'Décisions clarifiées entre métiers, DSI et partenaires',
                    'Agents et usages intégrés dans un cadre tenable',
                ],
            ],
            'expertises-audit/conformite-ia-gouvernance-et-ai-act' => [
                'eyebrow' => 'Conformite IA et gouvernance',
                'headline' => 'Mettre l’IA sous preuve, sous contrôle et sous responsabilité',
                'intro' => 'OLING aide à aligner AI Act, RGPD, sécurité et gouvernance interne pour déployer des usages IA sans angle mort de conformité.',
                'outcomes' => [
                    'Usages IA cartographiés et priorisés',
                    'Contrôles, rôles et preuves définis',
                    'Cadre de conformité exploitable par les équipes',
                ],
            ],
            'business-apps/transformation-digitale-automatisation-et-ia-utile' => [
                'eyebrow' => 'Transformation digitale et IA',
                'headline' => 'Moderniser les operations avec des automatisations et usages IA cibles',
                'intro' => 'OLING relie automatisation et IA à des gains concrets, avec des dispositifs adaptés aux moyens et contraintes des PME et PMI.',
                'outcomes' => [
                    'Feuille de route digitale pragmatique',
                    'Processus simplifiés et mieux outillés',
                    'Automatisations et agents utiles aux équipes',
                ],
            ],
            default => $defaults,
        };
    }
}
