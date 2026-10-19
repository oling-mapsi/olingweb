<?php
namespace App\Controller;
use App\Entity\{CmsMedia,SitePage};
use App\Service\{CmsMediaManager,CmsCatalogue,UploadManager};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\{Request,Response};
use Symfony\Component\Form\Extension\Core\Type\{TextType,TextareaType,ChoiceType,IntegerType,FileType,CheckboxType};
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints\{Image,Range,Length};
#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/catalogue/media')]
final class CmsMediaAdminController extends AbstractController {
 #[Route('/{technical}/{id}',name:'admin_catalogue_media',requirements:['technical'=>'service|page|practice|project|sector|legal'],methods:['GET','POST'])]
 public function edit(string $technical,int $id,Request $request,CmsMediaManager $manager,CmsCatalogue $catalogue,EntityManagerInterface $em,UploadManager $uploads): Response {
  $owner=$manager->owner($technical,$id);if(!$owner)throw $this->createNotFoundException();
  $mediaId=$request->query->getInt('media');$media=$mediaId?$em->find(CmsMedia::class,$mediaId):null;
  if($mediaId&&(!$media||$media->getOwnerType()!==$technical||$media->getOwner()?->getId()!==$id))throw $this->createNotFoundException();
  if($request->isMethod('POST')&&$request->request->has('unlink')){
   if(!$this->isCsrfTokenValid('cms_media_unlink_'.$technical.'_'.$id,(string)$request->request->get('_token')))throw $this->createAccessDeniedException();
   $target=$em->find(CmsMedia::class,$request->request->getInt('unlink'));
   if(!$target||$target->getOwnerType()!==$technical||$target->getOwner()?->getId()!==$id)throw $this->createNotFoundException();
   if($target->getSlot()==='hero'){$target->setPath('');$manager->setNativeHero($owner,null);}else{$em->remove($target);}
   $em->flush();$this->addFlash('success','Association retirée sur '.$technical.' #'.$id.' ; fichier conservé.');return $this->redirectToRoute('admin_catalogue_media',['technical'=>$technical,'id'=>$id]);
  }
  // Reopening the Hero editor edits its existing record instead of replacing its ID.
  if(!$mediaId && $request->query->get('slot')==='hero')foreach($manager->media($technical,$owner,'fr') as $existingHero)if($existingHero->getSlot()==='hero'){$media=$existingHero;$mediaId=$media->getId();break;}
  $media??=(new CmsMedia())->setOwner($technical,$owner);
  if(!$mediaId&&$request->query->get('slot')==='hero')$media->setSlot('hero')->setPath($manager->nativeHero($owner)??'');
  $library=[];
  foreach($catalogue->all() as $source)foreach(['Image','Image1','Image2','ImageHero','HeroImage','Ico'] as $field){$getter='get'.$field;if(!method_exists($source['object'],$getter))continue;$path=$source['object']->{$getter}();if(!$path)continue;try{$manager->assertPublicImage($path);$library[$source['technical'].' #'.$source['id'].' · '.$field.' · '.basename($path)]=$path;}catch(\InvalidArgumentException){}}
  foreach($em->getRepository(CmsMedia::class)->findAll() as $existing)if($existing->getPath())$library['Média #'.$existing->getId().' · '.basename($existing->getPath())]=$existing->getPath();
  $isHub=$owner instanceof SitePage && in_array($owner->getSlug(),['expertises-index','secteurs-index','projets','apropos','ressources'],true);
  $heroTranslation=$isHub?$owner->getTranslation($media->getLocale()):null;
  $frame=($heroTranslation?->getStructuredData()??[])['heroPresentation']['frame']??'none';
  if(!in_array($frame,['none','framed'],true))$frame='none';
  $builder=$this->createFormBuilder($media,['csrf_token_id'=>'cms_media_edit_'.$technical.'_'.$id])
   ->add('slot',ChoiceType::class,['label'=>'Rôle','choices'=>['Hero'=>'hero','Illustration secondaire'=>'secondary'],'disabled'=>$mediaId>0])
   ->add('locale',ChoiceType::class,['label'=>'Langue du texte alternatif','choices'=>['FR'=>'fr','EN'=>'en','ES'=>'es']])
   ->add('path',TextType::class,['label'=>'Réutiliser une image (chemin public)','required'=>false,'empty_data'=>'','constraints'=>[new Length(max:255)]])
   ->add('existingImage',ChoiceType::class,['label'=>'Bibliothèque de médias existants','mapped'=>false,'required'=>false,'placeholder'=>'Choisir une image existante','choices'=>$library,'choice_attr'=>static fn ($path)=>['data-path'=>(string)$path]])
   ->add('upload',FileType::class,['label'=>'Téléverser / remplacer','mapped'=>false,'required'=>false,'constraints'=>[new Image(maxSize:'10M',mimeTypes:['image/jpeg','image/png','image/webp','image/gif','image/avif'])]])
   ->add('hidden',CheckboxType::class,['label'=>'Masquer le hero','mapped'=>false,'required'=>false,'data'=>$media->getSlot()==='hero'&&($owner instanceof SitePage ? $owner->getHeroMediaMode()==='hidden' : $mediaId>0&&$media->getPath()==='')])
   ->add('alt',TextType::class,['label'=>'Texte alternatif','required'=>false,'empty_data'=>'','constraints'=>[new Length(max:255)]])
   ->add('caption',TextareaType::class,['label'=>'Légende','required'=>false])
   ->add('sortOrder',IntegerType::class,['label'=>'Ordre'])
   ->add('placement',ChoiceType::class,['label'=>'Position','choices'=>['Avant le contenu'=>'before-content','Après le contenu'=>'after-content','Après le premier H2'=>'after-h2-1','Après le deuxième H2'=>'after-h2-2','Après le troisième H2'=>'after-h2-3']])
   ->add('displayMode',ChoiceType::class,['label'=>'Affichage','choices'=>['Image entière'=>'contain','Recadrer'=>'cover','Libre'=>'free']])
   ->add('size',ChoiceType::class,['label'=>'Taille','choices'=>['Standard'=>'standard','Large'=>'large','Très large'=>'xlarge']])
   ->add('focalX',IntegerType::class,['label'=>'Point focal horizontal (%)','constraints'=>[new Range(min:0,max:100)]])
   ->add('focalY',IntegerType::class,['label'=>'Point focal vertical (%)','constraints'=>[new Range(min:0,max:100)]]);
  if($isHub)$builder->add('heroFrame',ChoiceType::class,['label'=>'Cadre du héros','mapped'=>false,'choices'=>['Sans cadre — image posée sur le fond'=>'none','Encadré — fond et bordure'=>'framed'],'data'=>$frame,'help'=>'Indépendant du cadrage de l’image. Sans cadre par défaut.']);
  $form=$builder->getForm();
  $originalMediaPath=$media->getPath();
  $form->handleRequest($request);
  if($form->isSubmitted() && $isHub && $media->getSlot()==='hero' && !$owner->getTranslation($media->getLocale()))$form->get('locale')->addError(new FormError('Créer d’abord la traduction de la page pour cette langue.'));
  if($form->isSubmitted()&&$form->isValid()){
   $file=$form->get('upload')->getData();
   $newImage=$file || $form->get('existingImage')->getData() || (trim($media->getPath())!=='' && $media->getPath()!==$originalMediaPath);
   // Selecting an image explicitly restores its visibility, even without JavaScript.
   $hidden=$form->get('hidden')->getData()&&$media->getSlot()==='hero'&&!$newImage;
   try{
    if($hidden)$media->setPath('');elseif($file)$media->setPath($uploads->upload($file,'cms/images'));else {if($form->get('existingImage')->getData())$media->setPath($form->get('existingImage')->getData());$manager->assertPublicImage($media->getPath());}
    if($media->getSlot()==='hero'){
     foreach($manager->media($technical,$owner,$media->getLocale()) as $old)if($old!==$media&&$old->getSlot()==='hero')$em->remove($old);
     // Native hero path is common; localized alt/options remain scoped to the selected locale.
     $manager->setNativeHero($owner,$media->getPath()?:null);
     if($owner instanceof SitePage)$owner->setHeroImageCaption($media->getCaption())->setHeroImageDisplayMode($media->getDisplayMode())->setHeroImageSize($media->getSize())->setHeroImageFocalPoint($media->getFocalX().'% '.$media->getFocalY().'%');
    }
    if($isHub && $media->getSlot()==='hero'){
     $translation=$owner->getTranslation($media->getLocale());$data=$translation->getStructuredData()??[];
     $data['heroPresentation']['frame']=$form->get('heroFrame')->getData();$translation->setStructuredData($data);
    }
    $em->persist($media);$em->flush();$this->addFlash('success','Média enregistré sur '.$technical.' #'.$id.' · '.($catalogue->publicPath($owner)??'rendu de référence intégré'));return $this->redirectToRoute('admin_catalogue_media',['technical'=>$technical,'id'=>$id,'media'=>$media->getId()]);
   }catch(\Symfony\Component\HttpFoundation\File\Exception\FileException $e){$form->get('upload')->addError(new FormError('L’image n’a pas pu être enregistrée. Réessayez avec un fichier valide.'));}catch(\InvalidArgumentException $e){$form->get('path')->addError(new FormError($e->getMessage()));}
  }
  return $this->render('admin/catalogue/media.html.twig',['owner'=>$owner,'technical'=>$technical,'ownerId'=>$id,'url'=>$catalogue->publicPath($owner),'nativeHero'=>$manager->nativeHero($owner),'media'=>$manager->media($technical,$owner),'form'=>$form->createView(),'isHub'=>$isHub,'backUrl'=>$owner instanceof SitePage?$this->generateUrl('admin_pages_edit',['id'=>$id]):null,'practices'=>[]]);
 }
}
