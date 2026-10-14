<?php

namespace App\Service\GrowthFactory;

final class GrowthFactoryView
{
    private const TYPE_CHANNELS = [
        'MAPSI_USERS' => ['mapsi_site', 'oling_site', 'linkedin_manual', 'email_manual'],
        'MAPSI_MARKETING' => ['mapsi_site', 'oling_site', 'linkedin_manual', 'email_manual'],
        'OLING' => ['mapsi_site', 'oling_site', 'linkedin_manual', 'email_manual'],
    ];

    public function typeOptions(): array
    {
        return [
            'MAPSI_USERS' => ['label' => 'Utilisateurs MAPSI', 'channels' => self::TYPE_CHANNELS['MAPSI_USERS']],
            'MAPSI_MARKETING' => ['label' => 'Marketing MAPSI', 'channels' => self::TYPE_CHANNELS['MAPSI_MARKETING']],
            'OLING' => ['label' => 'OLING', 'channels' => self::TYPE_CHANNELS['OLING']],
        ];
    }

    public function channelOptions(): array
    {
        return [
            'mapsi_site' => 'Site MAPSI',
            'oling_site' => 'Site OLING',
            'linkedin_manual' => 'LinkedIn manuel',
            'email_manual' => 'Email manuel',
        ];
    }

    public function destinationOptions(): array
    {
        return [
            'oling' => ['label' => 'OLING.fr', 'campaign_type' => 'OLING', 'channels' => ['oling_site']],
            'mapsi' => ['label' => 'MAPSI.fr', 'campaign_type' => 'MAPSI_MARKETING', 'channels' => ['mapsi_site']],
            'both' => ['label' => 'Les deux sites', 'campaign_type' => 'MAPSI_MARKETING', 'channels' => ['oling_site', 'mapsi_site']],
        ];
    }

    public function articleDestination(string $destination): array
    {
        return $this->destinationOptions()[$destination] ?? $this->destinationOptions()['oling'];
    }

    public function articleStatusLabel(string $status): string
    {
        return [
            'DRAFT' => 'Brouillon',
            'GENERATING' => 'Génération en cours',
            'READY_FOR_REVIEW' => 'À relire',
            'APPROVED' => 'Approuvé',
            'PARTIALLY_PUBLISHED' => 'Publication partielle',
            'PUBLISHED' => 'Publié',
            'ARCHIVED' => 'Archivé',
            'ERROR' => 'Erreur',
            'DELETED' => 'Supprimé',
        ][strtoupper($status)] ?? $status;
    }

    public function channelLabel(string $channel): string
    {
        return $this->channelOptions()[$channel] ?? $channel;
    }

    public function editorialLinksForChannel(string $channel): array
    {
        if ($channel === 'mapsi_site') {
            return [
                ['label' => 'MAPSI', 'url' => 'https://mapsi.fr/'],
                ['label' => 'Logiciel QHSE', 'url' => 'https://mapsi.fr/fr/'],
            ];
        }

        return [
            ['label' => 'OLING', 'url' => 'https://oling.fr/'],
            ['label' => 'Ressources OLING', 'url' => 'https://oling.fr/ressources'],
        ];
    }

    public function allowedChannels(string $type, array $requested): array
    {
        $allowed = self::TYPE_CHANNELS[$type] ?? [];

        return array_values(array_filter(array_map('strval', $requested), static fn (string $channel): bool => in_array($channel, $allowed, true)));
    }

    public function normalizeCampaign(array $campaign): array
    {
        $assets = [];
        $assetsByChannelList = [];
        $assetsByChannelLocale = [];
        foreach (($campaign['content_assets'] ?? []) as $asset) {
            if (!is_array($asset)) {
                continue;
            }
            $channel = (string) ($asset['channel'] ?? '');
            $locale = (string) ($asset['locale'] ?? 'fr-FR');
            $normalizedAsset = $asset + [
                'id' => '',
                'title' => '',
                'locale' => 'fr-FR',
                'content_html' => '',
                'content_text' => '',
                'status' => 'DRAFT',
                'version' => 0,
                'approved_by' => '',
                'approved_at' => null,
                'approved_version' => null,
                'published_at' => null,
                'public_url' => '',
                'image' => [],
                'seo_geo' => [],
                'editorial_quality' => [],
                'quality_blocking' => false,
            ];
            $messages = [
                'claim_without_source' => "Cette affirmation n'est associee a aucune source verifiable.",
                'claim_unknown_source' => "La source de cette affirmation ne fait pas partie des preuves fournies.",
                'unknown_source_id' => "Une reference de source est inconnue.",
                'unverified_claim' => "Une affirmation reste a verifier avec une source fiable.",
                'unauthorized_client_reference' => "Une reference client non autorisee doit etre retiree.",
                'content_too_similar' => "L'article ressemble trop a un contenu existant.",
                'body_too_short' => "L'article est trop court.",
                'body_too_long' => "L'article depasse la longueur editoriale prevue.",
                'manual_content_changed' => "Le texte a change : ses affirmations doivent etre controlees a nouveau.",
            ];
            $grouped = [];
            foreach ($normalizedAsset['editorial_quality']['issues'] ?? [] as $issue) {
                $code = $issue['code'] ?? 'editorial';
                $grouped[$code] ??= ['label' => $messages[$code] ?? ($issue['message'] ?? 'Controle editorial'), 'count' => 0];
                ++$grouped[$code]['count'];
            }
            $normalizedAsset['quality_issues'] = array_values($grouped);
            $html = (new \App\Service\GrowthContentSanitizer())->sanitizeHtml($normalizedAsset['content_html']);
            if (!empty($normalizedAsset['image']['image_url'])) {
                $original = '/studio-simple/assets/'.$normalizedAsset['id'].'/image';
                $proxy = '/admin/growth/campaigns/'.rawurlencode((string) $campaign['id']).'/assets/'.rawurlencode((string) $normalizedAsset['id']).'/image';
                if (!str_contains($html,$original)) {
                    $html = '<figure><img src="'.$original.'" alt="'.htmlspecialchars((string) ($normalizedAsset['image']['alt_text'] ?? $normalizedAsset['title']), ENT_QUOTES).'"></figure>'.$html;
                }
                $html = str_replace($original,$proxy,$html);
            }
            $normalizedAsset['editable_html'] = $html;
            $assets[$channel] ??= $normalizedAsset;
            $assetsByChannelList[$channel][] = $normalizedAsset;
            $assetsByChannelLocale[$channel][$locale] = $normalizedAsset;
        }

        return $campaign + [
            'id' => '',
            'name' => '',
            'campaign_type' => '',
            'theme' => '',
            'status' => 'DRAFT',
            'selected_channels' => [],
            'content_assets' => [],
            'publications' => [],
            'generation' => null,
            'assets_by_channel' => $assets,
            'assets_by_channel_list' => $assetsByChannelList,
            'assets_by_channel_locale' => $assetsByChannelLocale,
        ];
    }

    public function parityMatrix(): array
    {
        return [
            ['feature' => 'Campagnes et historique', 'status' => 'Repris via Growth Factory /studio-simple', 'phase' => 'Phase 3'],
            ['feature' => 'Types MAPSI_USERS / MAPSI_MARKETING / OLING', 'status' => 'Repris', 'phase' => 'Phase 3'],
            ['feature' => 'Assets multicanaux', 'status' => 'Repris via content_assets', 'phase' => 'Phase 3'],
            ['feature' => 'Versionnement par canal', 'status' => 'Préservé via expected_version', 'phase' => 'Phase 3'],
            ['feature' => 'Revue et approbation humaine', 'status' => 'Repris via /review et /approve', 'phase' => 'Phase 5'],
            ['feature' => 'Archivage / dépublication explicites', 'status' => 'Repris via /archive et /unpublish', 'phase' => 'Phase 5'],
            ['feature' => 'Weekly Packs', 'status' => 'Conservé Growth Factory, écran avancé à finaliser', 'phase' => 'Lot ultérieur'],
            ['feature' => 'Source Packs', 'status' => 'Conservé Growth Factory, écran avancé à finaliser', 'phase' => 'Lot ultérieur'],
            ['feature' => 'Planification / retry / paramètres canaux', 'status' => 'Conservé Growth Factory, non exposé dans MVP admin', 'phase' => 'Lot ultérieur'],
        ];
    }
}
