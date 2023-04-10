<?php

namespace App\Entity;

use App\Repository\ServicesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServicesRepository::class)]
class Services
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Designation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $Introduction_short = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description_short = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $designation_short = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ico = null;

    #[ORM\Column(length: 255)]
    private ?string $image1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image2 = null;

    #[ORM\ManyToOne(inversedBy: 'services')]
    private ?Practice $practice = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, nullable: true)]
    #[Gedmo\Slug(fields: ["designation"])]
    private string $slug;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->Designation;
    }

    public function setDesignation(string $Designation): self
    {
        $this->Designation = $Designation;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getIntroductionShort(): ?string
    {
        return $this->Introduction_short;
    }

    public function setIntroductionShort(?string $Introduction_short): self
    {
        $this->Introduction_short = $Introduction_short;

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

    public function getDescriptionShort(): ?string
    {
        return $this->description_short;
    }

    public function setDescriptionShort(?string $description_short): self
    {
        $this->description_short = $description_short;

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

    public function setImage1(string $image1): self
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

    public function getPractice(): ?Practice
    {
        return $this->practice;
    }

    public function setPractice(?Practice $practice): self
    {
        $this->practice = $practice;

        return $this;
    }
}
