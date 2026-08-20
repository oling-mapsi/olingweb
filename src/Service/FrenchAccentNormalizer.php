<?php

namespace App\Service;

class FrenchAccentNormalizer
{
    /**
     * @var array<string, string>
     */
    private const REPLACEMENTS = [
        'A propos' => 'À propos',
        'a propos' => 'à propos',
        'Acces' => 'Accès',
        'acceder' => 'accéder',
        'adaptee' => 'adaptée',
        'adaptees' => 'adaptées',
        'aeroports' => 'aéroports',
        'amenageurs' => 'aménageurs',
        'capacite' => 'capacité',
        'chaine' => 'chaîne',
        'clarte' => 'clarté',
        'coeur' => 'cœur',
        'collectivites' => 'collectivités',
        'collectivite' => 'collectivité',
        'comites' => 'comités',
        'comite' => 'comité',
        'completement' => 'complètement',
        'competences' => 'compétences',
        'concretes' => 'concrètes',
        'conformite' => 'conformité',
        'controle' => 'contrôle',
        'controles' => 'contrôles',
        'coordonnees' => 'coordonnées',
        'credibles' => 'crédibles',
        'cyber securite' => 'cybersécurité',
        'cybersecurite' => 'cybersécurité',
        'decision' => 'décision',
        'decisions' => 'décisions',
        'decor' => 'décor',
        'deja' => 'déjà',
        'deleguee' => 'déléguée',
        'delais' => 'délais',
        'demarche' => 'démarche',
        'dependances' => 'dépendances',
        'differentes' => 'différentes',
        'differents' => 'différents',
        'difference' => 'différence',
        'donnees' => 'données',
        'duree' => 'durée',
        'echange' => 'échange',
        'echanges' => 'échanges',
        'ecosysteme' => 'écosystème',
        'editeurs' => 'éditeurs',
        'elaboration' => 'élaboration',
        'engagee' => 'engagée',
        'engagees' => 'engagées',
        'engage' => 'engagé',
        'equipe' => 'équipe',
        'equipes' => 'équipes',
        'evaluation' => 'évaluation',
        'eviter' => 'éviter',
        'execution' => 'exécution',
        'exposees' => 'exposées',
        'facon' => 'façon',
        'frequentes' => 'fréquentes',
        'gerer' => 'gérer',
        'gouvernee' => 'gouvernée',
        'heterogenes' => 'hétérogènes',
        'independant' => 'indépendant',
        'independante' => 'indépendante',
        'independants' => 'indépendants',
        'integree' => 'intégrée',
        'integrees' => 'intégrées',
        'integrer' => 'intégrer',
        'integration' => 'intégration',
        'integrateurs' => 'intégrateurs',
        'interventions concretes' => 'interventions concrètes',
        'intervenants associes' => 'intervenants associés',
        'liees' => 'liées',
        'lies' => 'liés',
        'lisibilite' => 'lisibilité',
        'maitrise' => 'maîtrise',
        'maitriser' => 'maîtriser',
        'maturite' => 'maturité',
        'meme' => 'même',
        'memorise' => 'mémorise',
        'metier' => 'métier',
        'metiers' => 'métiers',
        'methode' => 'méthode',
        'negoces' => 'négoces',
        'negoce' => 'négoce',
        'neutralite' => 'neutralité',
        'numeriques' => 'numériques',
        'numerique' => 'numérique',
        'operationnelle' => 'opérationnelle',
        'operationnelles' => 'opérationnelles',
        'operationnel' => 'opérationnel',
        'operationnels' => 'opérationnels',
        'operations' => 'opérations',
        'opportunites' => 'opportunités',
        'par ou commencer' => 'par où commencer',
        'particulieres' => 'particulières',
        'plutot' => 'plutôt',
        'presentation' => 'présentation',
        'presence' => 'présence',
        'priorisee' => 'priorisée',
        'priorite' => 'priorité',
        'priorites' => 'priorités',
        'privees' => 'privées',
        'prives' => 'privés',
        'propriete' => 'propriété',
        'qualifie' => 'qualifié',
        'qualite' => 'qualité',
        'realiste' => 'réaliste',
        'realites' => 'réalités',
        'realite' => 'réalité',
        'reglementaire' => 'réglementaire',
        'reglementaires' => 'réglementaires',
        'regulation' => 'régulation',
        'regulees' => 'régulées',
        'reliees' => 'reliées',
        'relies' => 'reliés',
        'reponse' => 'réponse',
        'resilience' => 'résilience',
        'responsabilite' => 'responsabilité',
        'responsabilites' => 'responsabilités',
        'reussir' => 'réussir',
        'role' => 'rôle',
        'roles' => 'rôles',
        'scenario' => 'scénario',
        'scenarios' => 'scénarios',
        'schema' => 'schéma',
        'schemas' => 'schémas',
        'securisation' => 'sécurisation',
        'securiser' => 'sécuriser',
        'securisez' => 'sécurisez',
        'securite' => 'sécurité',
        'societe' => 'société',
        'societes' => 'sociétés',
        'specifiques' => 'spécifiques',
        'strategie' => 'stratégie',
        'strategique' => 'stratégique',
        'structuree' => 'structurée',
        'structurees' => 'structurées',
        'systeme' => 'système',
        'systemes' => 'systèmes',
        'tracabilite' => 'traçabilité',
        'utile a' => 'utile à',
        'variete' => 'variété',
        'verifier' => 'vérifier',
    ];

    public function normalize(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return strtr($value, self::REPLACEMENTS);
    }

    public function normalizeStructured(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return $value;
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->normalize($value);
        }

        if (!is_array($decoded)) {
            return $this->normalize($value);
        }

        $normalized = $this->normalizeArray($decoded);

        return json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /**
     * @param array<mixed> $data
     * @return array<mixed>
     */
    private function normalizeArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = $this->normalize($value);
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->normalizeArray($value);
            }
        }

        return $data;
    }
}
