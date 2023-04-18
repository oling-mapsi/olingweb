<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $noncomplet = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $shortcv = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkedin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNoncomplet(): ?string
    {
        return $this->noncomplet;
    }

    public function setNoncomplet(string $noncomplet): self
    {
        $this->noncomplet = $noncomplet;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getShortcv(): ?string
    {
        return $this->shortcv;
    }

    public function setShortcv(?string $shortcv): self
    {
        $this->shortcv = $shortcv;

        return $this;
    }

    public function getLinkedin(): ?string
    {
        return $this->linkedin;
    }

    public function setLinkedin(?string $linkedin): self
    {
        $this->linkedin = $linkedin;

        return $this;
    }
}
