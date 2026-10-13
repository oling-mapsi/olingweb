<?php

namespace App\Service\GrowthFactory;

final class GrowthFactoryRoleMapper
{
    /** @param string[] $roles */
    public function map(array $roles): array
    {
        if (array_intersect($roles, ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN'])) {
            return ['ROLE_GROWTH_VIEW', 'ROLE_GROWTH_REVIEW', 'ROLE_GROWTH_APPROVE', 'ROLE_GROWTH_PUBLISH', 'ROLE_GROWTH_ADMIN'];
        }

        return array_values(array_filter(array_unique($roles), static fn (string $role): bool => str_starts_with($role, 'ROLE_GROWTH_')));
    }
}
