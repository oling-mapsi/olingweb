<?php

namespace App\Service;

use App\Entity\{Practice, Services, Projet, SitePage, Metier, LegalPage};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/** Read-only catalogue. Editorial contents and public routing are never altered. */
final class CmsCatalogue
{
    public const TYPES = ['practice'=>'Practices','service'=>'Services','expertise'=>'Expertises','project'=>'Projets / Références','resource'=>'Ressources','sector'=>'Secteurs','institutional'=>'Pages institutionnelles / libres','legal'=>'Pages légales'];
    public const SERVICE_PAGES = ['amoa-si','erp-progiciel','crm','gmao','rgpd','cyber-securite','conseil-qualite','conformite-reglementaire','si-finance','facturation-electronique-amoa','infrastructure-si-amoa','mapsi-progiciel','gestion-risques-audit-controle-interne','direction-qualite-deleguee','direction-conformite-externalisee','dsi-externalisee','pca-pra-continuite-activite'];
    public function __construct(private readonly EntityManagerInterface $em, private readonly RouterInterface $router, private readonly KernelInterface $kernel) {}

    public static function pageType(SitePage $page): string
    {
        if ($page->getCatalogType() !== null && isset(self::TYPES[$page->getCatalogType()])) { return $page->getCatalogType(); }
        $slug = (string) $page->getSlug();
        if (str_starts_with($slug, 'ressource-')) { return 'resource'; }
        if (str_starts_with($slug, 'expertise-')) { return 'expertise'; }
        if (str_starts_with($slug, 'secteur-')) { return 'sector'; }
        return in_array($slug, self::SERVICE_PAGES, true) ? 'service' : 'institutional';
    }

    public function all(): array
    {
        $observed = [];
        foreach ($this->baseline() as $record) { $observed[parse_url($record['url'], PHP_URL_PATH)] = $record; }
        $hubPath = $this->kernel->getProjectDir().'/var/cms/hubs.json';
        $hubSnapshot = is_file($hubPath) ? json_decode(file_get_contents($hubPath),true) : [];
        $navigationPaths = array_map(fn($item)=>parse_url($item['url'],PHP_URL_PATH),$hubSnapshot['navigation'] ?? []);
        $rows = [];
        foreach (['practice'=>Practice::class,'service'=>Services::class,'project'=>Projet::class,'page'=>SitePage::class,'sector'=>Metier::class,'legal'=>LegalPage::class] as $technical=>$class) {
            foreach ($this->em->getRepository($class)->findAll() as $object) {
                $type = $object instanceof SitePage ? self::pageType($object) : $technical;
                $practice = method_exists($object,'getCatalogPractice') ? $object->getCatalogPractice() : null;
                $slug = (string) $object->getSlug();
                $title = method_exists($object,'getDesignation') ? $object->getDesignation() : $object->getTitle();
                if ($object instanceof SitePage) { $narrative = $object->getTranslation('fr')?->getStructuredData()['landingNarrative'] ?? []; $title = $narrative['heroTitle'] ?? $object->getHeroTitle() ?: $title; }
                $status = method_exists($object,'getPublicationStatus') ? $object->getPublicationStatus() : null;
                $url = $this->publicPath($object);
                $anomalies = [];
                if (in_array($type,['service','expertise','resource','project'],true) && !$practice) { $anomalies[] = ['level'=>'warning','message'=>'Practice principale absente']; }
                if ($object instanceof Projet && $object->getServices()->isEmpty()) { $anomalies[] = ['level'=>'warning','message'=>'Aucun Service associé']; }
                if ($object instanceof Services && !$object->getPractice()) { $anomalies[] = ['level'=>'error','message'=>'Rattachement de route historique absent : page inaccessible']; }
                if ($object instanceof SitePage && $object->getCatalogType() === null) { $anomalies[] = ['level'=>'recommendation','message'=>'Type métier déduit ; classification à confirmer']; }
                $hub = 'unknown'; $reason = 'Visibilité gérée par les templates publics ; à vérifier';
                if ($type === 'resource' && $object instanceof SitePage) {
                    $blocked = preg_match('/^ressource-(pilot-|test-|demo-|e2e-)/',$slug) === 1;
                    $historicalResource = str_starts_with($slug,'ressource-');
                    $hub = $status === 'published' && !$blocked && $historicalResource ? 'yes' : 'no';
                    $reason = !$historicalResource ? 'Page hors sélection du hub Ressources historique' : ($blocked ? 'Préfixe technique exclu du hub public' : ($status !== 'published' ? 'Ressource non publiée' : 'Sélection repository du hub ; traduction à vérifier'));
                    if ($status === 'published' && $blocked) { $anomalies[] = ['level'=>'warning','message'=>$reason]; }
                }
                $expectedHub = ['service'=>'/services','expertise'=>'/expertises','project'=>'/projets','resource'=>'/ressources','sector'=>'/secteurs'][$type] ?? null;
                if ($url && $expectedHub && ($hubSnapshot['hubs'][$expectedHub]['http'] ?? null) === 200) {
                    $hub = in_array('https://oling.fr'.$url,$hubSnapshot['hubs'][$expectedHub]['links'],true) ? 'yes' : 'no';
                    $reason = $hub === 'yes' ? 'Lien observé dans la référence du hub public' : 'Aucun lien observé dans la référence du hub public';
                }
                $navigation = $url && $navigationPaths ? (in_array($url,$navigationPaths,true) ? 'yes' : 'no') : 'unknown';
                $observation = $url ? ($observed[$url] ?? null) : null;
                if (($observation['http'] ?? 0) === 404) { $anomalies[]=['level'=>'error','message'=>'HTTP 404 observé dans la référence publique']; }
                if ($observation && ($observation['canonical'] ?? '') !== '' && parse_url($observation['canonical'],PHP_URL_PATH) !== $url) { $anomalies[]=['level'=>'warning','message'=>'Canonical différent de l’URL : revue SEO nécessaire']; }
                foreach ($observation['internal_links'] ?? [] as $link) { if (($observed[parse_url($link,PHP_URL_PATH)]['http'] ?? 0) === 404) { $anomalies[]=['level'=>'warning','message'=>'Lien interne vers une 404 observée : '.parse_url($link,PHP_URL_PATH)]; } }
                $edit = match($technical) {
                    'practice'=>$this->router->generate('admin_practices_edit',['id'=>$object->getId()]),
                    'service'=>$this->router->generate('admin_services_edit',['id'=>$object->getId()]),
                    'project'=>$this->router->generate('admin_projets_edit',['id'=>$object->getId()]),
                    'page'=>$this->router->generate('admin_pages_edit',['id'=>$object->getId()]),
                    'sector'=>$this->router->generate('admin_metiers_edit',['id'=>$object->getId()]),
                    'legal'=>$this->router->generate('admin_legal_pages_edit',['slug'=>$slug]),
                };
                $updated = $object instanceof SitePage ? $object->getTranslation('fr')?->getSourceUpdatedAt() : ($object instanceof LegalPage ? $object->getUpdatedAt() : null);
                $rows[] = ['key'=>$technical.':'.$object->getId(),'technical'=>$technical,'type'=>$type,'id'=>$object->getId(),'object'=>$object,'title'=>$title,'slug'=>$slug,'practice'=>$practice,'url'=>$url,'status'=>$status ?? 'legacy','hub'=>$hub,'hubReason'=>$reason,'navigation'=>$navigation,'indexation'=>$observation && array_key_exists('indexable',$observation) ? ($observation['indexable'] === null ? 'unknown' : ($observation['indexable'] ? 'yes' : 'no')) : 'unknown','observation'=>$observation,'updated'=>$updated,'edit'=>$edit,'anomalies'=>$anomalies,'origin'=>$object instanceof SitePage && $object->getExternalId() ? 'Growth' : 'Admin'];
            }
        }
        $owners = [];
        foreach ($rows as $index=>$row) { if ($row['url']) { $owners[$row['url']][] = $index; } }
        foreach ($owners as $url=>$indexes) { if (count($indexes)>1) { foreach ($indexes as $index) { $rows[$index]['anomalies'][]=['level'=>'warning','message'=>'URL revendiquée par plusieurs objets : '.$url]; } } }
        return $rows;
    }

    public function baseline(): array
    {
        $path = $this->kernel->getProjectDir().'/var/cms/seo-before.json';
        if (!is_file($path)) { return []; }
        $data = json_decode(file_get_contents($path),true);
        return is_array($data['records'] ?? null) ? $data['records'] : [];
    }

    public function publicPath(object $object): ?string
    {
        $slug = (string) $object->getSlug();
        if ($object instanceof Services) { return $object->getPractice() ? '/'.$object->getPractice()->getSlug().'/'.$slug : null; }
        if ($object instanceof Practice) { return '/practice/'.$slug; }
        if ($object instanceof Projet) { $path = $object->getPublicUrl(); return $path && str_starts_with($path,'/') && !str_starts_with($path,'//') ? $path : null; }
        if ($object instanceof Metier) { return '/secteurs/'.$slug; }
        if ($object instanceof LegalPage) { return match($slug) {'polrgpd'=>'/a-propos/politiquergpd','polsecurite'=>'/a-propos/politiquesecurite','mentions-legales'=>'/mentions-legales','charte-ia'=>'/charte-ia',default=>null}; }
        if (str_starts_with($slug,'ressource-')) { return '/ressources/'.substr($slug,10); }
        if (str_starts_with($slug,'expertise-')) { return '/expertises/'.substr($slug,10); }
        if (str_starts_with($slug,'secteur-')) { return '/secteurs/'.substr($slug,8); }
        $fixed = ['home'=>'/','apropos'=>'/a-propos','team'=>'/a-propos/team','client'=>'/a-propos/client','metiers'=>'/a-propos/metiers','rse'=>'/a-propos/rse','expertises-index'=>'/expertises','secteurs-index'=>'/secteurs'];
        if (isset($fixed[$slug])) { return $fixed[$slug]; }
        // Only expose URLs supported by an existing static public route.
        foreach ($this->router->getRouteCollection() as $route) { if ($route->getPath() === '/'.$slug && !str_contains($route->getPath(),'{')) { return '/'.$slug; } }
        return null;
    }
}
