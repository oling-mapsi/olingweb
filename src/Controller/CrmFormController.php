<?php
namespace App\Controller;
use App\Service\Crm\{CrmConfig,FormIntake,CrmStore};
use Symfony\Component\HttpFoundation\{Request,JsonResponse};
use Symfony\Component\Routing\Annotation\Route;
final class CrmFormController
{
    #[Route('/api/crm/forms',name:'api_crm_forms',methods:['POST'])]
    public function receive(Request $r,CrmConfig $config,FormIntake $intake,CrmStore $store): JsonResponse
    {
        $token=$config->get('forms_token','');
        if (!$config->enabled('forms') || strlen($token)<32 || !hash_equals('Bearer '.$token,$r->headers->get('Authorization',''))) { return new JsonResponse(['error'=>'Unauthorized'],401); }
        if(strlen($r->getContent())>512000) { return new JsonResponse(['error'=>'Payload too large'],413); }
        try {
            $p=json_decode($r->getContent(),true,32,JSON_THROW_ON_ERROR);
            if (($p['site']??'')!=='MAPSI') { throw new \InvalidArgumentException(); }
            $result=$intake->receive($p); $store->setState('mapsi',['last_success'=>CrmStore::now()]);
            return new JsonResponse($result);
        } catch (\InvalidArgumentException|\JsonException|\TypeError) { return new JsonResponse(['error'=>'Invalid form'],422); }
    }
}
