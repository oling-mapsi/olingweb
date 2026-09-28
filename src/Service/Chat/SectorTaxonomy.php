<?php

namespace App\Service\Chat;

class SectorTaxonomy
{
    /** @var array<string, list<string>> */
    private const SECTORS = [
        'Aménagement du territoire et habitats' => ['amenagement du territoire', 'habitat', 'habitats', 'bailleur', 'bailleur social', 'logement social', 'sem amenagement', 'spl', 'foncier', 'patrimoine immobilier'],
        'Banque' => ['banque', 'bancaire', 'credit', 'etablissement financier', 'services bancaires', 'infrastructure bancaire'],
        'Chambre consulaire' => ['chambre consulaire', 'cci', 'chambre de commerce', 'cma', 'chambre des metiers', 'organisme consulaire'],
        'Collectivités territoriales' => ['collectivite', 'collectivites territoriales', 'mairie', 'commune', 'communaute d agglomeration', 'communaute de communes', 'departement', 'region', 'epci', 'etablissement public territorial'],
        'Eau et assainissement' => ['eau', 'eaux', 'assainissement', 'eau potable', 'societe des eaux', 'operateur de l eau', 'syndicat des eaux', 'regie de l eau', 'distribution d eau', 'traitement des eaux', 'reseau d eau', 'eaux usees', 'step'],
        'Formation professionnelle' => ['formation professionnelle', 'organisme de formation', 'cfa', 'centre de formation', 'ecole professionnelle', 'qualiopi'],
        'Industrie' => ['industrie', 'industriel', 'industriels', 'usine', 'production', 'manufacturing', 'pmi', 'maintenance industrielle', 'mes', 'atelier', 'supply chain'],
        'Mutuelle et assurance' => ['mutuelle', 'assurance', 'assureur', 'prevoyance', 'complementaire sante', 'courtier', 'organisme assureur'],
        'Négoce et distribution' => ['negoce', 'distribution', 'distributeur', 'grossiste', 'commerce', 'retail', 'concession', 'reseau de distribution', 'automobile', 'gestion commerciale'],
        'Santé' => ['sante', 'hopital', 'clinique', 'etablissement de sante', 'pharmacie', 'laboratoire', 'institut medical', 'medico social', 'soins'],
        'Transport' => ['transport', 'transports', 'port', 'grand port maritime', 'port autonome', 'aeroport', 'plateforme aeroportuaire', 'mobilite', 'transport public', 'logistique portuaire'],
    ];

    public function detect(string $text): ?string
    {
        $normalized = $this->normalize($text);
        foreach (self::SECTORS as $sector => $aliases) {
            foreach ($aliases as $alias) {
                if (preg_match('/\b'.preg_quote($this->normalize($alias), '/').'\b/', $normalized) === 1) {
                    return $sector;
                }
            }
        }

        return null;
    }

    /** @return list<string> */
    public function aliasesFor(string $sector): array
    {
        return self::SECTORS[$sector] ?? [];
    }

    /** @return array<string, list<string>> */
    public function sectors(): array
    {
        return self::SECTORS;
    }

    public function normalize(string $value): string
    {
        $value = strtr($value, [
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'ç' => 'c', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ö' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ÿ' => 'y',
            'À' => 'A', 'Â' => 'A', 'Ä' => 'A', 'Ç' => 'C', 'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Î' => 'I', 'Ï' => 'I', 'Ô' => 'O', 'Ö' => 'O', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ÿ' => 'Y',
        ]);
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($normalized === false) {
            $normalized = $value;
        }

        return trim(preg_replace('/[^a-z0-9]+/', ' ', strtolower($normalized)) ?? $normalized);
    }
}
