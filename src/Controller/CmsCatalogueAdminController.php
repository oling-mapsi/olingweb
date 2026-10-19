<?php

namespace App\Controller;

use App\Entity\{Practice, Services, Projet, SitePage, Metier};
use App\Service\{CmsCatalogue, GrowthPublishingService};
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotNull;

#[Route('/admin/catalogue')]
final class CmsCatalogueAdminController extends AbstractController
{
    #[Route('/visibility/{section}', name: 'admin_catalogue_visibility', requirements: ['section'=>'media|navigation|seo'], methods: ['GET'])]
    public function visibility(string $section, CmsCatalogue $catalogue, EntityManagerInterface $em): Response
    {
        $rows = $catalogue->all(); $media = [];
        foreach ($rows as $row) {
            foreach (['Image','Image1','Image2','ImageHero','HeroImage'] as $field) {
                if (!method_exists($row['object'],'get'.$field)) { continue; }
                $value=$row['object']->{'get'.$field}();
                if ($value && (str_starts_with($value,'/') && !str_starts_with($value,'//') || preg_match('~^https?://~',$value))) { $media[]=['path'=>$value,'title'=>$row['title'],'edit'=>$row['edit'],'field'=>$field]; }
            }
        }
        foreach ($em->getRepository(\App\Entity\CmsMedia::class)->findAll() as $item) {
            foreach ($rows as $row) { if ($row['object']===$item->getOwner() && $item->getPath()) { $media[]=['path'=>$item->getPath(),'title'=>$row['title'],'edit'=>$row['edit'],'field'=>$item->getSlot()];break; } }
        }
        return $this->render('admin/catalogue/visibility.html.twig',['section'=>$section,'rows'=>$rows,'media'=>$media,'baseline'=>$catalogue->baseline(),'practices'=>[]]);
    }

    #[Route('/service/{id}', name: 'admin_catalogue_service', methods: ['GET'])]
    public function service(Services $service): Response
    {
        return $this->redirectToRoute('admin_catalogue_offer',['technical'=>'service','id'=>$service->getId()]);
    }

    #[Route('/offre/{technical}/{id}', name: 'admin_catalogue_offer', requirements: ['technical'=>'service|page'], methods: ['GET'])]
    public function businessOffer(string $technical, int $id, \App\Service\CmsBusinessNavigation $business, \App\Service\CmsSiteNavigation $site): Response
    {
        $offer=$business->offer($technical.':'.$id);
        if (!$offer) { throw $this->createNotFoundException(); }
        $offer=$site->enrich($offer);$offer['pages']=array_map($site->enrich(...),$offer['pages']);$offer['localPages']=array_map($site->enrich(...),$offer['localPages']);
        return $this->render('admin/business/offer.html.twig',['offer'=>$offer,'auditDate'=>$business->auditDate(),'practices'=>[]]);
    }

    #[Route('/expertise/{id}', name: 'admin_catalogue_business_expertise', methods: ['GET'])]
    public function businessExpertise(int $id, \App\Service\CmsBusinessNavigation $business, \App\Service\CmsSiteNavigation $site): Response
    {
        $offer=$business->offer('page:'.$id);
        if (!$offer || $offer['kind']!=='expertise') { throw $this->createNotFoundException(); }
        $offer=$site->enrich($offer);$offer['pages']=array_map($site->enrich(...),$offer['pages']);$offer['localPages']=array_map($site->enrich(...),$offer['localPages']);
        return $this->render('admin/business/offer.html.twig',['offer'=>$offer,'auditDate'=>$business->auditDate(),'practices'=>[]]);
    }

    #[Route('/decisions', name: 'admin_catalogue_business_decisions', methods: ['GET'])]
    public function businessDecisions(\App\Service\CmsBusinessNavigation $business): Response
    {
        return $this->render('admin/business/decisions.html.twig',['rows'=>$business->decisions(),'auditDate'=>$business->auditDate(),'practices'=>[]]);
    }

    #[Route('/review', name: 'admin_catalogue_business_review', methods: ['GET'])]
    public function businessReview(\App\Service\CmsBusinessNavigation $business): Response
    {
        return $this->render('admin/business/review.html.twig',['proposals'=>$business->review(),'classifications'=>$business->classificationReview(),'auditDate'=>$business->auditDate(),'cards'=>$business->reviewCards(),'reviewEnabled'=>$business->reviewEnabled(),'practices'=>[]]);
    }

    #[Route('/review/decision', name: 'admin_catalogue_business_decision_save', methods: ['POST'])]
    public function saveBusinessDecision(Request $request, \App\Service\CmsBusinessNavigation $business): Response
    {
        if (!$this->isCsrfTokenValid('business-review', $request->request->get('_token'))) { throw $this->createAccessDeniedException('CSRF invalide'); }
        try { $business->recordReview((string)$request->request->get('key'),(string)$request->request->get('hash'),(string)$request->request->get('opinion'),$this->getUser()->getUserIdentifier()); }
        catch (\DomainException $e) { return new Response($e->getMessage(),409); }
        $this->addFlash('success','Avis enregistré en recette privée. Aucune classification ni association appliquée.');
        return $this->redirectToRoute('admin_catalogue_business_review');
    }

    private function businessCatalogue(Request $request, \App\Service\CmsBusinessNavigation $business, \App\Service\CmsSiteNavigation $site, ?string $kind=null): Response
    {
        $familyId=$request->query->getInt('practice')?:null;$q=trim((string)$request->query->get('q',''));$families=$business->families();
        if ($familyId && !isset($families[$familyId])) { throw $this->createNotFoundException(); }
        return $this->render('admin/business/catalogue.html.twig',['rows'=>array_map($site->enrich(...),$business->catalogue($familyId,$q,$kind)),'families'=>$families,'family'=>$familyId?$families[$familyId]:null,'q'=>$q,'kind'=>$kind,'presentationPages'=>$business->presentationPages($kind==='expertise'?'expertise':'service'),'auditDate'=>$business->auditDate(),'practices'=>[]]);
    }

    #[Route('/site-pages', name: 'admin_catalogue_site_pages', methods: ['GET'])]
    public function sitePages(Request $request, \App\Service\CmsAdminPresentation $presentation, \App\Service\CmsBusinessNavigation $business): Response
    {
        return $this->redirectToRoute('admin_site_section',['section'=>'societe']+$request->query->all());
    }
    #[Route('/all-content', name: 'admin_catalogue_all_content', methods: ['GET'])]
    public function allContent(\App\Service\CmsBusinessNavigation $business): Response
    {
        return $this->render('admin/business/all-content.html.twig',['rows'=>$business->contentInventory(),'practices'=>[]]);
    }
    #[Route('/settings', name: 'admin_catalogue_settings', methods: ['GET'])]
    public function settings(): Response { return $this->render('admin/catalogue/settings.html.twig',['practices'=>[]]); }
    private function workspace(string $mode, Request $request, \App\Service\CmsAdminPresentation $presentation, \App\Service\CmsBusinessNavigation $business): Response
    {
        $all=$presentation->rows(); $q=trim((string)$request->query->get('q','')); $practice=(string)$request->query->get('practice',''); $status=(string)$request->query->get('status',''); $category=(string)$request->query->get('category','');
        $organize=$request->query->get('view')==='pages'; $attached=[]; $families=$presentation->families();
        $selectedFamily=$families[(int)$practice]??null;
        foreach ($all as $row) { foreach ($row['associated'] as $page) { $attached[$page['key']]=true; } }
        $services=array_filter($all,fn($row)=>$row['technical']==='service');
        $unattached=array_filter($all,fn($row)=>($row['technical']==='service' && !isset($families[$row['practice']?->getId()])) || ($row['technical']==='page' && in_array($row['type'],['service','expertise'],true) && !isset($attached[$row['key']])));
        $unattachedKeys=array_fill_keys(array_column($unattached,'key'),true);
        $rows=array_values(array_filter($all,static function($row)use($mode,$organize,$unattachedKeys,$business,$category):bool {
            if($mode==='service')return $organize ? isset($unattachedKeys[$row['key']]) : $row['technical']==='service';
            return $mode==='site' ? ($business->placement($row)['section']==='Pages du site' || $category==='local' && $row['technical']==='page' && $row['category']==='local') : $row['type']===$mode;
        }));
        $rows=array_values(array_filter($rows,static function($row)use($q,$practice,$status,$category,$mode):bool {
            $text=$row['title'].' '.$row['description'].' '.$row['slug'].' '.$row['url'];
            foreach($row['associated'] as $page){$text.=' '.$page['title'].' '.$page['url'];}
            return ($q==='' || mb_stripos($text,$q)!==false) && ($practice==='' || ($practice==='none' ? $row['practice']===null : (string)$row['practice']?->getId()===$practice)) && ($status==='' || $row['status']===$status) && ($mode!=='site' || $category==='' || $row['category']===$category);
        }));
        $order=['home'=>0,'company'=>1,'contact'=>2,'local'=>3,'sector'=>4,'legal'=>5,'other'=>6];
        usort($rows,static fn($a,$b)=>($mode==='site' ? ($order[$a['category']]<=>$order[$b['category']]) : 0) ?: strnatcasecmp($a['title'],$b['title']));
        $count=count($rows);$pages=max(1,(int)ceil($count/20));$page=max(1,min((int)$request->query->get('page',1),$pages));
        return $this->render('admin/catalogue/workspace.html.twig',['presentationPages'=>$business->presentationPages($mode),'selectedFamily'=>$selectedFamily,'mode'=>$mode,'title'=>['service'=>'Prestations & expertises','site'=>'Pages du site','resource'=>'Articles & Growth','project'=>'Projets & références','sector'=>'Secteurs','expertise'=>'Expertises'][$mode]??$mode,'rows'=>array_slice($rows,($page-1)*20,20),'count'=>$count,'page'=>$page,'pages'=>$pages,'organize'=>$organize,'serviceCount'=>count($services),'unattachedCount'=>count($unattached),'filters'=>compact('q','practice','status','category'),'practiceChoices'=>array_column(array_values(array_filter($all,fn($r)=>$r['technical']==='practice')),'object'),'practices'=>[]]);
    }

    #[Route('/quality', name: 'admin_catalogue_quality', methods: ['GET'])]
    public function quality(CmsCatalogue $catalogue): Response
    {
        return $this->render('admin/catalogue/quality.html.twig', ['rows'=>array_filter($catalogue->all(),fn($r)=>$r['anomalies'] !== []),'practices'=>[]]);
    }

    #[Route('/practice/{id}', name: 'admin_catalogue_practice', methods: ['GET'])]
    public function practice(Practice $practice): Response
    {
        return $this->redirectToRoute('admin_catalogue_index',['type'=>'service','practice'=>$practice->getId()]);
    }

    #[Route('/relations/{technical}/{id}', name: 'admin_catalogue_relations', requirements: ['technical'=>'service|page|project'], methods: ['GET','POST'])]
    public function relations(string $technical, int $id, Request $request, EntityManagerInterface $em, CmsCatalogue $catalogue, GrowthPublishingService $growth, LoggerInterface $logger): Response
    {
        $class = ['service'=>Services::class,'page'=>SitePage::class,'project'=>Projet::class][$technical];
        $object = $em->find($class,$id);
        if (!$object) { throw $this->createNotFoundException(); }
        $type = $object instanceof SitePage ? CmsCatalogue::pageType($object) : ($object instanceof Projet ? 'project' : 'service');
        $builder = $this->createFormBuilder()
            ->add('practice',EntityType::class,['class'=>Practice::class,'choice_label'=>'designation','label'=>'Practice principale (classification)','required'=>in_array($type,['service','expertise','resource'],true),'constraints'=>in_array($type,['service','expertise','resource'],true) ? [new NotNull()] : [],'placeholder'=>'Non classé','data'=>$object->getCatalogPractice()]);
        $expertises = array_map(fn($r)=>$r['object'],array_values(array_filter($catalogue->all(),fn($r)=>$r['type']==='expertise' && $r['object'] instanceof SitePage && $r['object'] !== $object)));
        if ($object instanceof Services) {
            $builder->add('associatedPages',EntityType::class,['class'=>SitePage::class,'multiple'=>true,'required'=>false,'label'=>'Pages publiques associées (stockage et rendu autonomes)','choice_label'=>fn(SitePage $page)=>'SitePage #'.$page->getId().' · '.($catalogue->publicPath($page) ?? $page->getSlug()).' · '.$page->getTitle(),'data'=>$object->getCatalogPages()->toArray()]);
            $builder->add('secondaryPractices',EntityType::class,['class'=>Practice::class,'choice_label'=>'designation','label'=>'Practices secondaires','multiple'=>true,'required'=>false,'data'=>$object->getSecondaryPractices()->toArray()]);
        } else {
            $builder->add('services',EntityType::class,['class'=>Services::class,'choice_label'=>'designation','multiple'=>true,'required'=>false,'label'=>'Services associés','data'=>($object instanceof Projet ? $object->getServices() : $object->getCatalogServices())->toArray()]);
            $builder->add('expertises',EntityType::class,['class'=>SitePage::class,'choices'=>$expertises,'choice_label'=>'title','multiple'=>true,'required'=>false,'label'=>'Expertises associées','data'=>$object->getCatalogExpertises()->toArray()]);
            if ($object instanceof Projet) { $builder->add('sectors',EntityType::class,['class'=>Metier::class,'choice_label'=>'designation','multiple'=>true,'required'=>false,'label'=>'Secteurs secondaires','data'=>$object->getSecondarySectors()->toArray()]); }
            if ($object instanceof SitePage) {
                $locked = str_starts_with((string)$object->getSlug(),'ressource-') || str_starts_with((string)$object->getSlug(),'expertise-') || str_starts_with((string)$object->getSlug(),'secteur-');
                $builder->add('type',ChoiceType::class,['choices'=>array_flip(array_intersect_key(CmsCatalogue::TYPES,array_flip(['service','expertise','resource','sector','institutional','legal']))),'data'=>$type,'label'=>'Type métier principal','disabled'=>$locked]);
            }
        }
        $form = $builder->getForm(); $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($object instanceof SitePage && $object->getExternalId()) { $growth->assertAdminEditAllowed($object); }
            $before = $this->relationSnapshot($object);
            $object->setCatalogPractice($form->get('practice')->getData());
            if ($object instanceof Services) {
                foreach ($object->getCatalogPages()->toArray() as $page) { $object->removeCatalogPage($page); }
                foreach ($form->get('associatedPages')->getData() as $page) { $object->addCatalogPage($page); }
                foreach ($object->getSecondaryPractices()->toArray() as $value) { $object->removeSecondaryPractices($value); }
                foreach ($form->get('secondaryPractices')->getData() as $value) { if ($value !== $object->getCatalogPractice()) { $object->addSecondaryPractices($value); } }
            } else {
                $collection = $object instanceof Projet ? $object->getServices() : $object->getCatalogServices();
                foreach ($collection->toArray() as $value) { $object instanceof Projet ? $object->removeService($value) : $object->removeCatalogServices($value); }
                foreach ($form->get('services')->getData() as $value) { $object instanceof Projet ? $object->addService($value) : $object->addCatalogServices($value); }
                foreach ($object->getCatalogExpertises()->toArray() as $value) { $object->removeCatalogExpertises($value); }
                foreach ($form->get('expertises')->getData() as $value) { $object->addCatalogExpertises($value); }
                if ($object instanceof Projet) {
                    foreach ($object->getSecondarySectors()->toArray() as $value) { $object->removeSecondarySectors($value); }
                    foreach ($form->get('sectors')->getData() as $value) { $object->addSecondarySectors($value); }
                } else { $object->setCatalogType($form->get('type')->getData()); }
            }
            $em->flush();
            $logger->info('cms_catalogue_relations_updated',['object'=>$technical.':'.$id,'actor'=>$this->getUser()?->getUserIdentifier(),'before'=>$before,'after'=>$this->relationSnapshot($object)]);
            $this->addFlash('success','Classification enregistrée.');
            if ($object instanceof Services) { return $this->redirectToRoute('admin_catalogue_service',['id'=>$object->getId()]); }
            return $this->redirectToRoute('admin_catalogue_index',['type'=>$type]);
        }
        return $this->render('admin/catalogue/relations.html.twig',['form'=>$form->createView(),'object'=>$object,'technical'=>$technical,'publicUrl'=>$catalogue->publicPath($object),'practices'=>[],'relatedRows'=>array_filter($catalogue->all(),static function($row) use($object): bool {
            if (!$object instanceof Services) { return false; }
            return ($row['object'] instanceof SitePage && $row['object']->getCatalogServices()->contains($object)) || ($row['object'] instanceof Projet && $row['object']->getServices()->contains($object));
        })]);
    }

    private function relationSnapshot(object $object): array
    {
        $state = ['practice'=>$object->getCatalogPractice()?->getId()];
        foreach (['CatalogPages','SecondaryPractices','Services','CatalogServices','CatalogExpertises','SecondarySectors'] as $name) { if (method_exists($object,'get'.$name)) { $state[$name] = array_map(fn($v)=>$v->getId(),$object->{'get'.$name}()->toArray()); } }
        if ($object instanceof SitePage) { $state['type']=$object->getCatalogType(); }
        return $state;
    }

    #[Route('/{type}', name: 'admin_catalogue_index', requirements: ['type'=>'practice|service|expertise|project|resource|sector|institutional|legal'], methods: ['GET'])]
    public function index(string $type, Request $request, CmsCatalogue $catalogue, EntityManagerInterface $em, \App\Service\CmsAdminPresentation $presentation, \App\Service\CmsBusinessNavigation $business, \App\Service\CmsSiteNavigation $site): Response
    {
        $section=['project'=>'projets','resource'=>'ressources','sector'=>'secteurs','institutional'=>'societe','legal'=>'legal','expertise'=>'expertises','practice'=>'expertises'][$type]??null;
        if($section || $type==='service' && !$request->query->get('practice'))return $this->redirectToRoute('admin_site_section',['section'=>$section??'expertises']+$request->query->all());
        if ($type==='service' || $type==='expertise') { return $this->businessCatalogue($request,$business,$site,$type==='expertise'?'expertise':null); }
        if (in_array($type,['service','project','resource','expertise','sector'],true)) { return $this->workspace($type,$request,$presentation,$business); }
        if ($type==='institutional') { return $this->redirectToRoute('admin_site_section',['section'=>'societe']+$request->query->all()); }
        $q = trim((string)$request->query->get('q','')); $practice = (string)$request->query->get('practice',''); $status = (string)$request->query->get('status','');
        $navigation = (string)$request->query->get('navigation',''); $hub = (string)$request->query->get('hub',''); $indexation = (string)$request->query->get('indexation',''); $technical = (string)$request->query->get('technical','');
        $rows = array_values(array_filter($catalogue->all(),static function($row) use($type,$q,$practice,$status,$hub,$technical,$indexation,$navigation): bool {
            return $row['type']===$type && ($q==='' || mb_stripos($row['title'].' '.$row['slug'].' '.$row['url'],$q)!==false)
                && ($practice==='' || ($practice==='none' ? $row['practice']===null : (string)$row['practice']?->getId()===$practice))
                && ($status==='' || $row['status']===$status) && ($hub==='' || $row['hub']===$hub) && ($technical==='' || $row['technical']===$technical) && ($indexation==='' || $row['indexation']===$indexation) && ($navigation==='' || $row['navigation']===$navigation);
        }));
        $sort = in_array($request->query->get('sort'),['title','id','status'],true) ? $request->query->get('sort') : 'title';
        $direction = $request->query->get('direction')==='desc' ? -1 : 1;
        usort($rows,fn($a,$b)=>$direction*($sort==='id' ? $a['id']<=>$b['id'] : strnatcasecmp((string)$a[$sort],(string)$b[$sort])));
        $count=count($rows); $page=max(1,min((int)$request->query->get('page',1),max(1,(int)ceil($count/30))));
        return $this->render('admin/catalogue/index.html.twig',['type'=>$type,'title'=>CmsCatalogue::TYPES[$type],'rows'=>array_slice($rows,($page-1)*30,30),'count'=>$count,'page'=>$page,'pages'=>max(1,(int)ceil($count/30)),'filters'=>compact('q','practice','status','hub','technical','sort','indexation','navigation'),'practiceChoices'=>$em->getRepository(Practice::class)->findAll(),'practices'=>[]]);
    }
}
