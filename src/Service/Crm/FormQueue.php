<?php
namespace App\Service\Crm;

use Doctrine\DBAL\Connection;

/** Production-only durable queue; existing OLING receipts are also a recovery source. */
final class FormQueue
{
    public function __construct(private CrmConfig $config,private FormIntake $intake,private CrmStore $store,private Connection $db) {}
    public function enqueue(array $p): array
    {
        if (!$this->config->enabled('forms')) { return ['disabled'=>true]; }
        FormIntake::source($p);
        $dir=$this->config->get('forms_dir','/var/lib/oling-crm/forms');
        $file=$dir.'/'.hash('sha256',$p['site'].'|'.$p['reference']).'.json';
        if (!is_file($file)) {
            $tmp=tempnam($dir,'queue-'); if ($tmp===false) { throw new \RuntimeException('File formulaire indisponible.'); }
            chmod($tmp,0600);
            if (file_put_contents($tmp,CrmStore::json($p),LOCK_EX)===false || !rename($tmp,$file)) { @unlink($tmp); throw new \RuntimeException('File formulaire indisponible.'); }
        }
        try { $result=$this->intake->receive($p); @unlink($file); return $result; }
        catch (\Throwable) { return ['pending'=>true]; }
    }
    public function flush(): array
    {
        if (!$this->config->enabled('forms')) { return ['disabled'=>true]; }
        $dir=$this->config->get('forms_dir','/var/lib/oling-crm/forms'); $done=0; $failed=0;
        foreach(array_slice(glob($dir.'/*.json')?:[],0,200) as $file) {
            try { $p=json_decode(file_get_contents($file),true,32,JSON_THROW_ON_ERROR); $this->intake->receive($p); unlink($file); ++$done; }
            catch (\Throwable) { ++$failed; }
        }
        // A saved website receipt survives even an outage while writing the queue.
        $state=$this->store->state('forms'); $cursor=(int)($state['oling_cursor']??0);
        foreach($this->db->fetchAllAssociative('SELECT * FROM messagerie WHERE id>? ORDER BY id ASC LIMIT 100',[$cursor]) as $m) {
            if (!empty($m['submission_id'])) {
                try { $this->intake->receive(self::olingPayload($m)); }
                catch (\Throwable) { ++$failed; break; }
            }
            $cursor=(int)$m['id'];
        }
        // Separate cutover cursor: do not silently import old chat leads after a client reset.
        $chat=$this->store->state('chat_forms');
        if (array_key_exists('cursor',$chat)) {
            $chatCursor=(int)$chat['cursor'];$chatFailed=0;
            foreach ($this->db->fetchAllAssociative('SELECT l.*,c.source_path,c.source_url,c.submitted_at FROM chat_lead l JOIN chat_conversation c ON c.id=l.conversation_id WHERE l.id>? ORDER BY l.id ASC LIMIT 100',[$chatCursor]) as $lead) {
                if ($lead['rgpd_consent'] && $lead['submitted_at']) {
                    try { $this->intake->receive(self::chatPayload($lead)); }
                    catch (\Throwable) { ++$chatFailed;break; }
                }
                $chatCursor=(int)$lead['id'];
            }
            $this->store->setState('chat_forms',['cursor'=>$chatCursor,'last_attempt'=>CrmStore::now(),'pending'=>$chatFailed]);
            $failed+=$chatFailed;
        }
        $notificationPending=0; foreach(glob('/var/lib/oling-crm/mapsi-receipts/*.json')?:[] as $file) { $receipt=json_decode(file_get_contents($file),true); if (($receipt['notification']??'')!=='sent') { ++$notificationPending; } }
        $state=['mapsi_notification_pending'=>$notificationPending,'last_success'=>CrmStore::now(),'pending'=>count(glob($dir.'/*.json')?:[])+$failed,'delivered'=>$done,'oling_cursor'=>$cursor];
        $this->store->setState('forms',$state); return $state;
    }
    public static function chatPayload(array $lead): array
    {
        if (empty($lead['id']) || empty($lead['rgpd_consent'])) { throw new \InvalidArgumentException('Lead confirmé requis.'); }
        return ['site'=>'OLING','reference'=>'chat-lead-'.$lead['id'],'fields'=>[
            'contact'=>$lead['full_name'],'email'=>$lead['email'],'phone'=>$lead['phone']??'',
            'organization'=>$lead['company'],'summary'=>$lead['need_description'],'form_name'=>'chat_contact',
            'received_at'=>$lead['created_at'],'attribution'=>CrmStore::json(['source_path'=>$lead['source_path']??'','source_url'=>$lead['source_url']??''])
        ]];
    }
    public static function olingPayload(array $m,array $attribution=[]): array
    {
        return ['site'=>'OLING','reference'=>$m['submission_id'],'fields'=>['first_name'=>$m['first_name'],'last_name'=>$m['last_name'],'email'=>$m['work_email'],'organization'=>$m['company'],'summary'=>$m['details'],'form_name'=>'contact','received_at'=>$m['created_at']??'','attribution'=>$attribution?CrmStore::json($attribution):'']];
    }
}
