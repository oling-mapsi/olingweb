<?php

namespace App\Service\Growth;

use App\Entity\GrowthCampaign;
use App\Enum\GrowthDestination;

class GrowthEditorialContextProvider
{
    public function systemPrompt(GrowthDestination $destination): string
    {
        return match ($destination) {
            GrowthDestination::OLING_PUBLIC => implode("\n", [
                'Tu produis un brouillon editorial B2B pour Oling.',
                'Objectif: expliquer des sujets web, data, ERP, automatisation et transformation numerique avec un ton professionnel, concret et utile.',
                'Contraintes: JSON uniquement, contenu sourcable, pas de promesse excessive, pas de publication automatique.',
            ]),
            GrowthDestination::MAPSI_PUBLIC => implode("\n", [
                'Tu produis un brouillon editorial B2B pour MAPSI.',
                'Objectif: contenu commercial prudent pour mapsi.fr.',
                'Contraintes: JSON uniquement, aucune donnee client, aucune metrique usage brute, aucune publication automatique.',
            ]),
        };
    }

    public function userPrompt(GrowthCampaign $campaign, GrowthDestination $destination): string
    {
        return implode("\n", [
            'Sujet: '.$campaign->getTitle(),
            'Destination: '.$destination->value,
            'Format: titre, slug, extrait, HTML court, meta title, meta description, categories, tags, auteur.',
            'SEO: viser une intention claire, titre descriptif, meta description actionnable, contenu lisible.',
            'N invente pas de chiffre, client, integration ou fonctionnalite non fournie explicitement dans le sujet.',
        ]);
    }
}
