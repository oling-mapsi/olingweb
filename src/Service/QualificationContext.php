<?php
namespace App\Service;

/** Explicit journey policy shared by Twig, chat intake and CRM attribution. */
final class QualificationContext
{
    private const PATHS = [
        '/erp-progiciel' => 'erp', '/business-apps/erp' => 'erp',
        '/erp-progiciel/questionnaire' => 'erp',
        '/en/erp-software' => 'erp', '/en/erp-software/questionnaire' => 'erp',
        '/es/software-erp' => 'erp', '/es/software-erp/cuestionario' => 'erp',
        '/facturation-electronique-amoa' => 'rfe', '/consulting/reforme-facturation-electronique-amoa' => 'rfe',
        '/crm' => 'crm', '/en/crm-advisory' => 'crm', '/es/asesoria-crm' => 'crm',
        '/en/e-invoicing-project-advisory' => 'rfe', '/es/asesoria-facturacion-electronica' => 'rfe',
        '/en/outsourced-dpo-ongoing-gdpr-governance' => 'dpo', '/es/dpo-externo-gobernanza-rgpd-continua' => 'dpo',
        '/en/ai-project-advisory-programme-management-business-agents' => 'amoa-ia',
        '/es/asesoria-ia-gestion-proyectos-agentes-negocio' => 'amoa-ia',
        '/en/services/ai-project-advisory-management-and-business-agents' => 'amoa-ia',
        '/es/servicios/asesoria-ia-gestion-de-proyectos-ia-y-agentes-de-negocio' => 'amoa-ia',
        '/es/asesoria-erp-software-gestion' => 'erp',
        '/consulting/amoa-ia-pilotage-de-projets-ia-et-agents-metier' => 'amoa-ia',
        '/expertises/amoa-ia-pilotage-projets-agents' => 'amoa-ia',
        '/expertises/rgpd-dpo-gouvernance' => 'dpo',
    ];

    public static function path(?string $path): string
    {
        return rtrim((string) parse_url($path ?? '', PHP_URL_PATH), '/') ?: '/';
    }

    public static function need(?string $path): string
    {
        return self::PATHS[self::path($path)] ?? 'contact';
    }

    public static function allowsErp(?string $path): bool
    {
        return self::need($path) === 'erp';
    }
}
