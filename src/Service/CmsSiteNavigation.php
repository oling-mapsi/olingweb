<?php
namespace App\Service;

use App\Entity\{SitePage,Team};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/** Administrative mirror of existing public owners. No public rendering or writes. */
final class CmsSiteNavigation
{
    private array $observations;
    private ?array $rows=null;
    private ?array $localPages=null;
    public const SECTIONS=['expertises'=>'Nos expertises','secteurs'=>'Nos secteurs','projets'=>'Projets et références','ressources'=>'Articles et ressources / Growth','societe'=>'Notre société','medias'=>'Médiathèque','legal'=>'Pages légales'];
    private const HUBS=['expertises-index'=>['expertises','Expertises','/expertises'],'services'=>['expertises','Services et offres','/services'],'secteurs-index'=>['secteurs','Secteurs','/secteurs'],'metiers'=>['secteurs','Métiers','/a-propos/metiers'],'projets'=>['projets','Projets et références','/projets'],'ressources'=>['ressources','Ressources','/ressources']];
    public function __construct(private CmsAdminPresentation $presentation,private CmsBusinessNavigation $business,private EntityManagerInterface $em,private LocalizedContentResolver $localized,KernelInterface $kernel, private \App\Service\I18n\LocalizedUrlGenerator $urls, private \Symfony\Component\Routing\RouterInterface $router)
    {
        $file=$kernel->getProjectDir().'/var/cms-public-observations.json';$this->observations=is_file($file)?json_decode(file_get_contents($file),true,512,JSON_THROW_ON_ERROR):['records'=>[],'captured_at'=>null];
    }
    public function sections(): array { return self::SECTIONS; }
    public function enrich(array $row): array
    {
        $url=$row['url'];$path=$url?parse_url($url,PHP_URL_PATH):null;$record=$path?($this->observations['records']['https://oling.fr'.$path]??null):null;$http=$record['http']??null;
        $visibility=!$url?'Sans URL publique':($http===200?'En ligne':($http>=400?'Non accessible':'À vérifier'));
        $editorial=['published'=>'Publié','draft'=>'Brouillon','unpublished'=>'Non publié','legacy'=>'Historique non renseigné'][$row['status']]??$row['status'];
        $robots=strtolower(($record['robots']??'').' '.($record['x_robots']??''));$indexability=$http!==200?'À vérifier':(str_contains($robots,'noindex')?'Interdite par robots':'Autorisée par robots');
        $row['displayTitle']=$row['displayTitle']??$this->business->displayTitle($row);$row['publicUrl']=$url?'https://oling.fr'.$url:null;$row['visibility']=$visibility;$row['http']=$http;$row['editorialLabel']=$editorial;$row['indexability']=$indexability;$row['observedAt']=$record['checked_at']??$this->observations['captured_at'];
        preg_match('~^/(en|es)(?:/|$)~',(string)$path,$language);$row['locale']=$language[1]??'fr';
        $row['preview']=$row['object'] instanceof SitePage && $row['object']->getTranslation($row['locale'])?'/admin/pages/'.$row['id'].'/preview/'.$row['locale']:null;
        $row['canView']=$url && $http===200;$row['diagnosticOnly']=false;
        if($row['technical']==='page' && $row['slug']==='home'){$row['edit']='/admin/home';$row['displayTitle']='Accueil';$row['preview']=null;}
        $row['ownerLabel']=match($row['technical']) {'service'=>'Prestation historique','page'=>match($row['type']){'expertise'=>'Page d’expertise','resource'=>'Article','sector'=>'Page sectorielle',default=>'Page éditoriale'},'practice'=>'Practice','sector'=>'Fiche Metier historique — carte du catalogue','project'=>'Référence projet','legal'=>'Page légale','team'=>'Fiche collaborateur',default=>'Contenu'};
        if($row['technical']==='page' && $row['slug']==='home')$row['ownerLabel']='Page d’accueil';
        $row['hub']=isset(self::HUBS[$row['slug']]) && $row['technical']==='page';$row['domainUrl']=null;
        if($row['technical']==='service')$row['domainUrl']='/admin/catalogue/offre/service/'.$row['id'];
        elseif($row['technical']==='page' && $row['type']==='service')$row['domainUrl']='/admin/catalogue/offre/page/'.$row['id'];
        elseif($row['type']==='expertise')$row['domainUrl']='/admin/catalogue/expertise/'.$row['id'];
        $row['references']=[];
        if(is_object($row['object']) && method_exists($row['object'],'getProjets'))foreach($row['object']->getProjets() as $project)$row['references'][]=['title'=>$project->getDesignation(),'edit'=>'/admin/projets/'.$project->getId().'/edit'];
        $this->localPages??=array_column($this->business->localPages(),null,'key');
        $row['localPageCount']=count(array_filter($this->localPages,fn($local)=>$local['localOwner']===$row['key']));
        if(isset($this->localPages[$row['key']])){$row['ownerLabel']='Page complémentaire SEO/GEO · AMOA SI';$row['domainUrl']='/admin/catalogue/offre/'.str_replace(':','/',$this->localPages[$row['key']]['localOwner']).'#local-seo';}
        $row['associatedPages']=[];
        foreach($row['pages']??[] as $page)if($page['key']!==$row['key'])$row['associatedPages'][]=$this->enrich($page);
        $row['publicOwner']=null;
        return $row;
    }
    public function rows(): array
    {
        if($this->rows!==null)return $this->rows;$out=[];
        foreach($this->presentation->rows() as $row){$row=$this->enrich($row);$row['section']=$this->sectionFor($row);$out[$row['key']]=$row;}
        // A specialized sector page owns its public URL; the historical sector remains independently editable.
        foreach($out as &$row){if($row['technical']!=='sector')continue;foreach($out as $page)if($page['technical']==='page' && $page['type']==='sector' && $page['url']===$row['url'])$row['publicOwner']=['title'=>$page['title'],'edit'=>$page['edit']];}unset($row);
        foreach($this->teamRows() as $row)$out[$row['key']]=$row;
        return $this->rows=$out;
    }
    private function sectionFor(array $row): string
    {
        if($row['technical']==='page' && $row['category']==='local')return 'expertises';
        if($row['technical']==='page' && isset(self::HUBS[$row['slug']]))return self::HUBS[$row['slug']][0];
        return match($row['type']){'practice','expertise','service'=>'expertises','sector'=>'secteurs','project'=>'projets','resource'=>'ressources','legal'=>'legal',default=>'societe'};
    }
    public function teamRows(): array
    {
        $repo=$this->em->getRepository(Team::class);$published=array_map(fn($t)=>$t->getId(),$repo->findPublishedOrderedForLocale('fr'));$out=[];
        foreach($repo->findBy([],['displayOrder'=>'ASC','id'=>'ASC']) as $team){
            $view=$this->localized->getTeamView($team,'fr');$profile=$view->getPublicProfile()??[];$visible=in_array($team->getId(),$published,true) && is_array($profile) && $profile!==[];
            $baseTitle=$team->getNoncomplet();$title=$profile['displayName']??$baseTitle;$photo=$profile['photo']??$team->getPhoto();$slug=$profile['slug']??'';
            $row=['key'=>'team:'.$team->getId(),'id'=>$team->getId(),'object'=>$team,'technical'=>'team','type'=>'team','title'=>$title,'slug'=>$slug,'url'=>$visible?'/a-propos/team'.($slug?'#'.$slug:''):null,'thumbnail'=>$photo?(preg_match('~^https?://~i',$photo)?$photo:'/'.ltrim($photo,'/')):null,'description'=>(string)($view->getTitre()?:($profile['titre']??'')),'status'=>$visible?'published':'unpublished','edit'=>'/admin/teams/'.$team->getId().'/edit','section'=>'societe'];
            $row=$this->enrich($row);$row['publicEligibility']=$visible;$row['displayOrder']=$team->getDisplayOrder();$row['bio']=(string)($view->getShortcv()?:($profile['shortcv']??''));$row['profileOverrides']=['name'=>isset($profile['displayName']) && $profile['displayName']!==$baseTitle,'photo'=>isset($profile['photo']) && $profile['photo']!==$team->getPhoto()];
            $row['services']=array_map(fn($service)=>['title'=>$service->getDesignation(),'edit'=>'/admin/services/'.$service->getId().'/edit'],$team->getServices()->toArray());
            $row['teamEditorName']=$baseTitle;$row['preview']=null;$out[]=$row;
        }
        return $out;
    }
    public function section(string $section,string $q=''): array
    {
        $rows=array_values(array_filter($this->rows(),fn($row)=>($row['section']===$section || $section==='societe' && ($row['category']??'')==='local') && !$row['hub']));
        if(in_array($section,['expertises','services'],true)){
            $section='expertises';$rows=[];foreach($this->business->catalogue() as $row)if(($row['category']??'')!=='local')$rows[]=$this->enrich($row)+['section'=>'expertises'];
        }
        foreach($this->publicRows() as $row)if($row['technical']==='route' && !$row['diagnosticOnly'] && $row['section']===$section)$rows[]=$row;
        return $this->filter($rows,$q);
    }
    public function filter(array $rows,string $q): array
    {
        $q=rawurldecode(trim($q));if($q==='')return array_values($rows);
        if(in_array(parse_url($q,PHP_URL_HOST),['oling.fr','www.oling.fr'],true)){$path=parse_url($q,PHP_URL_PATH)?:'/';return array_values(array_filter($rows,static fn($r)=>in_array($path,array_map(static fn($url)=>rawurldecode(parse_url($url??'',PHP_URL_PATH)??''),array_merge([$r['publicUrl']??null],array_column($r['associatedPages']??[],'publicUrl'))),true)));}
return array_values(array_filter($rows,static fn($r)=>mb_stripos($r['title'].' '.($r['displayTitle']??'').' '.$r['slug'].' '.($r['publicUrl']??'').' '.implode(' ',array_map(static fn($p)=>$p['title'].' '.($p['publicUrl']??''),$r['associatedPages']??[])),$q)!==false));
    }
    public function hubs(string $section): array
    {
        $rows=[];foreach($this->rows() as $row)if($row['hub'] && $row['section']===$section){$row['displayTitle']=self::HUBS[$row['slug']][1];$row['generated']=$this->hubLists($row['slug']);$row['fields']=$this->hubFields($row['slug']);$rows[]=$row;}return $rows;
    }
    private function hubLists(string $slug): string
    {
        return match($slug){
            'expertises-index'=>'Les neuf fiches d’expertise viennent des pages d’expertise enregistrées. Les trois regroupements éditoriaux et leurs liens sont définis dans la présentation du site, distincts des quatre Practices historiques.',
            'services'=>'Les liens de prestations des trois regroupements et la mise en avant MAPSI sont définis dans la présentation du site et ses traductions. Modifier une prestation change sa fiche, pas automatiquement le libellé de tous les liens de cette page.',
            'secteurs-index','metiers'=>'Les cartes des onze secteurs viennent des secteurs enregistrés. Les trois pages sectorielles spécialisées ont leur propre éditeur. Les deux pages de présentation Secteurs et Métiers restent distinctes.',
            'projets'=>'Les références et leurs images viennent des projets enregistrés. La sélection publique privilégie les projets importés lorsqu’ils existent ; la mise en avant et la pagination sont générées.',
            'ressources'=>'Les articles publiés viennent des pages Ressources. Les brouillons et préfixes de test sont exclus du hub ; Growth conserve son workflow.',default=>''};
    }
    public function hubFields(string $slug): array
    {
        $direct=['Titre hero → H1 public','Intro hero → introduction publique','Badge (hero) → surtitre public','SEO/GEO : Titre (balise <title>) et Meta description'];
        $image='L’image principale peut être stockée dans la fiche, mais ces trois hubs ne la transmettent pas au bandeau public : la modifier ne change pas leur image affichée.';
        return match($slug){
            'expertises-index'=>['direct'=>$direct,'structured'=>['Configuration structurée (JSON du contenu) : highlights, section (kicker, title, text), primaryCta, secondaryCta et cta. Ces clés alimentent les bandeaux et blocs correspondants.'],'generated'=>['Les neuf fiches et leurs textes propres se modifient dans chaque page d’expertise.','Les quatre Practices organisent le catalogue administratif ; elles ne définissent pas les trois regroupements éditoriaux publics.'],'template'=>['Les trois regroupements, leurs titres, descriptions, liens et leur ordre ; les textes « Expertises transverses », les cartes complémentaires et la mise en avant MAPSI sont définis dans le gabarit et ses textes traduits.',$image]],
            'services'=>['direct'=>$direct,'structured'=>['Contenu structuré corePage : highlights, primaryCta, secondaryCta et cta. Les champs titre, introduction, badge et SEO restent prioritaires.'],'generated'=>['Chaque prestation a son propre éditeur. Il ne commande pas les libellés et descriptions des liens affichés dans ce hub.'],'template'=>['Les trois regroupements : titres, descriptions, liens, ordre ; les blocs de lecture, la mise en avant MAPSI et leurs traductions sont définis dans services-index et ses traductions.',$image]],
            'secteurs-index'=>['direct'=>$direct,'structured'=>['Configuration structurée (JSON du contenu) : highlights, section (kicker, title, text) et cta. Les boutons du bandeau vers Contact et Expertises sont fixés dans le gabarit.'],'generated'=>['Les onze cartes viennent des fiches Metier (nom, résumé et images) ; leurs liens sont calculés par le site.','Les trois pages sectorielles spécialisées ont leurs titres, contenus et éditeurs SitePage distincts.'],'template'=>['Titres et textes « Angles sectoriels », libellés des boutons, organisation et sélection des liens : gabarit et textes traduits.',$image]],
            default=>['direct'=>['Titre et introduction ; métadonnées SEO selon le rendu existant.'],'structured'=>[],'generated'=>[$this->hubLists($slug)],'template'=>[]]};
    }
    public function teamPresentation(): ?array { foreach($this->rows() as $row)if($row['technical']==='page' && $row['slug']==='team')return $row;return null; }
    /** Resolve observed localized URLs through their declared French alternate, never create routes. */
    public function publicRows(): array
    {
        $owners=[];
        foreach($this->rows() as $row){if(!$row['publicUrl'] || $row['technical']==='team')continue;$path=parse_url($row['publicUrl'],PHP_URL_PATH);if(!isset($owners[$path]) || $row['technical']==='page')$owners[$path]=$row;}
        // Resolve existing localized URLs through the same generator and fixed route siblings as the public site.
        foreach($this->rows() as $row)if($row['object'] instanceof SitePage)foreach(['en','es'] as $locale){$localizedPath=$this->urls->sitePagePath($row['object'],$locale);if($localizedPath)$owners[$localizedPath]=$row;}
        $routes=$this->router->getRouteCollection();
        foreach($routes as $name=>$route){$path=$route->getPath();if(!isset($owners[$path]) || str_contains($path,'{') || preg_match('/_(en|es)$/',$name))continue;foreach(['en','es'] as $locale){$alternate=$routes->get($name.'_'.$locale);if($alternate && !str_contains($alternate->getPath(),'{'))$owners[$alternate->getPath()]=$owners[$path];}}
        $out=[];
        foreach($this->observations['records'] as $url=>$record){
            $path=parse_url($url,PHP_URL_PATH);$fr=$path;
            foreach($record['hreflang']??[] as $alternate)if(($alternate['hreflang']??'')==='fr')$fr=parse_url($alternate['href'],PHP_URL_PATH);
            if(isset($owners[$path]) || isset($owners[$fr])){$row=$owners[$path]??$owners[$fr];$row['url']=$path;$row=$this->enrich($row);$row['key']='public:'.$path;$row['publicOwner']=null;}
            else {
                $section=$this->routeSection($path);
                $title=is_array($record['title']??null)?implode(' ',$record['title']):($record['title']??$path);
                $row=['key'=>'public:'.$path,'id'=>0,'object'=>null,'technical'=>'route','type'=>'institutional','title'=>$title?:$path,'slug'=>ltrim($path,'/'),'url'=>$path,'thumbnail'=>null,'description'=>'Rendu porté par le site, sans fiche éditoriale autonome identifiée.','status'=>'legacy','edit'=>null,'section'=>$section];$row=$this->enrich($row);$row['ownerLabel']='Route et gabarit publics — sans fiche autonome · '.(self::SECTIONS[$section]??'Rôle à vérifier');$row['diagnosticOnly']=($row['http']??0)>=400 || $section==='diagnostic';
            }
            $out[$url]=$row;
        }
        return $out;
    }
    private function routeSection(string $path): string
    {
        if(preg_match('~^/(?:en/resources|es/recursos|ressources)(?:/|$)~',$path))return 'ressources';
        if(preg_match('~^/(?:mapsi|en/services|es/servicios|en/erp-software|es/software-erp)(?:/|$)~',$path) || $path==='/pca-pra-continuite-activite')return 'expertises';
        if(preg_match('~^/(?:expertises|en/areas-of-expertise|es/areas-de-especializacion)(?:/|$)~',$path))return 'expertises';
        if(preg_match('~^/(?:secteurs|en/industries|es/sectores)(?:/|$)~',$path))return 'secteurs';
        if(preg_match('~^/(?:projets|en/projects|es/proyectos)(?:/|$)~',$path))return 'projets';
        return 'diagnostic';
    }
    public function diagnostics(): array { return array_values(array_filter($this->publicRows(),fn($r)=>$r['diagnosticOnly'] || $r['section']==='diagnostic')); }
    public function visibilityRecords(): array { return $this->publicRows(); }
    public function search(string $q): array
    {
        if(trim($q)==='')return [];$out=$this->filter($this->rows(),$q);$urls=array_fill_keys(array_filter(array_column($out,'publicUrl')),true);
        foreach($this->filter($this->publicRows(),$q) as $row)if(!$row['diagnosticOnly'] && !isset($urls[$row['publicUrl']])){$out[]=$row;$urls[$row['publicUrl']]=true;}
        return $out;
    }
    public function correspondence(): array
    {
        $rows=$this->publicRows();foreach($this->rows() as $row)if($row['publicUrl'] && !isset($rows[$row['publicUrl']]))$rows[$row['publicUrl']]=$row;$out=[];
        foreach($rows as $row)$out[]=['page'=>$row['publicUrl'],'section'=>($row['diagnosticOnly']?'Diagnostic technique · ':'').(self::SECTIONS[$row['section']]??'À vérifier'),'source'=>$row['ownerLabel'].($row['id']?' #'.$row['id']:''),'editor'=>$row['edit'],'preview'=>$row['preview'],'visibility'=>$row['visibility'],'editorial'=>$row['editorialLabel'],'indexability'=>$row['indexability'],'locale'=>$row['locale'],'diagnostic'=>$row['diagnosticOnly'],'observedAt'=>$row['observedAt']];return $out;
    }
}
