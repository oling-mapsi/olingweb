<?php

namespace App\Entity;

use App\Repository\ErpQuestionnaireSubmissionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ErpQuestionnaireSubmissionRepository::class)]
#[ORM\Table(name: 'erp_questionnaire_submission')]
#[ORM\HasLifecycleCallbacks]
class ErpQuestionnaireSubmission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    private ?string $publicToken = null;

    #[ORM\Column(length: 255)]
    private string $fullName = '';

    #[ORM\Column(length: 255)]
    private string $email = '';

    #[ORM\Column(length: 50)]
    private string $phone = '';

    #[ORM\Column(length: 255)]
    private string $company = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $jobTitle = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sector = null;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $organizationSize = null;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $userCount = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $solutionType = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $urgency = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $budgetRange = null;

    #[ORM\Column(type: Types::JSON)]
    private array $answers = [];

    #[ORM\Column(type: Types::JSON)]
    private array $summary = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $scoring = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $emailedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $retentionPurgeAt = null;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->retentionPurgeAt = $now->modify('+180 days');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\PrePersist]
    public function initializeTimestamps(): void
    {
        $this->createdAt ??= new \DateTimeImmutable();
        $this->retentionPurgeAt ??= $this->createdAt->modify('+180 days');
    }

    public function getPublicToken(): ?string
    {
        return $this->publicToken;
    }

    public function setPublicToken(string $publicToken): self
    {
        $this->publicToken = $publicToken;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    public function setCompany(string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(?string $jobTitle): self
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    public function getSector(): ?string
    {
        return $this->sector;
    }

    public function setSector(?string $sector): self
    {
        $this->sector = $sector;

        return $this;
    }

    public function getOrganizationSize(): ?string
    {
        return $this->organizationSize;
    }

    public function setOrganizationSize(?string $organizationSize): self
    {
        $this->organizationSize = $organizationSize;

        return $this;
    }

    public function getUserCount(): ?string
    {
        return $this->userCount;
    }

    public function setUserCount(?string $userCount): self
    {
        $this->userCount = $userCount;

        return $this;
    }

    public function getSolutionType(): ?string
    {
        return $this->solutionType;
    }

    public function setSolutionType(?string $solutionType): self
    {
        $this->solutionType = $solutionType;

        return $this;
    }

    public function getUrgency(): ?string
    {
        return $this->urgency;
    }

    public function setUrgency(?string $urgency): self
    {
        $this->urgency = $urgency;

        return $this;
    }

    public function getBudgetRange(): ?string
    {
        return $this->budgetRange;
    }

    public function setBudgetRange(?string $budgetRange): self
    {
        $this->budgetRange = $budgetRange;

        return $this;
    }

    public function getAnswers(): array
    {
        return $this->answers;
    }

    public function setAnswers(array $answers): self
    {
        $this->answers = $answers;

        return $this;
    }

    public function getSummary(): array
    {
        return $this->summary;
    }

    public function setSummary(array $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getScoring(): array
    {
        return $this->scoring ?? [];
    }

    public function setScoring(?array $scoring): self
    {
        $this->scoring = $scoring ?? [];

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getEmailedAt(): ?\DateTimeImmutable
    {
        return $this->emailedAt;
    }

    public function setEmailedAt(?\DateTimeImmutable $emailedAt): self
    {
        $this->emailedAt = $emailedAt;

        return $this;
    }

    public function getRetentionPurgeAt(): ?\DateTimeImmutable
    {
        return $this->retentionPurgeAt;
    }

    public function setRetentionPurgeAt(\DateTimeImmutable $retentionPurgeAt): self
    {
        $this->retentionPurgeAt = $retentionPurgeAt;

        return $this;
    }
}
