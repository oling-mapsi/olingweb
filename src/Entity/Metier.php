<?php

namespace App\Entity;

use App\Repository\MetierRepository;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MetierRepository::class)]
class Metier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $designation = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Slug(fields: ['designation'])]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageHero = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $homeHeroIntro = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $homeHeroText1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $homeHeroText2 = null;

    #[ORM\OneToMany(mappedBy: 'metier', targetEntity: Projet::class)]
    private Collection $projets;

    public function __construct()
    {
        $this->projets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImageHero(): ?string
    {
        return $this->imageHero;
    }

    public function setImageHero(?string $imageHero): self
    {
        $this->imageHero = $imageHero;

        return $this;
    }

    public function getHomeHeroIntro(): ?string
    {
        return $this->homeHeroIntro;
    }

    public function setHomeHeroIntro(?string $homeHeroIntro): self
    {
        $this->homeHeroIntro = $homeHeroIntro;

        return $this;
    }

    public function getHomeHeroText1(): ?string
    {
        return $this->homeHeroText1;
    }

    public function setHomeHeroText1(?string $homeHeroText1): self
    {
        $this->homeHeroText1 = $homeHeroText1;

        return $this;
    }

    public function getHomeHeroText2(): ?string
    {
        return $this->homeHeroText2;
    }

    public function setHomeHeroText2(?string $homeHeroText2): self
    {
        $this->homeHeroText2 = $homeHeroText2;

        return $this;
    }

    /**
     * @return Collection<int, Projet>
     */
    public function getProjets(): Collection
    {
        return $this->projets;
    }

    public function addProjet(Projet $projet): self
    {
        if (!$this->projets->contains($projet)) {
            $this->projets->add($projet);
            $projet->setMetier($this);
        }

        return $this;
    }

    public function removeProjet(Projet $projet): self
    {
        if ($this->projets->removeElement($projet)) {
            // set the owning side to null (unless already changed)
            if ($projet->getMetier() === $this) {
                $projet->setMetier(null);
            }
        }

        return $this;
    }

}
