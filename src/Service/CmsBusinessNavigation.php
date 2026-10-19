<?php
namespace App\Service;

use App\Entity\{Services, SitePage};
use Symfony\Component\HttpKernel\KernelInterface;

/** Read model only. Audit suggestions never mutate entities or public rendering. */
final class CmsBusinessNavigation
{
    private array $plan;
    private array $display;
    private string $reviewFile;
    private ?array $items = null;
    public function __construct(private CmsAdminPresentation $presentation, KernelInterface $kernel)
    {
        $this->display=json_decode(file_get_contents($kernel->getProjectDir().'/config/cms_business_display.json'),true,512,JSON_THROW_ON_ERROR);
        $this->reviewFile=$kernel->getProjectDir().'/var/cms-business-review/decisions.jsonl';
        $this->plan=json_decode(file_get_contents($kernel->getProjectDir().'/config/cms_business_review.json'),true,512,JSON_THROW_ON_ERROR);
    }
    public function items(): array
    {
        if ($this->items !== null) { return $this->items; }
        $families=$this->presentation->families();$items=[];
        foreach ($this->presentation->rows() as $row) {
            if ($row['technical']!=='service' && !($row['technical']==='page' && in_array($row['type'],['service','expertise'],true))) { continue; }
            $proposal=$row['technical']==='page' ? ($this->plan['classificationProposals'][(string)$row['id']]??null) : null;
            if ($proposal && $proposal['slug']!==$row['slug']) { $proposal=null; }
            $stored=$row['object']->getCatalogPractice();$family=$stored??($families[$proposal['practice']??0]['object']??null);
            $observation=$this->plan['observations'][$row['key']]??null;
            $fresh=$observation && $observation['slug']===$row['slug'] && $row['url'] && parse_url((string)$observation['url'],PHP_URL_PATH)===$row['url'];
            $row['family']=$family;$row['familyProposed']=!$stored && $family!==null;$row['familyLabel']=$family?->getDesignation()??'Famille à confirmer';
            $row['kind']=$row['technical']==='service'?'service':($row['type']==='expertise'?'expertise':'page');
            $row['identityIssue']=$row['technical']==='service' && $row['id']===8 && $row['slug']==='finance';
            $row['decisionReasons']=[];
            if (!$stored) { $row['decisionReasons'][]=$row['technical']==='service'?'Famille à confirmer — aucune Practice historique':'Famille proposée à confirmer'; }
            if ($row['technical']==='service' && !$row['url']) { $row['decisionReasons'][]='Service sans route publique'; }
            if ($row['identityIssue']) { $row['decisionReasons'][]='Identité commerciale à clarifier : titre conformité, corps finance / BI'; }
            $row['http']=$fresh?$observation['http']:null;$row['inMenu']=$fresh?(bool)$observation['menu']:null;
            $row['navigationLabel']=$row['inMenu']===true?'Présent':($row['inMenu']===false?'Absent':'À vérifier');
            $row['linkedServices']=[];
            if ($row['object'] instanceof SitePage) {
                $data=$row['object']->getTranslation('fr')?->getStructuredData()??[];
                foreach ($data['expertisePage']['linkedServices']??[] as $link) {
                    foreach ($this->presentation->rows() as $candidate) {
                        if ($candidate['technical']==='service' && $candidate['slug']===($link['service']??null) && $candidate['object']->getPractice()?->getSlug()===($link['practice']??null)) { $row['linkedServices'][$candidate['key']]=$candidate; }
                    }
                }
            }
            $row['displayTitle']=$this->displayTitle($row);
            $row['proposal']=$proposal;$items[$row['key']]=$row;
        }
        return $this->items=$items;
    }
    public function families(): array
    {
        $families=$this->presentation->families();
        foreach ($families as &$family) { $family['pageCount']=0;$family['expertiseCount']=0;$family['decisionCount']=0; }
        unset($family);
        foreach ($this->items() as $row) {
            $id=$row['family']?->getId();if (!isset($families[$id])) { continue; }
            if ($row['kind']==='page') { ++$families[$id]['pageCount']; }
            if ($row['kind']==='expertise') { ++$families[$id]['expertiseCount']; }
            if ($row['decisionReasons']) { ++$families[$id]['decisionCount']; }
        }
        return $families;
    }
    public function groups(): array
    {
        $items=$this->items();$groups=[];
        foreach ($this->plan['commercialGroups'] as [$serviceId,$pageId]) {
            $source=$items['service:'.$serviceId]??null;$page=$items['page:'.$pageId]??null;
            if (!$source || !$page || $page['kind']!=='page' || $this->isRejected($serviceId,$pageId) || $source['family']?->getId()!==$page['family']?->getId()) { continue; }
            $existing=$page['object']->getCatalogServices();
            if ($existing->count()>0 && ($existing->count()!==1 || !$existing->contains($source['object']))) { continue; }
            $groups[$page['key']]=['source'=>$source['key'],'registered'=>$existing->contains($source['object'])];
        }
        // Persisted commercial relations take precedence; multi-offer pages stay autonomous.
        foreach ($items as $page) {
            if (!$page['object'] instanceof SitePage || $page['kind']!=='page' || $page['object']->getCatalogServices()->count()!==1) { continue; }
            $service=$page['object']->getCatalogServices()->first();$key='service:'.$service->getId();
            if (isset($items[$key]) && !$this->isRejected($service->getId(),$page['id']) && $items[$key]['family']?->getId()===$page['family']?->getId()) { $groups[$page['key']]=['source'=>$key,'registered'=>true]; }
        }
        return $groups;
    }
    public function offer(string $key): ?array
    {
        $items=$this->items();$groups=$this->groups();$key=$groups[$key]['source']??$key;$row=$items[$key]??null;if (!$row) { return null; }
        $row['pages']=[$key=>$row+['relationLabel'=>'Page propre existante']];$row['groupProposed']=false;$row['related']=[];$row['localPages']=[];
        foreach ($groups as $pageKey=>$group) {
            if ($group['source']!==$key) { continue; }
            $row['pages'][$pageKey]=$items[$pageKey]+['relationLabel'=>$group['registered']?'Relation CMS enregistrée':'Association métier proposée — non enregistrée'];
            $row['groupProposed']=$row['groupProposed'] || !$group['registered'];
        }
        if ($row['kind']==='service') {
            foreach ($items as $candidate) {
                if ($candidate['kind']!=='expertise') { continue; }
                $cms=$candidate['object']->getCatalogServices()->contains($row['object']);$json=isset($candidate['linkedServices'][$key]);$proposed=in_array([$row['id'],$candidate['id']],$this->plan['transverseProposals'],true);
                if ($cms || $json || $proposed) { $row['related'][$candidate['key']]=$candidate+['relationKind'=>$cms?'registered':($proposed?'proposed':'historical'),'displayTitle'=>$this->displayTitle($candidate),'relationLabel'=>$cms?'Association métier enregistrée':($proposed?'Association proposée':'Lien éditorial historique à vérifier')]; }
            }
        }
        if ($row['kind']==='expertise') {
            $row['concerned']=[];
            foreach ($items as $candidate) {
                if ($candidate['kind']==='expertise') { continue; }
                $cms=$candidate['object'] instanceof SitePage
                    ? $candidate['object']->getCatalogExpertises()->contains($row['object'])
                    : $row['object']->getCatalogServices()->contains($candidate['object']);
                $json=isset($row['linkedServices'][$candidate['key']]);
                $proposed=$candidate['technical']==='service' && in_array([$candidate['id'],$row['id']],$this->plan['transverseProposals'],true);
                if ($cms || $json || $proposed) {
                    $root=$groups[$candidate['key']]['source']??$candidate['key'];
                    $row['concerned'][$root]=$items[$root]+['relationKind'=>$cms?'registered':($proposed?'proposed':'historical'),'relationLabel'=>$cms?'Association métier enregistrée':($proposed?'Association proposée':'Lien éditorial historique à vérifier')];
                }
            }
        }
        foreach ($this->localPages() as $page) { if ($page['localOwner']===$key) { $row['localPages'][$page['key']]=$page; } }
        return $row;
    }
    public function displayTitle(array $row): string
    {
        $alias=$this->display['shortTitles'][$row['key']]??null;
        return $alias && $alias['slug']===$row['slug'] ? $alias['label'] : $row['title'];
    }
    public function localPages(): array
    {
        $out=[];
        foreach ($this->presentation->rows() as $row) {
            $entry=$row['technical']==='page'?($this->display['localSeoPages'][(string)$row['id']]??null):null;
            if (!$entry || $entry['slug']!==$row['slug'] || !isset($this->items()[$entry['owner']])) { continue; }
            $out[]=$row+['localOwner'=>$entry['owner'],'localRole'=>$entry['role'],'relationLabel'=>'Accès métier proposé — non enregistré'];
        }
        return $out;
    }
    /** One administrative primary destination per existing content, never a new entity. */
    public function placement(array $row): array
    {
        $items=$this->items();$key=$row['key'];$groups=$this->groups();
        if (isset($items[$key])) {
            $item=$items[$key];$owner=$groups[$key]['source']??$key;
            $path=$item['kind']==='expertise'?'/admin/catalogue/expertise/'.$row['id']:'/admin/catalogue/offre/'.str_replace(':','/',$owner);
            return ['section'=>isset($groups[$key])?'Page complémentaire SEO/GEO':($item['kind']==='expertise'?'Expertises':'Prestations'),'path'=>$path,'owner'=>$owner,'family'=>$item['familyLabel'],'pending'=>$item['decisionReasons'],'proposed'=>$item['familyProposed'] || isset($groups[$key]) && !$groups[$key]['registered']];
        }
        foreach ($this->localPages() as $local) {
            if ($local['key']===$key) { return ['section'=>'Pages complémentaires SEO/GEO locales','path'=>'/admin/catalogue/offre/'.str_replace(':','/',$local['localOwner']).'#local-seo','owner'=>$local['localOwner'],'family'=>$items[$local['localOwner']]['familyLabel'],'pending'=>['Accès principal AMOA SI proposé à approuver'],'proposed'=>true]; }
        }
        $hubs=['services'=>'service','expertises-index'=>'expertise','projets'=>'project','ressources'=>'resource','secteurs-index'=>'sector','public-pme-eti'=>'sector'];
        if ($row['technical']==='page' && isset($hubs[$row['slug']])) { return ['section'=>'Présentation du catalogue','path'=>'/admin/catalogue/'.$hubs[$row['slug']],'owner'=>$key,'family'=>'Transverse','pending'=>[],'proposed'=>false]; }
        $section=match($row['technical']) {'practice'=>'Practices','project'=>'Projets & références','sector'=>'Secteurs',default=>match($row['type']) {'resource'=>'Articles & Growth','sector'=>'Secteurs',default=>'Pages du site'}};
        $path=match($section) {'Practices'=>'/admin/catalogue/service?practice='.$row['id'],'Projets & références'=>'/admin/catalogue/project','Articles & Growth'=>'/admin/catalogue/resource','Secteurs'=>'/admin/catalogue/sector',default=>'/admin/catalogue/site-pages'};
        return ['section'=>$section,'path'=>$path,'owner'=>$key,'family'=>'','pending'=>[],'proposed'=>false];
    }
    public function contentInventory(): array
    {
        return array_map(fn($row)=>$row+['placement'=>$this->placement($row)],$this->presentation->rows());
    }
    public function presentationPages(string $type): array
    {
        return array_values(array_filter($this->contentInventory(),static fn($r)=>$r['placement']['section']==='Présentation du catalogue' && $r['placement']['path']==='/admin/catalogue/'.$type));
    }
    public function reviewEnabled(): bool { return getenv('OLING_CMS_REVIEW_ENABLED')==='1'; }
    public function reviewCards(): array
    {
        $cards=[];
        foreach ($this->classificationReview() as $x) { $cards['classification:'.$x['source']['id']]=['source'=>$x['source'],'target'=>$x['target']->getDesignation(),'role'=>$x['role'],'reason'=>$x['reason'],'state'=>$x['state'],'blocked'=>false,'targetKey'=>'practice:'.$x['target']->getId()]; }
        foreach ($this->review() as $x) { $cards['association:'.$x['service'].':'.$x['page']]=['source'=>$x['source'],'target'=>$x['target']['title'],'role'=>$x['role'],'reason'=>$x['reason'],'state'=>$x['state'],'blocked'=>$x['rejected'],'targetKey'=>$x['target']['key']]; }
        foreach ($cards as $key=>&$card) {
            $source=$card['source'];$object=$source['object'];
            $relations=method_exists($object,'getCatalogPages')?$object->getCatalogPages():$object->getCatalogServices();
            $target=$this->items()[$card['targetKey']]??null;
            $targetState=$target ? [$target['slug'],$target['title'],$target['url'],$target['object']->getCatalogPractice()?->getId(),array_map(fn($o)=>$o->getId(),$target['object']->getCatalogServices()->toArray())] : null;
            $state=[$card['target'],$card['role'],$targetState,$key,$source['slug'],$source['title'],$source['url'],$object->getCatalogPractice()?->getId(),method_exists($object,'getCatalogType')?$object->getCatalogType():null,array_map(fn($o)=>$o->getId(),$relations->toArray()),$card['targetKey'],$this->plan];
            $card['hash']=hash('sha256',json_encode($state));$card['decision']=null;
        } unset($card);
        if (is_file($this->reviewFile)) {
            $f=fopen($this->reviewFile,'rb');if (!$f || !flock($f,LOCK_SH)) { throw new \RuntimeException('Journal indisponible'); }
            while (($line=fgets($f))!==false) { $event=json_decode($line,true,512,JSON_THROW_ON_ERROR);if(isset($cards[$event['key']]) && hash_equals($cards[$event['key']]['hash'],$event['hash'])) { $cards[$event['key']]['decision']=$event; } }
            flock($f,LOCK_UN);fclose($f);
        }
        return $cards;
    }
    /** Durable approval journal only; applying Doctrine classifications is deliberately absent. */
    public function recordReview(string $key, string $hash, string $opinion, string $actor): void
    {
        if (!$this->reviewEnabled()) { throw new \DomainException('Validation désactivée hors recette privée'); }
        $card=$this->reviewCards()[$key]??null;
        if (!$card || $card['blocked'] || !in_array($opinion,['approved','rejected','pending'],true) || !hash_equals($card['hash'],$hash)) { throw new \DomainException('Proposition refusée ou modifiée : rechargez la page'); }
        $dir=dirname($this->reviewFile);if(!is_dir($dir) && !mkdir($dir,0700,true) && !is_dir($dir))throw new \RuntimeException('Journal indisponible');
        $f=fopen($this->reviewFile,'ab');if(!$f || !flock($f,LOCK_EX))throw new \RuntimeException('Journal indisponible');
        try { chmod($this->reviewFile,0600);$line=json_encode(['key'=>$key,'hash'=>$hash,'opinion'=>$opinion,'actor'=>$actor,'at'=>gmdate(DATE_ATOM),'scope'=>'private-review-only','applied'=>false],JSON_THROW_ON_ERROR)."\n";if(fwrite($f,$line)!==strlen($line) || !fflush($f))throw new \RuntimeException('Écriture du journal échouée'); } finally { flock($f,LOCK_UN);fclose($f); }
    }
    public function catalogue(?int $family=null, string $q='', ?string $kind=null): array
    {
        $groups=$this->groups();$rows=[];
        foreach ($this->items() as $key=>$row) {
            if (isset($groups[$key]) || ($row['category']??'')==='local') { continue; }
            if ($family && $row['family']?->getId()!==$family) { continue; }
            if ($kind && $row['kind']!==$kind) { continue; }
            $offer=$this->offer($key);$text=$row['displayTitle'].' '.$row['title'].' '.$row['slug'].' '.$row['url'];
            foreach ($offer['pages'] as $page) { $text.=' '.$page['title'].' '.$page['url']; }
            if ($q!=='' && mb_stripos($text,$q)===false) { continue; }
            $rows[]=$offer;
        }
        usort($rows,static fn($a,$b)=>strnatcasecmp($a['title'],$b['title']));return $rows;
    }
    public function decisions(): array { return array_values(array_filter($this->items(),static fn($row)=>$row['decisionReasons']!==[])); }
    public function review(): array
    {
        $items=$this->items();$out=[];
        foreach ($this->plan['review'] as $index=>$proposal) {
            $source=$items['service:'.$proposal['service']]??null;$target=$items['page:'.$proposal['page']]??null;
            if (!$source || !$target || $source['slug']!==$proposal['serviceSlug'] || $target['slug']!==$proposal['pageSlug']) { continue; }
            $registered=$target['object']->getCatalogServices()->contains($source['object']);
            $state=$proposal['rejected']?'Rejetée par l’audit':($registered?'Relation CMS enregistrée':'Proposition');
            $out[]=$proposal+['number'=>$index+1,'source'=>$source,'target'=>$target,'state'=>$state,'registered'=>$registered,'jsonLink'=>isset($target['linkedServices'][$source['key']])];
        }
        return $out;
    }
    public function classificationReview(): array
    {
        $out=[];$families=$this->presentation->families();
        foreach ($this->items() as $row) {
            if ($row['technical']!=='page' || !$row['proposal']) { continue; }
            $target=$families[$row['proposal']['practice']]['object']??null;if (!$target) { continue; }
            $current=$row['object']->getCatalogPractice();
            $out[]=['source'=>$row,'target'=>$target,'role'=>$row['proposal']['role'],'reason'=>'Proposition de l’audit métier : '.$row['proposal']['role'].'. Classification administrative indépendante du routage et des relations de Services.','state'=>$current?($current->getId()===$target->getId()?'Famille enregistrée':'Classement existant différent — arbitrage requis'):'Proposition'];
        }
        return $out;
    }
    public function auditDate(): string { return $this->plan['auditDate']; }
    private function isRejected(int $service, int $page): bool { return in_array([$service,$page],$this->plan['rejected'],true); }
}
