<?php

namespace App\Entity;

use App\Repository\PracticeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PracticeRepository::class)]
class Practice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $designation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $introduction = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $introduction_short = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description_short = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ico = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image2 = null;

    #[ORM\OneToMany(mappedBy: 'practice', targetEntity: Services::class)]
    private Collection $services;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, nullable: false)]
    #[Gedmo\Slug(fields: ["designation"])]
    private string $slug;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $designation_short = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $class1 = null;

    public function __construct()
    {
        $this->services = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
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

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(?string $introduction): self
    {
        $this->introduction = $introduction;

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

    public function getIntroductionShort(): ?string
    {
        return $this->introduction_short;
    }

    public function setIntroductionShort(?string $introduction_short): self
    {
        $this->introduction_short = $introduction_short;

        return $this;
    }

    public function getDescriptionShort(): ?string
    {
        return $this->description_short;
    }

    public function setDescriptionShort(?string $description_short): self
    {
        $this->description_short = $description_short;

        return $this;
    }

    public function getIco(): ?string
    {
        return $this->ico;
    }

    public function setIco(?string $ico): self
    {
        $this->ico = $ico;

        return $this;
    }

    public function getImage1(): ?string
    {
        return $this->image1;
    }

    public function setImage1(?string $image1): self
    {
        $this->image1 = $image1;

        return $this;
    }

    public function getImage2(): ?string
    {
        return $this->image2;
    }

    public function setImage2(?string $image2): self
    {
        $this->image2 = $image2;

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
            $service->setPractice($this);
        }

        return $this;
    }

    public function removeService(Services $service): self
    {
        if ($this->services->removeElement($service)) {
            // set the owning side to null (unless already changed)
            if ($service->getPractice() === $this) {
                $service->setPractice(null);
            }
        }

        return $this;
    }

    public function getDesignationShort(): ?string
    {
        return $this->designation_short;
    }

    public function setDesignationShort(?string $designation_short): self
    {
        $this->designation_short = $designation_short;

        return $this;
    }

    public function getClass1(): ?string
    {
        return $this->class1;
    }

    public function setClass1(?string $class1): self
    {
        $this->class1 = $class1;

        return $this;
    }
}
