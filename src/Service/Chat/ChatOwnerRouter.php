<?php

namespace App\Service\Chat;

final class ChatOwnerRouter
{
    public const AMOA = '/amoa-si';
    public const ERP = '/business-apps/erp';
    public const FINANCE = '/si-finance';
    public const CRM = '/crm';
    public const GMAO = '/gmao';
    public const RFE = '/facturation-electronique-amoa';

    public function resolveOwnerUrl(string $query): ?string
    {
        $text = $this->normalize($query);

        if ($this->matches($text, '/(rfe|facturation electronique|e reporting|plateforme de dematerialisation)/')) {
            return self::RFE;
        }
        if ($this->matches($text, '/(amoa si finance|si finance|erp finance|finance system|systeme d information finance|systeme financier)/')) {
            return self::FINANCE;
        }
        if ($this->matches($text, '/\b(gmao|gestion de maintenance|gestion des interventions|maintenance assistee|cahier des charges maintenance)\b/')) {
            return self::GMAO;
        }
        if ($this->matches($text, '/(\bcrm\b|si client|systeme d information client|relation client|gestion client)/')) {
            return self::CRM;
        }
        if ($this->matches($text, '/\b(erp|pgi|progiciel|sage x3|sap|s4hana|divalto|cegid)\b/') || $this->looksLikeErpProcurement($text)) {
            return self::ERP;
        }
        if ($this->matches($text, '/\b(amoa|amo|maitrise d ouvrage|assistance a maitrise d ouvrage)\b/')) {
            return self::AMOA;
        }

        return null;
    }

    private function looksLikeErpProcurement(string $text): bool
    {
        if (!$this->matches($text, '/\b(amoa|amo|maitrise d ouvrage|assistance a maitrise d ouvrage)\b/')) {
            return false;
        }

        $signals = 0;
        foreach (['cahier des charges', 'consultation', 'editeur', 'integrateur', 'migration', 'reprise de donnees', 'recette', 'mise en production'] as $signal) {
            if (str_contains($text, $signal)) {
                ++$signals;
            }
        }

        return $signals >= 2;
    }

    private function matches(string $value, string $pattern): bool
    {
        return preg_match($pattern, $value) === 1;
    }

    private function normalize(string $value): string
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
