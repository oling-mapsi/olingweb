<?php
namespace App\Service;
use App\Entity\Services;
final class LegacyMapsiPublicUsage
{
    private const PUBLISHED = [20=>'arborescence',21=>'automatisation-et-workflow',19=>'integration-progiciel',29=>'mapsi-audit',30=>'mapsi-pca',28=>'mapsi-portfolio',26=>'mapsi-qualiopi',27=>'mapsi-risques',31=>'mapsi-secu'];
    public static function slugs(): array { return array_values(self::PUBLISHED); }
    public static function isPreservedUsage(Services $service): bool
    {
        return $service->getPractice() === null && (self::PUBLISHED[$service->getId()] ?? null) === $service->getSlug();
    }
}
