<?php

namespace App\Service\Analytics;

class AnalyticsPathNormalizer
{
    public function normalize(?string $path): string
    {
        $path = parse_url((string) $path, PHP_URL_PATH) ?: '/';
        $path = preg_replace('#/{2,}#', '/', $path) ?: '/';
        $path = rtrim($path, '/') ?: '/';

        return mb_substr($path, 0, 255);
    }

    public function family(string $path): string
    {
        return match (true) {
            $path === '/' => 'Accueil',
            str_contains($path, 'erp') || str_contains($path, 'amoa') => 'AMOA ERP',
            str_contains($path, 'crm') => 'CRM',
            str_contains($path, 'sirh') => 'SIRH',
            str_contains($path, 'gmao') => 'GMAO',
            str_contains($path, 'dsi') || str_contains($path, 'gouvernance') => 'DSI',
            str_contains($path, 'rgpd') || str_contains($path, 'dpo') => 'RGPD/DPO',
            str_contains($path, 'cyber') || str_contains($path, 'iso27001') => 'Cybersécurité',
            str_contains($path, 'qse') || str_contains($path, 'conformite') => 'Conformité',
            str_contains($path, 'mapsi') => 'MAPSI',
            str_contains($path, 'projet') || str_contains($path, 'reference') => 'Projets et références',
            default => 'Autres pages',
        };
    }
}
