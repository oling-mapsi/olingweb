<?php
namespace App\Service\Crm;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GraphMailbox
{
    public function __construct(private HttpClientInterface $http, private CrmConfig $config) {}
    public function token(): string
    {
        $c=$this->config;
        if (!$c->graphReady()) { throw new \RuntimeException('Connexion Microsoft 365 non configurée ou désactivée.'); }
        $body=['client_id'=>$c->get('client_id')];
        $file=$c->get('oauth_file','/var/lib/oling-crm/oauth.json');
        if (is_readable($file)) {
            $saved=json_decode(file_get_contents($file),true,32,JSON_THROW_ON_ERROR);
            $body+=['grant_type'=>'refresh_token','refresh_token'=>$saved['refresh_token'],'scope'=>'https://graph.microsoft.com/Mail.Read offline_access'];
        } else { $body+=['grant_type'=>'client_credentials','client_secret'=>$c->get('client_secret'),'scope'=>'https://graph.microsoft.com/.default']; }
        $r=$this->http->request('POST','https://login.microsoftonline.com/'.rawurlencode($c->get('tenant_id')).'/oauth2/v2.0/token',['body'=>$body,'timeout'=>30,'max_duration'=>45,'max_redirects'=>0]);
        if ($r->getStatusCode()!==200) { throw new \RuntimeException('Microsoft OAuth : accès refusé ('.$r->getStatusCode().'). Reconnecter Microsoft 365.'); }
        $data=$r->toArray();
        if (!empty($data['refresh_token'])) {
            $tmp=$file.'.tmp'; file_put_contents($tmp,json_encode(['refresh_token'=>$data['refresh_token']],JSON_THROW_ON_ERROR),LOCK_EX); chmod($tmp,0600); rename($tmp,$file);
        }
        return $data['access_token'];
    }
    public function page(string $url,string $token): array
    {
        // Never forward bearer tokens to a nextLink outside the fixed Graph origin.
        if (!str_starts_with($url,'https://graph.microsoft.com/v1.0/')) { throw new \RuntimeException('Pagination Microsoft invalide.'); }
        $r=$this->http->request('GET',$url,['headers'=>['Authorization'=>'Bearer '.$token,'Prefer'=>'IdType="ImmutableId", outlook.body-content-type="text"'],'timeout'=>30,'max_duration'=>60,'max_redirects'=>0]);
        if ($r->getStatusCode()!==200) { throw new \RuntimeException('Microsoft Graph HTTP '.$r->getStatusCode().'. Une prochaine exécution reprendra la synchronisation.'); }
        return $r->toArray();
    }
}
