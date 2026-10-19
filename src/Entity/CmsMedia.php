<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
#[ORM\Table(name: 'cms_media')]
#[ORM\Index(name:'idx_cms_media_locale_order',columns:['locale','sort_order','id'])]
class CmsMedia {
 #[ORM\Id, ORM\GeneratedValue, ORM\Column] private ?int $id=null;
 #[ORM\Column(length:16)] private string $slot='secondary';
 #[ORM\Column(length:8)] private string $locale='fr';
 #[ORM\Column(length:255)] private string $path='';
 #[ORM\Column(length:255)] private string $alt='';
 #[ORM\Column(type:'text',nullable:true)] private ?string $caption=null;
 #[ORM\Column] private int $sortOrder=0;
 #[ORM\Column(length:32)] private string $placement='after-content';
 #[ORM\Column(length:16)] private string $displayMode='contain';
 #[ORM\Column(length:16)] private string $size='standard';
 #[ORM\Column] private int $focalX=50;
 #[ORM\Column] private int $focalY=50;
 #[ORM\ManyToOne(targetEntity:Services::class)]
 #[ORM\JoinColumn(nullable:true,onDelete:'CASCADE')] private ?Services $service=null;
 #[ORM\ManyToOne(targetEntity:SitePage::class)]
 #[ORM\JoinColumn(nullable:true,onDelete:'CASCADE')] private ?SitePage $page=null;
 #[ORM\ManyToOne(targetEntity:Practice::class)]
 #[ORM\JoinColumn(nullable:true,onDelete:'CASCADE')] private ?Practice $practice=null;
 #[ORM\ManyToOne(targetEntity:Projet::class)]
 #[ORM\JoinColumn(nullable:true,onDelete:'CASCADE')] private ?Projet $project=null;
 #[ORM\ManyToOne(targetEntity:Metier::class)]
 #[ORM\JoinColumn(nullable:true,onDelete:'CASCADE')] private ?Metier $sector=null;
 #[ORM\ManyToOne(targetEntity:LegalPage::class)]
 #[ORM\JoinColumn(nullable:true,onDelete:'CASCADE')] private ?LegalPage $legal=null;
 public const OWNERS=['service','page','practice','project','sector','legal'];
 public function getId(): ?int {return $this->id;}
 public function getSlot(): string {return $this->slot;}
 public function setSlot(string $v): self {$this->slot=$v;return $this;}
 public function getLocale(): string {return $this->locale;}
 public function setLocale(string $v): self {$this->locale=$v;return $this;}
 public function getPath(): string {return $this->path;}
 public function setPath(string $v): self {$this->path=$v;return $this;}
 public function getAlt(): string {return $this->alt;}
 public function setAlt(string $v): self {$this->alt=$v;return $this;}
 public function getCaption(): ?string {return $this->caption;}
 public function setCaption(?string $v): self {$this->caption=$v;return $this;}
 public function getSortOrder(): int {return $this->sortOrder;}
 public function setSortOrder(int $v): self {$this->sortOrder=$v;return $this;}
 public function getPlacement(): string {return $this->placement;}
 public function setPlacement(string $v): self {$this->placement=$v;return $this;}
 public function getDisplayMode(): string {return $this->displayMode;}
 public function setDisplayMode(string $v): self {$this->displayMode=$v;return $this;}
 public function getSize(): string {return $this->size;}
 public function setSize(string $v): self {$this->size=$v;return $this;}
 public function getFocalX(): int {return $this->focalX;}
 public function setFocalX(int $v): self {$this->focalX=$v;return $this;}
 public function getFocalY(): int {return $this->focalY;}
 public function setFocalY(int $v): self {$this->focalY=$v;return $this;}
 public function setOwner(string $type,object $owner): self {
  $class=match($type){'service'=>Services::class,'page'=>SitePage::class,'practice'=>Practice::class,'project'=>Projet::class,'sector'=>Metier::class,'legal'=>LegalPage::class,default=>throw new \InvalidArgumentException('Invalid owner')};
  if(!$owner instanceof $class)throw new \InvalidArgumentException('Owner type mismatch');
  foreach(self::OWNERS as $field)$this->{$field}=null; $this->{$type}=$owner;return $this;
 }
 public function getOwner(): ?object {foreach(self::OWNERS as $f)if($this->{$f}!==null)return $this->{$f};return null;}
 public function getOwnerType(): ?string {foreach(self::OWNERS as $f)if($this->{$f}!==null)return $f;return null;}
}
