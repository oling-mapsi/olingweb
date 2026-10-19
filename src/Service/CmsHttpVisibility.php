<?php
namespace App\Service;

use Symfony\Component\HttpClient\NativeHttpClient;
use Symfony\Component\HttpKernel\KernelInterface;

/** Public GET probes only. Persists observations in a local runtime file, never editorial data. */
final class CmsHttpVisibility
{
    private string $file;
    public function __construct(KernelInterface $kernel) { $this->file=$kernel->getProjectDir().'/var/cms-public-observations.json'; }
    public function refresh(string $url): array
    {
        $data=json_decode(file_get_contents($this->file),true,512,JSON_THROW_ON_ERROR);
        $parts=parse_url($url);
        if(!isset($data['records'][$url]) || ($parts['scheme']??'')!=='https' || ($parts['host']??'')!=='oling.fr' || (isset($parts['port']) || isset($parts['user'])) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment']))throw new \InvalidArgumentException('URL absente du relevé OLING autorisé.');
        $at=gmdate('c');$http=0;$robots='';$xRobots='';$failure=null;
        // Dedicated unauthenticated client: no cookies, credentials, redirects or simulation.
        $client=new NativeHttpClient(['max_redirects'=>0,'timeout'=>8,'max_duration'=>12,'headers'=>['User-Agent'=>'OLING-CMS-Visibility-Readonly/1','Accept'=>'text/html']]);
        try{
            $response=$client->request('GET',$url);$http=$response->getStatusCode();$headers=$response->getHeaders(false);$xRobots=implode(', ',$headers['x-robots-tag']??[]);
            $body='';foreach($client->stream($response) as $chunk){$body.=$chunk->getContent();if(strlen($body)>2*1024*1024){$response->cancel();throw new \RuntimeException('Réponse trop volumineuse.');}}
            $document=new \DOMDocument();if(@$document->loadHTML($body)){foreach($document->getElementsByTagName('meta') as $meta)if(strtolower($meta->getAttribute('name'))==='robots')$robots=$meta->getAttribute('content');}
        }catch(\Throwable $e){$http=0;$failure='Contrôle HTTP non abouti ; visibilité à vérifier.';}
        $lock=fopen($this->file.'.lock','c');if(!$lock || !flock($lock,LOCK_EX))throw new \RuntimeException('Relevé indisponible.');
        $temporary=null;
        try{
            $data=json_decode(file_get_contents($this->file),true,512,JSON_THROW_ON_ERROR);
            $record=$data['records'][$url];$record['http']=$http;$record['robots']=$robots;$record['x_robots']=$xRobots;$record['checked_at']=$at;$record['probe_error']=$failure;
            $data['records'][$url]=$record;$data['last_manual_check_at']=$at;
            $temporary=tempnam(dirname($this->file),'cms-visibility-');chmod($temporary,0600);
            if(file_put_contents($temporary,json_encode($data,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR))===false || !rename($temporary,$this->file))throw new \RuntimeException('Enregistrement du relevé impossible.');
        }finally{if($temporary && is_file($temporary))unlink($temporary);flock($lock,LOCK_UN);fclose($lock);}
        return ['url'=>$url,'http'=>$http,'checked_at'=>$at,'error'=>$failure];
    }
}
