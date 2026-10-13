<?php

namespace App\Service\GrowthFactory;

final class GrowthFactoryView
{
    private const TYPE_CHANNELS = [
        'MAPSI_USERS' => ['mapsi_site', 'linkedin_manual', 'email_manual'],
        'MAPSI_MARKETING' => ['mapsi_site', 'oling_site', 'linkedin_manual', 'email_manual'],
        'OLING' => ['oling_site', 'linkedin_manual', 'email_manual'],
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

    public function allowedChannels(string $type, array $requested): array
    {
        $allowed = self::TYPE_CHANNELS[$type] ?? [];

        return array_values(array_filter(array_map('strval', $requested), static fn (string $channel): bool => in_array($channel, $allowed, true)));
    }

    public function normalizeCampaign(array $campaign): array
    {
        $assets = [];
        foreach (($campaign['content_assets'] ?? []) as $asset) {
            if (!is_array($asset)) {
                continue;
            }
            $channel = (string) ($asset['channel'] ?? '');
            $assets[$channel] = $asset + [
                'id' => '',
                'title' => '',
                'content_html' => '',
                'content_text' => '',
                'status' => 'DRAFT',
                'version' => 0,
                'published_at' => null,
                'public_url' => '',
            ];
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
            'assets_by_channel' => $assets,
        ];
    }

    public function parityMatrix(): array
    {
        return [
            ['feature' => 'Campagnes et historique', 'status' => 'Repris via Growth Factory /studio-simple', 'phase' => 'Phase 3'],
            ['feature' => 'Types MAPSI_USERS / MAPSI_MARKETING / OLING', 'status' => 'Repris', 'phase' => 'Phase 3'],
            ['feature' => 'Assets multicanaux', 'status' => 'Repris via content_assets', 'phase' => 'Phase 3'],
            ['feature' => 'Versionnement par canal', 'status' => 'Préservé via expected_version', 'phase' => 'Phase 3'],
            ['feature' => 'Weekly Packs', 'status' => 'Conservé Growth Factory, écran avancé à finaliser', 'phase' => 'Lot ultérieur'],
            ['feature' => 'Source Packs', 'status' => 'Conservé Growth Factory, écran avancé à finaliser', 'phase' => 'Lot ultérieur'],
            ['feature' => 'Planification / retry / paramètres canaux', 'status' => 'Conservé Growth Factory, non exposé dans MVP admin', 'phase' => 'Lot ultérieur'],
        ];
    }
}
