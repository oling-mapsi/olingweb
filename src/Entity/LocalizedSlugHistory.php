<?php

namespace App\Entity;

use App\Repository\LocalizedSlugHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocalizedSlugHistoryRepository::class)]
#[ORM\Table(
    name: 'localized_slug_history',
    indexes: [
        new ORM\Index(name: 'idx_localized_slug_history_lookup', columns: ['resource_type', 'resource_id', 'locale', 'old_slug']),
    ]
)]
class LocalizedSlugHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private string $resourceType = '';

    #[ORM\Column]
    private int $resourceId = 0;

    #[ORM\Column(length: 5)]
    private string $locale = SitePageTranslation::LOCALE_FR;

    #[ORM\Column(length: 255)]
    private string $oldSlug = '';

    #[ORM\Column(length: 255)]
    private string $newSlug = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $changedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $changedBy = null;
}
