<?php
namespace App\Service;
use App\Entity\{CmsMedia,Services,SitePage,Practice,Projet,Metier,LegalPage};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
final class CmsMediaManager {
 public const CLASSES=['service'=>Services::class,'page'=>SitePage::class,'practice'=>Practice::class,'project'=>Projet::class,'sector'=>Metier::class,'legal'=>LegalPage::class];
 public function __construct(private EntityManagerInterface $em,#[Autowire('%kernel.project_dir%/public')] private string $publicDir){}
 public function owner(string $type,int $id): ?object {return isset(self::CLASSES[$type])?$this->em->find(self::CLASSES[$type],$id):null;}
 public function media(string $type,object $owner,?string $locale=null): array {
  $rows=$this->em->getRepository(CmsMedia::class)->findBy([$type=>$owner],['sortOrder'=>'ASC','id'=>'ASC']);
  return $locale===null?$rows:array_values(array_filter($rows,fn($r)=>$r->getLocale()===$locale));
 }
 public function assertPublicImage(string $path): void {
  $relative=ltrim($path,'/');$parts=explode('/',$relative);
  if(str_starts_with($path,'//')||str_contains($path,':')||in_array('..',$parts,true)||!preg_match('/\.(?:png|jpe?g|gif|webp|avif|svg)$/i',$relative))throw new \InvalidArgumentException('Choisir un fichier image public local.');
  $file=realpath($this->publicDir.'/'.$relative);$root=realpath($this->publicDir);$uploads=realpath($this->publicDir.'/uploads');
  if(!$file||!is_file($file)||!(str_starts_with($file,$root.'/')||$uploads&&str_starts_with($file,$uploads.'/')))throw new \InvalidArgumentException('Image publique indisponible.');
 }
 public function setNativeHero(object $owner,?string $path): void {
  if($owner instanceof SitePage){$owner->setHeroImage($path)->setHeroMediaMode($path?'custom':'hidden');return;}
  foreach(['ImageHero','Image2','Image'] as $f)if(method_exists($owner,'set'.$f)){$owner->{'set'.$f}($path);return;}
 }
 public function nativeHero(object $owner): ?string {
  foreach(['HeroImage','ImageHero','Image2','Image1','Image'] as $f)if(method_exists($owner,'get'.$f)&&$owner->{'get'.$f}())return $owner->{'get'.$f}();return null;
 }
}
