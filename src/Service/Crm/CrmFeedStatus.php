<?php
namespace App\Service\Crm;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class CrmFeedStatus extends AbstractExtension
{
    public function __construct(private CrmStore $store, private CrmConfig $config) {}
    public function getFunctions(): array { return [new TwigFunction('crm_feed_status', [$this,'status'])]; }
    public function status(): array
    {
        return self::summarize($this->config->status(), $this->store->state('watch'), $this->store->state('watch_connection'), $this->store->state('forms'), $this->store->state('mapsi'), time());
    }
    public static function summarize(array $enabled,array $watch,array $connection,array $forms,array $mapsi,int $now): array
    {
        $feeds=[];
        if ($enabled['watch'] ?? false) {
            $last=$watch['last_success']??$connection['last_success']??null;
            $feeds[]=['name'=>'Agent marchés publics','status'=>!empty($watch['last_success'])?'Actif · dernière réception confirmée':($last?'Actif · connexion confirmée':'Actif · réception à confirmer'),'date'=>$last,'detail'=>isset($watch['last_success'])?'Dernier import réussi':'Dernière connexion réussie'];
        }
        if ($enabled['forms'] ?? false) {
            $last=$forms['last_success']??null;
            $healthy=($enabled['scheduler']??false) && $last && strtotime($last)>=$now-1200 && empty($forms['pending']) && empty($forms['error']);
            $feeds[]=['name'=>'Formulaires oling.fr','status'=>$healthy?'Actifs · contrôle opérationnel':'Actifs · contrôle à vérifier','date'=>$last,'detail'=>'Dernier contrôle de la reprise'];
            $feeds[]=['name'=>'Formulaires mapsi.fr','status'=>$healthy && !empty($mapsi['last_success'])?'Actifs · réception confirmée':'Actifs · réception à vérifier','date'=>$mapsi['last_success']??null,'detail'=>'Dernière réception réussie'];
        }
        return $feeds;
    }
}
