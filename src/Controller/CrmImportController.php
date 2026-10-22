<?php
namespace App\Controller;

use App\Service\Crm\{CrmConfig,CrmStore};
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\{Request,JsonResponse};
use Symfony\Component\Routing\Annotation\Route;

final class CrmImportController
{
    #[Route('/api/crm/watch',name:'api_crm_watch',methods:['POST'])]
    public function import(Request $r,CrmConfig $config,CrmStore $store): JsonResponse
    {
        $token=$config->get('import_token');
        if (!$config->enabled('watch') || strlen($token)<32 || !hash_equals('Bearer '.$token,$r->headers->get('Authorization',''))) { return new JsonResponse(['error'=>'Accès refusé.'],403); }
        if (strlen($r->getContent())>65536) { return new JsonResponse(['error'=>'Charge trop volumineuse.'],413); }
        try {
            $p=json_decode($r->getContent(),true,16,JSON_THROW_ON_ERROR);
            if (!is_array($p) || !is_string($p['id']??null) || !is_string($p['title']??null) || !is_bool($p['follow']??false) || !is_array($p['fields']??[])) { throw new \InvalidArgumentException(); }
            // Stable tender identity includes lot, avoiding merging different lots.
            $fields=$p['fields']??[]; ksort($fields); $p['fields']=$fields; ksort($p);
            $identity=!empty($fields['reference']) && !empty($fields['buyer']) ? $fields['buyer'].'|'.$fields['reference'].'|'.($fields['lot']??'') : $p['id'];
            $source=['origin'=>'watch','external_id'=>$p['id'].'|'.hash('sha256',CrmStore::json($p)),'identity'=>$identity,'title'=>$p['title'],'fields'=>$fields,'url'=>$p['url']??'','kind'=>'public','follow'=>$p['follow']??false];
            $result=$store->ingest($source); $store->setState('watch',['last_success'=>CrmStore::now(),'agent'=>$p['agent']??'Veille marchés publics OLING']);
            return new JsonResponse($result+['url'=>'https://oling.fr/admin/crm/'.$result['id']],$result['duplicate']?200:201);
        } catch (UniqueConstraintViolationException) { return new JsonResponse(['error'=>'Import simultané ; réessayer la même requête.'],409); }
        catch (\JsonException|\InvalidArgumentException|\TypeError) { return new JsonResponse(['error'=>'JSON invalide : id, title, follow booléen et fields requis ; liens HTTPS.'],422); }
    }
}
