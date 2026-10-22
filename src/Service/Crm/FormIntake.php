<?php
namespace App\Service\Crm;

/** Shared reference binds direct delivery and the structured email notification. */
final class FormIntake
{
    public function __construct(private CrmStore $store) {}
    public static function source(array $p,string $external='',string $url=''): array
    {
        $site=$p['site']??''; $ref=$p['reference']??'';
        if (!in_array($site,['OLING','MAPSI'],true) || !is_string($ref) || !preg_match('/^[a-zA-Z0-9_-]{8,100}$/D',$ref)) { throw new \InvalidArgumentException('Référence formulaire invalide.'); }
        $f=$p['fields']??[];
        if (!is_array($f) || !filter_var($f['email']??'',FILTER_VALIDATE_EMAIL) || empty($f['summary'])) { throw new \InvalidArgumentException('Contact et besoin requis.'); }
        $f['site']=$site; $f['submission_ref']=$ref; $f['contact']=trim(($f['first_name']??'').' '.($f['last_name']??''))?:($f['contact']??'');
        $title='Demande '.$site.' · '.($f['organization']??$f['contact']?:'Nouveau contact');
        $f['title']=$title;
        return ['origin'=>'form','identity'=>$site.'|'.$ref,'external_id'=>$external?:'direct|'.$site.'|'.$ref,'title'=>$title,'fields'=>$f,'kind'=>'private','url'=>$url,'commercial'=>true];
    }
    public function receive(array $p,string $external='',string $url=''): array { return $this->store->ingest(self::source($p,$external,$url)); }
}
