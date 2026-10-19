<?php
namespace App\Service;

use App\Entity\{CmsMedia, Services, SitePage};
use Doctrine\ORM\EntityManagerInterface;

/** Read-only presentation: never infers or persists relationships. */
final class CmsAdminPresentation
{
    private ?array $rows = null;
    public function __construct(private CmsCatalogue $catalogue, private EntityManagerInterface $em) {}
    public function rows(): array
    {
        if ($this->rows !== null) { return $this->rows; }
        $media = [];
        foreach ($this->em->getRepository(CmsMedia::class)->findBy(['locale'=>'fr'],['sortOrder'=>'ASC','id'=>'ASC']) as $item) {
            $media[$item->getOwnerType().':'.$item->getOwner()?->getId()][]=$item;
        }
        $rows=$this->catalogue->all();
        foreach ($rows as &$row) {
            $object=$row['object'];$owned=$media[$row['key']]??[];$image=null;
            foreach ($owned as $item) { if ($item->getSlot()==='hero') { $image=$item->getPath();break; } }
            foreach (['HeroImage','ImageHero','Image1','Image','Image2'] as $field) {
                if (!$image && method_exists($object,'get'.$field)) { $image=$object->{'get'.$field}(); }
            }
            if (!$image && $object instanceof SitePage) {
                $data=$object->getTranslation('fr')?->getStructuredData()??[];
                foreach ([$data['homePage']['hero']['portraitImage']??null,$data['landingNarrative']['heroImage']??null,$data['corePage']['heroImage']??null] as $path) {
                    if (is_string($path) && $path!=='') { $image=$path;break; }
                }
                if (!$image && preg_match('~<img\b[^>]*\bsrc=["\']([^"\']+)["\']~i',(string)$object->getBodyHtml(),$match)) { $image=html_entity_decode($match[1],ENT_QUOTES); }
            }
            $row['thumbnail']=$this->imagePath($image ?: ($owned[0]??null)?->getPath());
            $description='';
            foreach (['IntroductionShort','DescriptionShort','ShortDescription','HeroIntro','MetaDescription','Description'] as $field) {
                if (!$description && method_exists($object,'get'.$field)) { $description=(string)$object->{'get'.$field}(); }
            }
            $row['description']=mb_substr(trim(preg_replace('/\s+/u',' ',strip_tags($description))??''),0,145);
            $row['statusLabel']=['published'=>'Publié','draft'=>'Brouillon','unpublished'=>'Non publié','legacy'=>'Non renseigné'][$row['status']]??$row['status'];
            $row['category']=$this->category($row);
            $row['pageLabel']=match($row['slug']) {
                'home'=>'Accueil','apropos'=>'À propos','rse'=>'RSE & engagements','team'=>'Équipe','client'=>'Clients','metiers'=>'Nos métiers','contact'=>'Contact',
                default=>str_starts_with($row['slug'],'cabinet-conseil-') ? 'Implantation · '.ucwords(str_replace('-',' ',substr($row['slug'],15))) : $row['title']
            };
            $row['associated']=[];
        }
        unset($row);
        foreach ($rows as &$row) {
            if (!$row['object'] instanceof Services) { continue; }
            foreach ($rows as $page) {
                if ($page['object'] instanceof SitePage && $page['object']->getCatalogServices()->contains($row['object'])) { $row['associated'][$page['key']]=$page; }
            }
            // Expertises are included only through stored explicit page relations.
            foreach ($row['associated'] as $page) {
                foreach ($page['object']->getCatalogExpertises() as $expertise) {
                    foreach ($rows as $candidate) { if ($candidate['object']===$expertise) { $row['associated'][$candidate['key']]=$candidate; } }
                }
            }
        }
        unset($row);
        return $this->rows=$rows;
    }
    /** Existing Practice identities and stored Service membership only. */
    public function families(): array
    {
        $families=[];
        foreach ($this->rows() as $row) {
            if ($row['technical']==='practice') { $families[$row['id']]=$row+['serviceCount'=>0]; }
        }
        foreach ($this->rows() as $row) {
            if ($row['technical']==='service' && isset($families[$row['practice']?->getId()])) {
                ++$families[$row['practice']->getId()]['serviceCount'];
            }
        }
        return $families;
    }
    public function item(object $object): ?array
    {
        foreach ($this->rows() as $row) { if ($row['object']===$object) { return $row; } }
        return null;
    }
    private function imagePath(?string $path): ?string
    {
        if (!$path || str_starts_with($path,'//') || preg_match('~^(?:javascript|data|file):~i',$path)) { return null; }
        return preg_match('~^https?://~i',$path) ? $path : '/'.ltrim($path,'/');
    }
    private function category(array $row): string
    {
        if ($row['type']==='legal') { return 'legal'; }
        if ($row['type']==='sector') { return 'sector'; }
        $slug=$row['slug'];
        if ($slug==='home') { return 'home'; }
        if ($slug==='contact') { return 'contact'; }
        if (in_array($slug,['apropos','rse','team','client','metiers'],true)) { return 'company'; }
        if (str_starts_with($slug,'cabinet-conseil-') || in_array($slug,['metropoles-hexagone','hexagone-drom-dom-tom'],true)) { return 'local'; }
        return 'other';
    }
}
