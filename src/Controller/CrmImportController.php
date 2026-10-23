<?php
namespace App\Controller;

use App\Service\Crm\{CrmConfig,CrmStore,CrmWatchIdentity};
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\{Request,JsonResponse,Response};
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
            $fields=$p['fields']??[]; $fields['notice_id']=$p['id']; ksort($fields); $p['fields']=$fields; ksort($p);
            $aliases=CrmWatchIdentity::aliases($fields); $legacy=$store->identityDeal('watch','agent-id|'.$p['id']) ?? $store->identityDeal('watch','notice|'.$p['id'].'|lot|');
            if ($legacy && ($legacy['lot']==='' || $legacy['lot']===($fields['lot']??''))) {
                $aliases[]='agent-id|'.$p['id'];
                if ($legacy['lot']==='') { $aliases[]='notice|'.$p['id'].'|lot|'; }
            }
            if (preg_match('/newsletter|digest|sélection d.appels|veille quotidienne/ui',$p['title'])) { throw new \InvalidArgumentException(); }
            $source=['origin'=>'watch','external_id'=>$p['id'].'|'.hash('sha256',CrmStore::json($p)),'identity'=>'notice|'.$p['id'].'|lot|'.($fields['lot']??''),'aliases'=>$aliases,'title'=>$p['title'],'fields'=>$fields,'url'=>$p['url']??'','kind'=>'public','follow'=>$p['follow']??false];
            $result=$store->ingest($source); $store->setState('watch',['last_success'=>CrmStore::now(),'agent'=>$p['agent']??'Veille marchés publics OLING']);
            if (!empty($result['deleted'])) { return new JsonResponse($result+['ignored'=>true,'reason'=>'deleted_by_user','message'=>'Fiche supprimée par l’utilisateur. Ne pas réessayer avec un autre identifiant.']); }
            return new JsonResponse($result+['url'=>'https://oling.fr/admin/crm/'.$result['id']],$result['duplicate']?200:201);
        } catch (UniqueConstraintViolationException) { return new JsonResponse(['error'=>'Import simultané ; réessayer la même requête.'],409); }
        catch (\JsonException|\InvalidArgumentException|\TypeError) { return new JsonResponse(['error'=>'JSON invalide : id, title, follow booléen et fields requis ; liens HTTPS.'],422); }
    }

    #[Route('/api/crm/mcp',name:'api_crm_mcp',methods:['POST','GET','DELETE'])]
    public function mcp(Request $r,CrmConfig $config,CrmStore $store,Connection $db): Response
    {
        $token=$config->get('import_token','');
        if (!$config->enabled('watch') || !is_string($token) || strlen($token)<32 || !hash_equals('Bearer '.$token,$r->headers->get('Authorization',''))) { return new JsonResponse(['error'=>'Unauthorized'],401,['WWW-Authenticate'=>'Bearer']); }
        $origin=$r->headers->get('Origin');
        if ($origin!==null && !in_array($origin,['https://chatgpt.com','https://oling.fr'],true)) { return new JsonResponse(['error'=>'Forbidden origin'],403); }
        if ($r->getMethod()!=='POST') { return new Response('',405,['Allow'=>'POST']); }
        if (strlen($r->getContent())>65536) { return new Response('',413); }
        try { $p=json_decode($r->getContent(),true,16,JSON_THROW_ON_ERROR); } catch (\JsonException) { return $this->rpcError(null,-32700,'Parse error'); }
        if (!is_array($p) || ($p['jsonrpc']??'')!=='2.0' || !is_string($p['method']??null)) { return $this->rpcError(null,-32600,'Invalid request'); }
        $id=$p['id']??null; $method=$p['method'];
        if (!array_key_exists('id',$p)) { return new Response('',202); }
        if ($method==='initialize') {
            $version=$p['params']['protocolVersion']??'2025-03-26';
            $result=['protocolVersion'=>in_array($version,['2025-03-26','2025-06-18','2025-11-25'],true)?$version:'2025-03-26','capabilities'=>['tools'=>new \stdClass()],'serverInfo'=>['name'=>'OLING CRM','version'=>'2.0.0']];
        } elseif ($method==='ping') { $result=new \stdClass(); }
        elseif ($method==='tools/list') {
            $properties=[];
            foreach (CrmStore::FIELDS as $field) { $properties[$field]=['type'=>'string']; }
            $result=['tools'=>[
                ['name'=>'crm_tender_decisions','description'=>'Lire les décisions des marchés publics avant chaque import. Paginer jusqu’à next_offset=null. Ne pas réimporter comme nouveau un avis/lot refusé. Aucun email ni contact.','inputSchema'=>['type'=>'object','properties'=>['offset'=>['type'=>'integer','minimum'=>0]],'additionalProperties'=>false],'annotations'=>['readOnlyHint'=>true,'destructiveHint'=>false,'openWorldHint'=>false]],
                ['name'=>'crm_connection_status','description'=>'Vérifier la connexion au CRM OLING sans lire de données commerciales.','inputSchema'=>['type'=>'object','properties'=>new \stdClass(),'additionalProperties'=>false],'annotations'=>['readOnlyHint'=>true,'destructiveHint'=>false,'openWorldHint'=>false]],
                ['name'=>'crm_import_tender','description'=>'Créer ou enrichir une suggestion de marché public OLING. Lire crm_tender_decisions avant les imports. Réutiliser le même id stable par avis et lot. Ne jamais importer un digest ou une newsletter. Une décision rejetée reste rejetée. Si deleted=true ou ignored=true, la fiche a été supprimée : ne pas la recréer ni changer son identifiant pour réessayer. follow=true uniquement si l’utilisateur demande explicitement à suivre. Les corrections manuelles sont conservées. Aucune lecture des emails, aucun envoi. dry_run=true teste sans conserver de fiche.','inputSchema'=>['type'=>'object','properties'=>['id'=>['type'=>'string','maxLength'=>500],'title'=>['type'=>'string','maxLength'=>1000],'follow'=>['type'=>'boolean'],'url'=>['type'=>'string'],'fields'=>['type'=>'object','properties'=>$properties,'additionalProperties'=>false],'dry_run'=>['type'=>'boolean','default'=>false]],'required'=>['id','title','follow','fields'],'additionalProperties'=>false],'annotations'=>['readOnlyHint'=>false,'destructiveHint'=>false,'idempotentHint'=>true,'openWorldHint'=>false]]
            ]];
        } elseif ($method==='tools/call') {
            $name=$p['params']['name']??''; $args=$p['params']['arguments']??[];
            if ($name==='crm_connection_status') { $value=['connected'=>true,'environment'=>'production','crm_url'=>'https://oling.fr/admin/crm']; }
            elseif ($name==='crm_tender_decisions') { $value=$store->tenderDecisions(max(0,(int)($args['offset']??0))); }
            elseif ($name==='crm_import_tender' && is_array($args)) {
                $dry=$args['dry_run']??false;
                if (!is_bool($dry)) { return $this->rpcError($id,-32602,'Invalid dry_run'); }
                unset($args['dry_run']);
                $inner=Request::create('/api/crm/watch','POST',[],[],[],['HTTP_AUTHORIZATION'=>'Bearer '.$token],json_encode($args,JSON_THROW_ON_ERROR));
                if ($dry) { $db->beginTransaction(); }
                try { $response=$this->import($inner,$config,$store); $value=json_decode($response->getContent(),true); }
                finally { if ($dry) { $db->rollBack(); } }
                if ($dry) { unset($value['url'],$value['id']); $value['dry_run']=true; $value['persisted']=false; }
                if ($response->getStatusCode()>=400) { $result=['isError'=>true,'content'=>[['type'=>'text','text'=>json_encode($value,JSON_UNESCAPED_UNICODE)]]]; return new JsonResponse(['jsonrpc'=>'2.0','id'=>$id,'result'=>$result]); }
                $store->setState('watch_connection',['last_success'=>CrmStore::now(),'transport'=>'mcp','dry_run'=>$dry]);
            } else { return $this->rpcError($id,-32602,'Unknown tool or invalid arguments'); }
            $result=['content'=>[['type'=>'text','text'=>json_encode($value,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)]]];
        } else { return $this->rpcError($id,-32601,'Method not found'); }
        return new JsonResponse(['jsonrpc'=>'2.0','id'=>$id,'result'=>$result],200,['Cache-Control'=>'no-store']);
    }
    private function rpcError(mixed $id,int $code,string $message): JsonResponse
    { return new JsonResponse(['jsonrpc'=>'2.0','id'=>$id,'error'=>['code'=>$code,'message'=>$message]]); }
}
