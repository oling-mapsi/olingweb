<?php
namespace App\Service\Crm;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

final class CrmSync
{
    public function __construct(private CrmConfig $config,private CrmStore $store,private GraphMailbox $graph,private EmailExtractor $extractor, #[Autowire('%kernel.project_dir%')] private string $projectDir) {}
    public function run(?string $since=null): array
    {
        if (!$this->config->graphReady()) { throw new \RuntimeException('Microsoft 365 désactivé ou non connecté.'); }
        $lock=(new LockFactory(new FlockStore($this->config->get('lock_dir','/var/lib/oling-crm'))))->createLock('email-sync');
        if (!$lock->acquire()) { return ['busy'=>true]; }
        $state=$this->store->state('email'); $count=0;
        try {
            if ($since!==null && !preg_match('/^\d{4}-\d{2}-\d{2}$/D',$since)) { throw new \InvalidArgumentException('Utiliser --since=AAAA-MM-JJ.'); }
            if ($since!==null) { $state=[]; }
            $start=$state['run_started']??CrmStore::now();
            $initial=empty($state['watermark']);
            $pass=$state['pass']??1;
            $historyFrom=$since ? $since.'T00:00:00Z' : ($state['history_from']??gmdate('Y-m-d\TH:i:s\Z',strtotime('-12 months')));
            $from=$since ? $since.'T00:00:00Z' : ($state['watermark']??gmdate('Y-m-d\TH:i:s\Z',strtotime('-12 months')));
            $mailbox=$this->config->get('mailbox');
            $url=$state['next']??('https://graph.microsoft.com/v1.0/users/'.rawurlencode($mailbox).'/messages?'.http_build_query(['$filter'=>'lastModifiedDateTime ge '.$from,'$top'=>50,'$select'=>'id,internetMessageId,conversationId,subject,body,from,toRecipients,webLink,isDraft,receivedDateTime,sentDateTime,internetMessageHeaders']));
            $token=$this->graph->token();
            $state=['pass'=>$pass,'history_from'=>$historyFrom,'run_started'=>$start,'next'=>$url,'watermark'=>$state['watermark']??null,'last_success'=>$state['last_success']??null,'error'=>null];
            // Bounded work; durable page cursor resumes without losing any page.
            for ($page=0;$page<30;++$page) {
                $result=$this->graph->page($url,$token);
                foreach ($result['value']??[] as $message) {
                    $date=max($message['receivedDateTime']??'',$message['sentDateTime']??'');
                    if ($date < $historyFrom) { continue; }
                    $s=$this->extractor->extract($message,$mailbox);
                    if ($s && ($s['commercial'] || $this->store->hasIdentity('email',$s['identity']))) { $r=$this->store->ingest($s); $count+=!$r['duplicate']; }
                }
                $url=$result['@odata.nextLink']??null;
                $state['next']=$url; $state['processed']=($state['processed']??0)+count($result['value']??[]); $state['updated_at']=CrmStore::now();
                if (!$url && $initial && $pass===1) {
                    $state=['pass'=>2,'history_from'=>$historyFrom,'watermark'=>null,'run_started'=>$start,'next'=>'https://graph.microsoft.com/v1.0/users/'.rawurlencode($mailbox).'/messages?'.http_build_query(['$filter'=>'lastModifiedDateTime ge '.$historyFrom,'$top'=>50,'$select'=>'id,internetMessageId,conversationId,subject,body,from,toRecipients,webLink,isDraft,receivedDateTime,sentDateTime,internetMessageHeaders'])];
                    $this->store->setState('email',$state); return ['new_events'=>$count,'pending'=>true,'pass'=>2];
                }
                if (!$url) { $state=['history_from'=>$historyFrom,'last_success'=>CrmStore::now(),'watermark'=>gmdate('Y-m-d\TH:i:s\Z',strtotime($start)-300),'error'=>null,'processed'=>$state['processed']]; }
                $this->store->setState('email',$state);
                if (!$url) { break; }
            }
            $this->store->setState('sync_request',[]);
            return ['new_events'=>$count,'pending'=>(bool)$url];
        } catch (\Throwable $e) {
            // Store a sanitized operational error, never OAuth responses or email bodies.
            $state['error']=str_starts_with($e->getMessage(),'Microsoft ') ? mb_substr($e->getMessage(),0,220) : 'Échec du traitement ; vérifier la configuration et relancer.';
            $state['last_attempt']=CrmStore::now(); $this->store->setState('email',$state); throw $e;
        } finally { $lock->release(); }
    }
}
