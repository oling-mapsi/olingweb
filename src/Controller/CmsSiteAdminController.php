<?php
namespace App\Controller;
use App\Service\{CmsSiteNavigation,CmsBusinessNavigation};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request,Response};
use Symfony\Component\Routing\Annotation\Route;

final class CmsSiteAdminController extends AbstractController
{
    #[Route('/admin/site/rechercher',name:'admin_site_search',methods:['GET'],priority:20)]
    public function search(Request $request,CmsSiteNavigation $site):Response
    {
        $q=trim((string)$request->query->get('q',''));return $this->render('admin/site/search.html.twig',['rows'=>$q!==''?$site->search($q):[],'q'=>$q,'practices'=>[]]);
    }
    #[Route('/admin/site/diagnostic',name:'admin_site_diagnostic',methods:['GET'],priority:30)]
    public function diagnostic(CmsSiteNavigation $site):Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('admin/site/diagnostic.html.twig',['rows'=>$site->diagnostics(),'records'=>$site->visibilityRecords(),'practices'=>[]]);
    }
    #[Route('/admin/site/visibilite',name:'admin_site_refresh_visibility',methods:['POST'],priority:30)]
    public function refreshVisibility(Request $request,\App\Service\CmsHttpVisibility $visibility):Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if(!$this->isCsrfTokenValid('cms_visibility_refresh',$request->request->get('_token')))throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        try{$result=$visibility->refresh((string)$request->request->get('url'));$this->addFlash($result['error']?'warning':'success',$result['error']??('Contrôle réel terminé : HTTP '.$result['http'].' · '.$result['checked_at'].' · '.$result['url']));}
        catch(\InvalidArgumentException $e){throw $this->createNotFoundException($e->getMessage());}
        return $this->redirectToRoute('admin_site_diagnostic');
    }
    #[Route('/admin/site/{section}',name:'admin_site_section',requirements:['section'=>'expertises|services|secteurs|projets|ressources|societe|medias|legal'],methods:['GET'])]
    public function section(string $section,Request $request,CmsSiteNavigation $site,CmsBusinessNavigation $business):Response
    {
        if($section==='services')return $this->redirectToRoute('admin_site_section',['section'=>'expertises']+$request->query->all());
        if($section==='expertises' && $request->query->get('practice'))return $this->redirectToRoute('admin_catalogue_index',['type'=>'service']+$request->query->all());
        if($section==='medias')return $this->redirectToRoute('admin_catalogue_visibility',['section'=>'media']);
        $rows=$site->section($section,(string)$request->query->get('q',''));
        $filters=['status'=>(string)$request->query->get('status',''),'practice'=>(string)$request->query->get('practice','')];
        $rows=array_values(array_filter($rows,static fn($row)=>($filters['status']==='' || $row['status']===$filters['status']) && ($filters['practice']==='' || ($filters['practice']==='none' ? ($row['practice']??null)===null : (string)($row['practice']??null)?->getId()===$filters['practice']))));
        if($section==='expertises'){
            $families=$business->families();$groups=[];$unorganized=[];$hubs=$site->hubs('expertises');usort($hubs,fn($a,$b)=>strcmp($a['slug'],$b['slug']));
            foreach($rows as $row){$familyId=($row['family']??null)?->getId();if(isset($families[$familyId]))$groups[$familyId][]=$row;else $unorganized[]=$row;}
            return $this->render('admin/site/expertises.html.twig',['rows'=>$rows,'families'=>$families,'groups'=>$groups,'unorganized'=>$unorganized,'hubs'=>$hubs,'q'=>(string)$request->query->get('q',''),'practices'=>[]]);
        }
        return $this->render('admin/site/section.html.twig',['section'=>$section,'title'=>$site->sections()[$section],'rows'=>$rows,'filters'=>$filters,'hubs'=>$site->hubs($section),'families'=>$business->families(),'teamPresentation'=>$site->teamPresentation(),'q'=>(string)$request->query->get('q',''),'practices'=>[]]);
    }
}
