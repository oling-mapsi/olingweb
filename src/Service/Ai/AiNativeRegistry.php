<?php
namespace App\Service\Ai;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
final class AiNativeRegistry
{
    public const STATES=['draft'=>'À cadrer','requested'=>'À prendre en charge','associated'=>'Tâche native associée','external_running'=>'Exécution externe suivie','result_received'=>'Résultat reçu','under_review'=>'Revue en cours','correction_requested'=>'Correction demandée','ready_for_validation'=>'À valider','blocked'=>'Arbitrage requis','cancel_requested'=>'Arrêt externe demandé','cancelled'=>'Annulée','closed'=>'Clôturée'];
    public function __construct(private Connection $db,private AiStore $store,private AiArtifacts $artifacts) {}
    private static function json(array $v): string {return json_encode($v,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);}
    private static function canonical(array $v): array {if(!array_is_list($v))ksort($v);foreach($v as &$x)if(is_array($x))$x=self::canonical($x);return $v;}
    public function call(string $tool,array $args,int $owner,string $actor): array
    {
        AiTrackingContract::validate($tool,$args);
        if(!preg_match('/^[A-Za-z0-9_-]{1,80}$/D',$actor)||$owner<1)throw new \InvalidArgumentException('invalid_identity');
        if($tool==='ai_mission_get')return $this->detail($args['mission_id'],$owner);
        if($tool==='ai_missions_list') {
            $offset=$args['offset']??0;$limit=$args['limit']??25;
            $rows=$this->db->fetchAllAssociative('SELECT id,title,status,version,project,updated_at FROM ai_mission WHERE owner_id=? ORDER BY updated_at DESC,id LIMIT '.($limit+1).' OFFSET '.$offset,[$owner]);
            return ['missions'=>array_slice($rows,0,$limit),'next_offset'=>count($rows)>$limit?$offset+$limit:null];
        }
        return $this->once($owner,$actor,$tool,$args,function()use($tool,$args,$owner,$actor){
            if($tool==='ai_mission_create') {
                $id=$this->store->create($owner,$args['title'],$args['brief'],$args['project'],$args['event_id']);
                $this->db->update('ai_mission',['status'=>'requested','executor'=>'unassigned'],['id'=>$id]);
                return $this->snapshot($id,$owner);
            }
            $id=$args['mission_id'];
            // Lock before snapshot: portable serialization, plus optimistic version check.
            $this->db->executeStatement('UPDATE ai_mission SET version=version WHERE id=? AND owner_id=?',[$id,$owner]);
            $m=$this->store->mission($id,$owner);
            if((int)$m['version']!==$args['version'])throw new \DomainException('stale_version');
            $binding=$this->binding($id);
            $data=[];
            if($tool==='ai_mission_prompt') {
                AiWorkflow::assertState($m['status'],['draft','requested']);
                $p=$this->db->fetchAssociative('SELECT * FROM ai_prompt_version WHERE id=?',[$m['prompt_version_id']]);$v=bin2hex(random_bytes(16));
                $this->db->insert('ai_prompt_version',['id'=>$v,'prompt_id'=>$p['prompt_id'],'content'=>$args['content'],'content_hash'=>hash('sha256',$args['content']),'created_at'=>time()]);
                $this->change($m,'requested',['prompt_version_id'=>$v]);
            } elseif($tool==='ai_task_associate') {
                AiWorkflow::assertState($m['status'],['requested','correction_requested']);
                if($binding&&$binding['executor']!==$actor)throw new \DomainException('execution_owner_conflict');
                $url=AiTrackingContract::proofUrl($args['task_url'],$args['task_id']);
                if(!in_array($url,['codex://threads/'.$args['task_id'],'https://chatgpt.com/codex/tasks/'.$args['task_id']],true))throw new \InvalidArgumentException('invalid_task_url');
                $values=['task_id'=>$args['task_id'],'task_url'=>$url,'executor'=>$actor];
                if($binding)$this->db->update('ai_native_binding',$values,['mission_id'=>$id]);else $this->db->insert('ai_native_binding',['mission_id'=>$id]+$values);
                $run=bin2hex(random_bytes(16));
                $this->db->insert('ai_run',['id'=>$run,'mission_id'=>$id,'role'=>(int)$m['corrections']?'correct':'execute','model'=>$args['model'],'effort'=>$args['effort'],'status'=>'associated','executor'=>$actor,'thread_id'=>$args['task_id'],'created_at'=>time(),'request_key'=>hash('sha256','native-run:'.$id.':'.$args['event_id']),'ordinal'=>1+(int)$this->db->fetchOne('SELECT MAX(ordinal) FROM ai_run WHERE mission_id=?',[$id])]);
                $this->change($m,'associated',['executor'=>$actor]);$data=['run_id'=>$run,'task_id'=>$args['task_id'],'task_reference_source'=>'operator_declared_not_platform_verified'];
            } elseif($tool==='ai_task_event') {
                $r=$this->run($m,$binding,$actor,$args['run_id']);
                if($args['event']==='started') {AiWorkflow::assertState($m['status'],['associated']);$state='external_running';$runStatus='external_running';}
                else {AiWorkflow::assertState($m['status'],['associated','external_running','cancel_requested']);$state=$m['status']==='cancel_requested'?'cancelled':'blocked';$runStatus='interrupted';}
                $this->db->update('ai_run',['status'=>$runStatus],['id'=>$r['id']]);$this->change($m,$state);$data=['run_id'=>$r['id'],'event'=>$args['event'],'source'=>'executor_report'];
            } elseif($tool==='ai_task_result') {
                $r=$this->run($m,$binding,$actor,$args['run_id']);AiWorkflow::assertState($m['status'],['associated','external_running']);
                foreach($args['proofs'] as $proof)AiTrackingContract::proofUrl($proof['url'],$binding['task_id']);
                $output=self::json(['summary'=>$args['summary'],'proofs'=>$args['proofs'],'checks'=>$args['checks'],'verification'=>'executor_report_not_independently_verified']);
                if(strlen($output)>60000)throw new \InvalidArgumentException('result_too_large');
                $asset=$this->artifacts->put($output);
                $this->db->insert('ai_asset',$asset+['id'=>bin2hex(random_bytes(16)),'mission_id'=>$id,'run_id'=>$r['id'],'title'=>'Résultat externe — '.$m['title'],'created_at'=>time()]);
                $this->db->update('ai_run',['status'=>'completed','output'=>$output],['id'=>$r['id']]);$this->change($m,'result_received');$data=['run_id'=>$r['id'],'source'=>'executor_report','proof_count'=>count($args['proofs'])];
            } elseif($tool==='ai_mission_review') {
                $r=$this->run($m,$binding,$actor,$args['run_id']);AiWorkflow::assertState($m['status'],['result_received','under_review']);
                $this->decision($m,$r,$args['verdict'],$args['feedback'],0,false);$data=['run_id'=>$r['id'],'verdict'=>$args['verdict'],'source'=>'external_review_not_human_validation','task_reference_source'=>'operator_declared_not_platform_verified','review_task_id'=>$args['review_task_id'],'model'=>$args['model'],'effort'=>$args['effort']];
            }
            $this->event($id,$tool,$actor,$data);return $this->snapshot($id,$owner);
        });
    }
    private function once(int $owner,string $actor,string $tool,array $args,callable $work): array
    {
        $key=hash('sha256','native:'.$owner.':'.$args['event_id']);
        $hash=hash('sha256',self::json(self::canonical(['actor'=>$actor,'tool'=>$tool,'args'=>$args])));
        try {
            return $this->db->transactional(function()use($key,$hash,$owner,$work){
                $this->db->insert('ai_tracking_receipt',['operation_key'=>$key,'owner_id'=>$owner,'payload_hash'=>$hash,'response'=>'{}','created_at'=>time()]);
                $result=$work();$this->db->update('ai_tracking_receipt',['response'=>self::json($result)],['operation_key'=>$key]);return $result;
            });
        } catch(UniqueConstraintViolationException) {
            $old=$this->db->fetchAssociative('SELECT * FROM ai_tracking_receipt WHERE operation_key=? AND owner_id=?',[$key,$owner]);
            if(!$old||!hash_equals($old['payload_hash'],$hash))throw new \DomainException('idempotency_or_task_conflict');
            return json_decode($old['response'],true,32,JSON_THROW_ON_ERROR);
        }
    }
    private function binding(string $id): ?array {return $this->db->fetchAssociative('SELECT * FROM ai_native_binding WHERE mission_id=?',[$id])?:null;}
    private function run(array $m,?array $b,string $actor,string $run): array
    {
        if(!$b||$b['executor']!==$actor)throw new \DomainException('execution_owner_conflict');
        $r=$this->db->fetchAssociative('SELECT * FROM ai_run WHERE mission_id=? ORDER BY ordinal DESC',[$m['id']]);
        if(!$r||$r['id']!==$run)throw new \DomainException('stale_run');return $r;
    }
    private function snapshot(string $id,int $owner): array
    {
        $m=$this->store->mission($id,$owner);$run=$this->db->fetchOne('SELECT id FROM ai_run WHERE mission_id=? ORDER BY ordinal DESC',[$id]);
        return ['mission_id'=>$id,'version'=>(int)$m['version'],'status'=>$m['status'],'run_id'=>$run?:null,'execution_triggered'=>false];
    }
    private function change(array $m,string $state,array $extra=[]): void
    {
        if($this->db->update('ai_mission',$extra+['status'=>$state,'version'=>(int)$m['version']+1,'updated_at'=>time()],['id'=>$m['id'],'version'=>$m['version']])!==1)throw new \DomainException('concurrent_change');
    }
    private function event(string $id,string $kind,string $actor,array $data=[]): void
    {
        $this->db->insert('ai_event',['id'=>bin2hex(random_bytes(16)),'mission_id'=>$id,'kind'=>$kind,'data'=>self::json(['actor'=>$actor]+$data),'created_at'=>time(),'ordinal'=>1+(int)$this->db->fetchOne('SELECT MAX(ordinal) FROM ai_event WHERE mission_id=?',[$id])]);
    }
    public function detail(string $id,int $owner): array
    {
        $d=$this->store->detail($id,$owner);$d['binding']=$this->binding($id);
        foreach($d['assets'] as &$a)unset($a['storage_key']);
        foreach($d['runs'] as &$r) {
            unset($r['request_key']);$r['report']=null;
            try {$report=json_decode($r['output']??'',true,16,JSON_THROW_ON_ERROR);}catch(\JsonException){$report=null;}
            if(is_array($report)&&($report['verification']??null)==='executor_report_not_independently_verified') {
                foreach($report['proofs']??[] as $proof)AiTrackingContract::proofUrl($proof['url'],$r['thread_id']);
                $r['report']=$report;
            }
        }
        foreach($d['events'] as &$e)unset($e['operation_key']);
        $d['prompt']=$this->db->fetchAssociative('SELECT content,content_hash,created_at FROM ai_prompt_version WHERE id=?',[$d['mission']['prompt_version_id']]);
        return $d;
    }
    /** Human UI only, protected by owner/admin checks and CSRF in the controller. */
    public function human(string $id,int $owner,int $version,string $key,string $action,string $feedback='',bool $verified=false): void
    {
        AiTrackingContract::safeText($feedback);
        if(!preg_match('/^[A-Za-z0-9_-]{12,80}$/D',$key)||strlen($feedback)>10000)throw new \InvalidArgumentException('invalid_human_request');
        $args=['mission_id'=>$id,'version'=>$version,'event_id'=>$key,'action'=>$action,'feedback'=>$feedback,'verified'=>$verified];
        $this->once($owner,'human-'.$owner,'human',$args,function()use($id,$owner,$version,$action,$feedback,$verified){
            $this->db->executeStatement('UPDATE ai_mission SET version=version WHERE id=? AND owner_id=?',[$id,$owner]);$m=$this->store->mission($id,$owner);
            if((int)$m['version']!==$version)throw new \DomainException('stale_version');
            $r=$this->db->fetchAssociative('SELECT * FROM ai_run WHERE mission_id=? ORDER BY ordinal DESC',[$id]);
            if($action==='request') {AiWorkflow::assertState($m['status'],['draft']);$this->change($m,'requested',['executor'=>'unassigned']);}
            elseif($action==='cancel') {
                AiWorkflow::assertState($m['status'],['draft','requested','associated','external_running','result_received','under_review','correction_requested','ready_for_validation','blocked']);
                $this->change($m,in_array($m['status'],['associated','external_running'],true)?'cancel_requested':'cancelled');
            } elseif($action==='review_begin') {AiWorkflow::assertState($m['status'],['result_received']);$this->change($m,'under_review');}
            elseif(in_array($action,['approve','correct','blocked'],true)) {
                if(!$r)throw new \DomainException('no_result');
                AiWorkflow::assertState($m['status'],['result_received','under_review','ready_for_validation','blocked']);
                if($action==='approve'&&(!$verified||$m['status']!=='ready_for_validation'))throw new \DomainException('human_verification_required');
                $this->decision($m,$r,$action,$feedback,$owner,$verified);
            }else throw new \InvalidArgumentException('invalid_action');
            $this->event($id,'human_'.$action,'human-'.$owner,['verified_by_human'=>$action==='approve'&&$verified]);return $this->snapshot($id,$owner);
        });
    }
    private function decision(array $m,array $run,string $verdict,string $feedback,int $reviewer,bool $verified): void
    {
        if(trim($feedback)===''||strlen($feedback)>10000)throw new \InvalidArgumentException('review_feedback_required');
        if($run['status']!=='completed')throw new \DomainException('result_required');
        $extra=[];
        if($verdict==='correct') {
            if((int)$m['corrections']>=2)throw new \DomainException('correction_limit_arbitration_required');
            $state='correction_requested';$extra=['corrections'=>(int)$m['corrections']+1];
        }elseif($verdict==='approve') {
            $state=$reviewer>0&&$verified?'closed':'ready_for_validation';
            if(!$this->db->fetchOne('SELECT id FROM ai_asset WHERE run_id=?',[$run['id']]))throw new \DomainException('proof_required');
            if($state==='closed')$extra=['closure'=>$feedback];
        }else $state='blocked';
        $this->db->insert('ai_review',['id'=>bin2hex(random_bytes(16)),'mission_id'=>$m['id'],'run_id'=>$run['id'],'verdict'=>$verdict,'feedback'=>$feedback,'reviewer_id'=>$reviewer,'created_at'=>time()]);
        $this->change($m,$state,$extra);
    }
}
