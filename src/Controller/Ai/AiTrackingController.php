<?php
namespace App\Controller\Ai;
use App\Entity\User;
use App\Service\Ai\{AiTrackingAccess,AiTrackingContract,AiNativeRegistry};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\{Request,Response,JsonResponse};
use Symfony\Component\Routing\Annotation\Route;
final class AiTrackingController
{
    #[Route('/api/ai/mcp',name:'admin_ai_tracking_mcp',methods:['GET','POST','DELETE'])]
    public function dispatch(Request $r,AiTrackingAccess $access,AiNativeRegistry $registry,EntityManagerInterface $em): Response
    {
        if(!$access->available())return new JsonResponse(['error'=>'oauth_connector_not_configured'],503);
        if(!$r->isSecure())return new JsonResponse(['error'=>'https_required'],403);
        $origin=$r->headers->get('Origin');if($origin!==null&&!in_array($origin,['https://chatgpt.com','https://ai.oling.fr'],true))return new JsonResponse(['error'=>'origin_denied'],403);
        $identity=$access->resolve($r);if(!$identity)return new JsonResponse(['error'=>'unauthorized'],401,['WWW-Authenticate'=>'Bearer']);
        $user=$em->find(User::class,$identity['owner']);
        if(!$user||!array_intersect(['ROLE_ADMIN','ROLE_SUPER_ADMIN'],$user->getRoles()))return new JsonResponse(['error'=>'forbidden'],403);
        if($r->getMethod()!=='POST')return new Response('',405,['Allow'=>'POST']);
        if(strlen($r->getContent())>65536)return new JsonResponse(['error'=>'payload_too_large'],413);
        if(!str_starts_with($r->headers->get('Content-Type',''),'application/json'))return new JsonResponse(['error'=>'json_required'],415);
        try {$p=json_decode($r->getContent(),true,16,JSON_THROW_ON_ERROR);}catch(\JsonException){return $this->error(null,-32700,'invalid_json');}
        if(!is_array($p)||($p['jsonrpc']??null)!=='2.0'||!is_string($p['method']??null)||isset($p['id'])&&!(is_int($p['id'])||is_string($p['id'])))return $this->error(null,-32600,'invalid_request');
        $id=$p['id']??null;
        if(!array_key_exists('id',$p))return new Response('',202); // Notifications never cause mutations.
        $method=$p['method'];$params=$p['params']??[];
        if(!is_array($params))return $this->error($id,-32602,'invalid_params');
        if($method==='initialize')$result=['protocolVersion'=>'2025-03-26','capabilities'=>['tools'=>new \stdClass()],'serverInfo'=>['name'=>'OLING IA tracking','version'=>'1.0.0-local']];
        elseif($method==='ping')$result=new \stdClass();
        elseif($method==='tools/list') {
            $tools=[];foreach(AiTrackingContract::tools() as $tool)if(in_array($tool['scope'],$identity['scopes'],true)){unset($tool['scope']);$tools[]=$tool;}$result=['tools'=>$tools];
        }elseif($method==='tools/call') {
            $name=$params['name']??'';$args=$params['arguments']??[];$tool=is_string($name)?(AiTrackingContract::tools()[$name]??null):null;
            if(!$tool||!is_array($args))return $this->error($id,-32602,'unknown_tool_or_arguments');
            if(!in_array($tool['scope'],$identity['scopes'],true))return $this->error($id,-32003,'scope_denied');
            try {$value=$registry->call($name,$args,$identity['owner'],$identity['actor']);}
            catch(\OutOfBoundsException){return $this->error($id,-32004,'not_found');}
            catch(\DomainException){return $this->error($id,-32009,'conflict_stale_or_transition');}
            catch(\InvalidArgumentException|\TypeError|\JsonException){return $this->error($id,-32602,'invalid_arguments');}
            catch(\Doctrine\DBAL\Exception\RetryableException){return $this->error($id,-32009,'concurrent_change_retry_same_event');}
            $result=['content'=>[['type'=>'text','text'=>json_encode($value,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]]];
        }else return $this->error($id,-32601,'method_not_found');
        return new JsonResponse(['jsonrpc'=>'2.0','id'=>$id,'result'=>$result],200,['Cache-Control'=>'private, no-store']);
    }
    private function error(mixed $id,int $code,string $message): JsonResponse {return new JsonResponse(['jsonrpc'=>'2.0','id'=>$id,'error'=>['code'=>$code,'message'=>$message]],200,['Cache-Control'=>'private, no-store']);}
}
