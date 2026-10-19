<?php
namespace App\EventSubscriber;
use App\Entity\{CmsMedia,SitePage,Projet};
use App\Service\CmsCatalogue;
use App\Service\I18n\LocalizedUrlGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Twig\Environment;
/** Optional media: empty table produces byte-identical HTML. Never edits editorial storage. */
final class CmsMediaRenderSubscriber implements EventSubscriberInterface {
 public function __construct(private EntityManagerInterface $em,private CmsCatalogue $catalogue,private Environment $twig,private LocalizedUrlGenerator $localizedUrls){}
 public static function getSubscribedEvents(): array {return ['kernel.response'=>['render',-10]];}
 public function render(ResponseEvent $event): void {
  if(!$event->isMainRequest()||$event->getResponse()->getStatusCode()!==200)return;
  $request=$event->getRequest();$path=$request->getPathInfo();
  if(str_starts_with($path,'/admin')||!str_contains($event->getResponse()->headers->get('Content-Type',''),'text/html'))return;
  $rows=$this->em->getRepository(CmsMedia::class)->findBy(['locale'=>$request->getLocale()],['sortOrder'=>'ASC','id'=>'ASC']);
  if(!$rows)return;
  $html=$event->getResponse()->getContent();$groups=[];
  foreach($rows as $media){$owner=$media->getOwner();if(!$owner)continue;
   $urls=[$this->catalogue->publicPath($owner)];
   if($owner instanceof SitePage)$urls[]=$this->localizedUrls->sitePagePath($owner,$request->getLocale());
   if($owner instanceof Projet)$urls=[]; // Reference targets are not independent project pages.
   if($owner instanceof Projet && $owner->isFeaturedProjects() && in_array($path,['/projets','/en/projects','/es/proyectos'],true))$urls[]=$path;
   if(!in_array($path,$urls,true))continue;
   $hubHero=$media->getSlot()==='hero' && $owner instanceof SitePage && in_array($owner->getSlug(),['expertises-index','secteurs-index','projets','apropos','ressources','services'],true);
   // Native image selection wins over a stale auxiliary media row.
   if($hubHero && ($owner->getHeroMediaMode()==='hidden' || ltrim((string)$owner->getHeroImage(),'/')!==ltrim($media->getPath(),'/')))continue;
   $figure=$this->twig->render('public/_cms_media_figure.html.twig',['media'=>$media]);
   if($media->getSlot()==='hero'&&!$owner instanceof Projet){
    $pattern='~<figure\b[^>]*class="[^"]*oling-shell-hero__media[^"]*"[^>]*>.*?</figure>~s';
    if(preg_match($pattern,$html)){$heroFigure=$this->twig->render('public/_cms_media_figure.html.twig',['media'=>$media,'insideHero'=>$hubHero]);$html=preg_replace_callback($pattern,static fn()=>$heroFigure,$html,1);}
    elseif(preg_match('~<img\b[^>]*class="[^"]*oling-resource-hero-image[^"]*"[^>]*>~',$html))$html=preg_replace('~<img\b[^>]*class="[^"]*oling-resource-hero-image[^"]*"[^>]*>~',$figure,$html,1);
    elseif($figure)$groups['before-content'][]=$figure;
   }else{$groups[$media->getPlacement()][]=$figure;}
  }
  foreach($groups as $placement=>$figures){$chunk=implode('', $figures);
   if($placement==='before-content')$html=preg_replace_callback('~<main\b[^>]*>~',fn($m)=>$m[0].$chunk,$html,1);
   elseif(preg_match('/^after-h2-([1-3])$/',$placement,$m)){
    $wanted=(int)$m[1];$n=0;$found=false;$html=preg_replace_callback('~</h2>~i',function($m)use(&$n,$wanted,$chunk,&$found){if(++$n===$wanted){$found=true;return $m[0].$chunk;}return $m[0];},$html);
    if(!$found)$html=str_replace('</main>',$chunk.'</main>',$html);
   }else{$html=str_replace('</main>',$chunk.'</main>',$html);}
  }
  $event->getResponse()->setContent($html);
 }
}
