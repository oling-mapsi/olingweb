<?php

namespace App\Entity;

use App\Repository\AgencyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgencyRepository::class)]
class Agency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 190)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $streetLine1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $streetLine2 = null;

    #[ORM\Column(length: 20)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 120)]
    private ?string $city = null;

    #[ORM\Column(length: 120)]
    private ?string $country = null;

    #[ORM\Column(length: 2)]
    private ?string $countryCode = null;

    #[ORM\Column(nullable: true)]
    private ?int $position = null;

    public function __construct()
    {
        $this->country = 'FRANCE';
        $this->countryCode = 'FR';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStreetLine1(): ?string
    {
        return $this->streetLine1;
    }

    public function setStreetLine1(string $streetLine1): self
    {
        $this->streetLine1 = $streetLine1;

        return $this;
    }

    public function getStreetLine2(): ?string
    {
        return $this->streetLine2;
    }

    public function setStreetLine2(?string $streetLine2): self
    {
        $this->streetLine2 = $streetLine2;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getStreetAddress(): string
    {
        $lines = array_filter([$this->streetLine1, $this->streetLine2]);

        return implode(', ', $lines);
    }
}
