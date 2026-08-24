<?php

namespace App\Entity;

use App\Repository\ProjetRepository;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjetRepository::class)]
class Projet
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

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: Services::class, inversedBy: 'projets')]
    private Collection $services;

    #[ORM\ManyToOne(inversedBy: 'projets')]
    private ?Metier $metier = null;

    #[ORM\ManyToMany(targetEntity: Team::class, inversedBy: 'projets')]
    private Collection $teams;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageHero = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $class = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $featuredProjects = false;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $featuredProjectsRank = null;

    #[ORM\Column(length: 32, nullable: true, unique: true)]
    private ?string $externalId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $clientName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $territory = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $periodLabel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $publicUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $shortDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $proofStatus = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $publicationStatus = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $softwareTags = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $softwareFamilies = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $softwareRelation = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $softwarePriority = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $historicalReference = false;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    public function __construct()
    {
        $this->services = new ArrayCollection();
        $this->teams = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Services>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Services $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
        }

        return $this;
    }

    public function removeService(Services $service): self
    {
        $this->services->removeElement($service);

        return $this;
    }

    public function getMetier(): ?Metier
    {
        return $this->metier;
    }

    public function setMetier(?Metier $metier): self
    {
        $this->metier = $metier;

        return $this;
    }

    /**
     * @return Collection<int, Team>
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
            $team->addProjet($this);
        }

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        if ($this->teams->removeElement($team)) {
            $team->removeProjet($this);
        }

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

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(?string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function isFeaturedProjects(): bool
    {
        return $this->featuredProjects;
    }

    public function setFeaturedProjects(bool $featuredProjects): self
    {
        $this->featuredProjects = $featuredProjects;

        return $this;
    }

    public function getFeaturedProjectsRank(): ?int
    {
        return $this->featuredProjectsRank;
    }

    public function setFeaturedProjectsRank(?int $featuredProjectsRank): self
    {
        $this->featuredProjectsRank = $featuredProjectsRank;

        return $this;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(?string $externalId): self
    {
        $this->externalId = $externalId;

        return $this;
    }

    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    public function setClientName(?string $clientName): self
    {
        $this->clientName = $clientName;

        return $this;
    }

    public function getTerritory(): ?string
    {
        return $this->territory;
    }

    public function setTerritory(?string $territory): self
    {
        $this->territory = $territory;

        return $this;
    }

    public function getPeriodLabel(): ?string
    {
        return $this->periodLabel;
    }

    public function setPeriodLabel(?string $periodLabel): self
    {
        $this->periodLabel = $periodLabel;

        return $this;
    }

    public function getPublicUrl(): ?string
    {
        return $this->publicUrl;
    }

    public function setPublicUrl(?string $publicUrl): self
    {
        $this->publicUrl = $publicUrl;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getProofStatus(): ?string
    {
        return $this->proofStatus;
    }

    public function setProofStatus(?string $proofStatus): self
    {
        $this->proofStatus = $proofStatus;

        return $this;
    }

    public function getPublicationStatus(): ?string
    {
        return $this->publicationStatus;
    }

    public function setPublicationStatus(?string $publicationStatus): self
    {
        $this->publicationStatus = $publicationStatus;

        return $this;
    }

    public function getSoftwareTags(): array
    {
        return $this->softwareTags ?? [];
    }

    public function setSoftwareTags(?array $softwareTags): self
    {
        $this->softwareTags = $softwareTags;

        return $this;
    }

    public function getSoftwareFamilies(): array
    {
        return $this->softwareFamilies ?? [];
    }

    public function setSoftwareFamilies(?array $softwareFamilies): self
    {
        $this->softwareFamilies = $softwareFamilies;

        return $this;
    }

    public function getSoftwareRelation(): ?string
    {
        return $this->softwareRelation;
    }

    public function setSoftwareRelation(?string $softwareRelation): self
    {
        $this->softwareRelation = $softwareRelation;

        return $this;
    }

    public function getSoftwarePriority(): ?string
    {
        return $this->softwarePriority;
    }

    public function setSoftwarePriority(?string $softwarePriority): self
    {
        $this->softwarePriority = $softwarePriority;

        return $this;
    }

    public function isHistoricalReference(): bool
    {
        return $this->historicalReference;
    }

    public function setHistoricalReference(bool $historicalReference): self
    {
        $this->historicalReference = $historicalReference;

        return $this;
    }

    public function getMetadata(): array
    {
        return $this->metadata ?? [];
    }

    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

}
