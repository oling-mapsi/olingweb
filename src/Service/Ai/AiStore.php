<?php
namespace App\Service\Ai;
use Doctrine\DBAL\Connection;

final class AiStore
{
    public function __construct(private Connection $db, private AiArtifacts $artifacts) {}
    private static function id(): string {return bin2hex(random_bytes(16));}
    private static function json(array $data): string {return json_encode($data,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);}
    private static function key(string $key): string {if(!preg_match('/^[a-zA-Z0-9_-]{12,80}$/D',$key))throw new \InvalidArgumentException('Identifiant de requête invalide.');return hash('sha256',$key);}
    public function mission(string $id,int $owner): array
    {
        $m=$this->db->fetchAssociative('SELECT * FROM ai_mission WHERE id=? AND owner_id=?',[$id,$owner]);
        if(!$m)throw new \OutOfBoundsException('Mission introuvable.');return $m;
    }
    public function all(int $owner): array {return $this->db->fetchAllAssociative('SELECT * FROM ai_mission WHERE owner_id=? ORDER BY updated_at DESC,id',[$owner]);}
    public function prompts(int $owner): array {return $this->db->fetchAllAssociative('SELECT p.title,v.* FROM ai_prompt p JOIN ai_prompt_version v ON v.prompt_id=p.id WHERE p.owner_id=? ORDER BY v.created_at DESC,v.id',[$owner]);}
    public function assets(int $owner): array {return $this->db->fetchAllAssociative('SELECT a.* FROM ai_asset a JOIN ai_mission m ON m.id=a.mission_id WHERE m.owner_id=? ORDER BY a.created_at DESC,a.id',[$owner]);}
    public function detail(string $id,int $owner): array
    {
        return ['mission'=>$this->mission($id,$owner),'runs'=>$this->db->fetchAllAssociative('SELECT * FROM ai_run WHERE mission_id=? ORDER BY ordinal',[$id]),'events'=>$this->db->fetchAllAssociative('SELECT * FROM ai_event WHERE mission_id=? ORDER BY ordinal',[$id]),'reviews'=>$this->db->fetchAllAssociative('SELECT * FROM ai_review WHERE mission_id=? ORDER BY created_at,id',[$id]),'assets'=>$this->db->fetchAllAssociative('SELECT * FROM ai_asset WHERE mission_id=? ORDER BY created_at,id',[$id])];
    }
    public function asset(string $id,int $owner): array
    {
        $a=$this->db->fetchAssociative('SELECT a.* FROM ai_asset a JOIN ai_mission m ON m.id=a.mission_id WHERE a.id=? AND m.owner_id=?',[$id,$owner]);
        if(!$a)throw new \OutOfBoundsException('Livrable introuvable.');return $a;
    }
    private function event(string $id,string $kind,array $data,?string $operation=null): void
    {$this->db->insert('ai_event',['id'=>self::id(),'mission_id'=>$id,'kind'=>$kind,'data'=>self::json($data),'created_at'=>time(),'operation_key'=>$operation,'ordinal'=>1+(int)$this->db->fetchOne('SELECT MAX(ordinal) FROM ai_event WHERE mission_id=?',[$id])]);}
    private function change(array $m,string $state,array $extra=[]): void
    {
        $values=$extra+['status'=>$state,'version'=>(int)$m['version']+1,'updated_at'=>time()];
        if($this->db->update('ai_mission',$values,['id'=>$m['id'],'version'=>$m['version']])!==1)throw new \DomainException('Mission modifiée simultanément. Rechargez la page.');
    }
    public function create(int $owner,string $title,string $brief,string $project,string $key): string
    {
        $operation=hash('sha256',$owner.':create:'.self::key($key));
        return $this->db->transactional(function()use($owner,$title,$brief,$project,$operation){
            $old=$this->db->fetchOne('SELECT mission_id FROM ai_event WHERE operation_key=?',[$operation]);if($old)return $old;
            if(trim($title)===''||mb_strlen($title)>180||trim($brief)===''||strlen($brief)>20000||!preg_match('/^[a-z0-9][a-z0-9_-]{0,79}$/D',$project))throw new \InvalidArgumentException('Titre, cadrage et projet requis.');
            $id=self::id();$prompt=self::id();$version=self::id();
            $content="Objectif : ".$title."\nCadrage et critères de réussite :\n".$brief."\nLivrer le résultat et les preuves de vérification. Respecter le périmètre local autorisé. Aucun déploiement, push, accès production, lecture de secrets ou changement de sécurité.";
            $this->db->insert('ai_prompt',['id'=>$prompt,'title'=>$title,'owner_id'=>$owner]);
            $this->db->insert('ai_prompt_version',['id'=>$version,'prompt_id'=>$prompt,'content'=>$content,'content_hash'=>hash('sha256',$content),'created_at'=>time()]);
            $this->db->insert('ai_mission',['id'=>$id,'title'=>$title,'brief'=>$brief,'project'=>$project,'executor'=>'mac-local','status'=>'draft','prompt_version_id'=>$version,'owner_id'=>$owner,'version'=>1,'corrections'=>0,'created_at'=>time(),'updated_at'=>time()]);
            $this->event($id,'created',['actor'=>$owner],$operation);return $id;
        });
    }
    public function promptVersion(string $id,int $owner,int $version,string $content,string $key): void
    {
        $this->action($id,$owner,$version,$key,'prompt',function($m)use($content){
            AiWorkflow::assertState($m['status'],['draft']);
            if(trim($content)===''||strlen($content)>30000)throw new \InvalidArgumentException('Prompt vide ou trop long.');
            $p=$this->db->fetchAssociative('SELECT * FROM ai_prompt_version WHERE id=?',[$m['prompt_version_id']]);$v=self::id();
            $this->db->insert('ai_prompt_version',['id'=>$v,'prompt_id'=>$p['prompt_id'],'content'=>$content,'content_hash'=>hash('sha256',$content),'created_at'=>time()]);
            $this->change($m,'draft',['prompt_version_id'=>$v]);
        });
    }
    private function action(string $id,int $owner,int $version,string $key,string $kind,callable $work): void
    {
        $operation=hash('sha256',$owner.':'.$id.':'.$kind.':'.self::key($key));
        $this->db->transactional(function()use($id,$owner,$version,$operation,$kind,$work){
            $m=$this->mission($id,$owner);
            if($this->db->fetchOne('SELECT id FROM ai_event WHERE operation_key=?',[$operation]))return;
            if((int)$m['version']!==$version)throw new \DomainException('Version périmée. Rechargez la mission.');
            $work($m);$this->event($id,$kind,['actor'=>$owner],$operation);
        });
    }
    private function enqueue(array $m,string $role,string $requestKey): void
    {
        $policy=AiWorkflow::role($role);
        $this->db->insert('ai_run',['id'=>self::id(),'mission_id'=>$m['id'],'role'=>$role,'model'=>$policy['model'],'effort'=>$policy['effort'],'status'=>'queued','executor'=>$m['executor'],'created_at'=>time(),'request_key'=>$requestKey,'ordinal'=>1+(int)$this->db->fetchOne('SELECT MAX(ordinal) FROM ai_run WHERE mission_id=?',[$m['id']])]);
    }
    public function launch(string $id,int $owner,int $version,string $key): void
    {$this->action($id,$owner,$version,$key,'launch',function($m){AiWorkflow::assertState($m['status'],['draft']);$this->change($m,'queued');$this->enqueue($m,'execute',hash('sha256',$m['id'].':initial'));});}
    public function cancel(string $id,int $owner,int $version,string $key): void
    {
        $this->action($id,$owner,$version,$key,'cancel',function($m){
            AiWorkflow::assertState($m['status'],['draft','queued','running','reviewing','awaiting_review','blocked','interrupted']);
            $this->db->executeStatement("UPDATE ai_run SET status='cancel_requested' WHERE mission_id=? AND status='running'",[$m['id']]);
            $this->db->executeStatement("UPDATE ai_run SET status='cancelled' WHERE mission_id=? AND status='queued'",[$m['id']]);
            $this->change($m,'cancelled');
        });
    }
    public function review(string $id,int $owner,int $version,string $key,string $decision,string $feedback): void
    {
        $this->action($id,$owner,$version,$key,'review',function($m)use($owner,$decision,$feedback){
            AiWorkflow::assertState($m['status'],['awaiting_review','blocked','interrupted']);
            if(trim($feedback)===''||strlen($feedback)>10000)throw new \InvalidArgumentException('Motif et preuves requis.');
            $run=$this->db->fetchAssociative("SELECT * FROM ai_run WHERE mission_id=? ORDER BY ordinal DESC",[$m['id']]);
            if(!$run)throw new \DomainException('Aucune exécution à examiner.');
            // Never replay an uncertain external action until the previous process is reconciled.
            if($this->db->fetchOne("SELECT id FROM ai_run WHERE mission_id=? AND status IN ('running','cancel_requested','uncertain')",[$m['id']]))throw new \DomainException('Réconciliez d’abord l’exécution interrompue.');
            if($decision==='approve') {
                AiWorkflow::assertState($m['status'],['awaiting_review']);
                if(!$this->db->fetchOne('SELECT id FROM ai_asset WHERE mission_id=?',[$m['id']]))throw new \DomainException('Livrable absent.');
                $this->change($m,'closed',['closure'=>$feedback]);
            } elseif($decision==='correct') {
                if((int)$m['corrections']>=AiWorkflow::MAX_CORRECTIONS)throw new \DomainException('Deux cycles atteints : arbitrage, pas de relance automatique.');
                $this->change($m,'queued',['corrections'=>(int)$m['corrections']+1]);
                $this->enqueue($m,'correct',hash('sha256',$m['id'].':human:'.$m['version']));
            } else throw new \InvalidArgumentException('Décision invalide.');
            $this->db->insert('ai_review',['id'=>self::id(),'mission_id'=>$m['id'],'run_id'=>$run['id'],'verdict'=>$decision,'feedback'=>$feedback,'reviewer_id'=>$owner,'created_at'=>time()]);
        });
    }
    public function presence(int $owner,string $executor,int $now): void
    {
        // Atomic upsert acquires the executor row before claim reads (including MySQL REPEATABLE READ).
        // A preliminary SELECT would establish a stale snapshot and permit two different missions.
        $suffix=$this->db->getDatabasePlatform()->getName()==='mysql'
            ?' ON DUPLICATE KEY UPDATE seen_at=VALUES(seen_at)'
            :' ON CONFLICT(executor,owner_id) DO UPDATE SET seen_at=excluded.seen_at';
        $this->db->executeStatement('INSERT INTO ai_relay (id,executor,owner_id,seen_at) VALUES (?,?,?,?)'.$suffix,[self::id(),$executor,$owner,$now]);
    }
    public function online(int $owner): bool {return (int)$this->db->fetchOne('SELECT MAX(seen_at) FROM ai_relay WHERE owner_id=?',[$owner])>time()-45;}
    public function claim(int $owner,string $executor,array $projects,int $now): ?array
    {
        return $this->db->transactional(function()use($owner,$executor,$projects,$now){
            $this->presence($owner,$executor,$now);
            $expired=$this->db->fetchAllAssociative("SELECT r.*,m.version,m.status AS mission_status FROM ai_run r JOIN ai_mission m ON m.id=r.mission_id WHERE m.owner_id=? AND r.executor=? AND r.status IN ('running','cancel_requested') AND r.lease_until<?",[$owner,$executor,$now]);
            foreach($expired as $r)$this->expireRun($r,$owner,$now);
            // A single executor owns one active run at a time, including uncertain outcomes.
            if($this->db->fetchOne("SELECT r.id FROM ai_run r JOIN ai_mission m ON m.id=r.mission_id WHERE m.owner_id=? AND r.executor=? AND r.status IN ('running','cancel_requested','uncertain')",[$owner,$executor]))return null;
            $runs=$this->db->fetchAllAssociative("SELECT r.*,m.project FROM ai_run r JOIN ai_mission m ON m.id=r.mission_id WHERE m.owner_id=? AND r.executor=? AND r.status='queued' AND m.status IN ('queued','reviewing') ORDER BY r.created_at,r.id",[$owner,$executor]);
            foreach($runs as $r) {
                if(!in_array($r['project'],$projects,true))continue;
                if($this->db->update('ai_run',['status'=>'running','lease_until'=>$now+45,'heartbeat_at'=>$now],['id'=>$r['id'],'status'=>'queued'])!==1)continue;
                $m=$this->mission($r['mission_id'],$owner);$this->change($m,$r['role']==='review'?'reviewing':'running');
                $prompt=$this->db->fetchOne('SELECT content FROM ai_prompt_version WHERE id=?',[$m['prompt_version_id']]);
                $prior=$this->db->fetchAllAssociative("SELECT role,output FROM ai_run WHERE mission_id=? AND status='completed' ORDER BY ordinal",[$m['id']]);
                $notes=$this->db->fetchFirstColumn('SELECT feedback FROM ai_review WHERE mission_id=? ORDER BY created_at,id',[$m['id']]);
                $this->event($m['id'],'claimed',['run_id'=>$r['id'],'executor'=>$executor,'model'=>$r['model'],'effort'=>$r['effort']]);
                return $r+['prompt'=>$prompt,'prior'=>$prior,'feedback'=>$notes];
            }return null;
        });
    }
    private function ownedRun(string $id,int $owner,string $executor): array
    {
        $r=$this->db->fetchAssociative('SELECT r.* FROM ai_run r JOIN ai_mission m ON m.id=r.mission_id WHERE r.id=? AND m.owner_id=? AND r.executor=?',[$id,$owner,$executor]);
        if(!$r)throw new \OutOfBoundsException('Exécution introuvable.');return $r;
    }
    /** Must run in a transaction. Expiry is persisted even if a later delivery is rejected. */
    private function expireRun(array $r,int $owner,int $now): bool
    {
        if(!in_array($r['status'],['running','cancel_requested'],true)||(int)$r['lease_until']>=$now)return false;
        if($this->db->update('ai_run',['status'=>'uncertain','failure'=>'Heartbeat expiré ; vérifier le processus avant reprise.'],['id'=>$r['id'],'status'=>$r['status'],'lease_until'=>$r['lease_until']])!==1)return false;
        $m=$this->mission($r['mission_id'],$owner);
        if($m['status']!=='cancelled')$this->change($m,'interrupted');
        $this->event($m['id'],'lease_expired',['run_id'=>$r['id']]);return true;
    }
    public function heartbeat(string $id,int $owner,string $executor,int $now,?string $thread=null,?string $turn=null): string
    {
        return $this->db->transactional(function()use($id,$owner,$executor,$now,$thread,$turn){
            $this->presence($owner,$executor,$now);$r=$this->ownedRun($id,$owner,$executor);
            if($this->expireRun($r,$owner,$now))return 'uncertain';
            if($r['status']==='running') {
                $values=['lease_until'=>$now+45,'heartbeat_at'=>$now];
                foreach(['thread_id'=>$thread,'turn_id'=>$turn] as $k=>$v)if($v!==null&&strlen($v)<=100)$values[$k]=$v;
                $this->db->update('ai_run',$values,['id'=>$id,'status'=>'running','lease_until'=>$r['lease_until']]);
            }
            return $this->ownedRun($id,$owner,$executor)['status'];
        });
    }
    public function finish(string $id,int $owner,string $executor,array $result,int $now): void
    {
        $this->db->transactional(function()use($id,$owner,$executor,$now){$this->presence($owner,$executor,$now);$this->expireRun($this->ownedRun($id,$owner,$executor),$owner,$now);});
        $this->db->transactional(function()use($id,$owner,$executor,$result,$now){
            $this->presence($owner,$executor,$now);
            $r=$this->ownedRun($id,$owner,$executor);if(in_array($r['status'],['completed','failed','denied','interrupted','cancelled'],true))return;
            $m=$this->mission($r['mission_id'],$owner);
            if($r['status']==='uncertain'||(int)$r['lease_until']<$now)throw new \DomainException('Bail expiré : résultat à réconcilier, aucune relance implicite.');
            $status=$result['status']??'failed';$output=(string)($result['output']??'');
            if(strlen($output)>1048576)throw new \InvalidArgumentException('Résultat trop volumineux.');
            if($r['status']==='cancel_requested'||$m['status']==='cancelled')$status='cancelled';
            elseif(!empty($result['approval']))$status='denied';
            if(!in_array($status,['completed','failed','denied','interrupted','cancelled'],true))throw new \InvalidArgumentException('Résultat invalide.');
            if($status==='completed'&&trim($output)==='')$status='failed';
            if($this->db->update('ai_run',['status'=>$status,'output'=>$output,'failure'=>$status==='completed'?null:'Exécution arrêtée : '.$status],['id'=>$id,'status'=>$r['status']])!==1)throw new \DomainException('Exécution modifiée simultanément.');
            $evidence=[];
            foreach(array_slice((array)($result['evidence']??[]),0,100) as $item) {
                if(!is_array($item))continue;
                $evidence[]=array_intersect_key($item,array_flip(['id','type','status','exitCode']));
            }
            $this->event($m['id'],'run_'.$status,['run_id'=>$id,'tool_evidence'=>$evidence]);
            if($status!=='completed') {if($m['status']!=='cancelled')$this->change($m,$status==='interrupted'?'interrupted':'blocked');return;}
            $asset=$this->artifacts->put($output);
            $this->db->insert('ai_asset',$asset+['id'=>self::id(),'mission_id'=>$m['id'],'run_id'=>$id,'title'=>$r['role'].' — '.$m['title'],'created_at'=>$now]);
            if($r['role']!=='review') {
                $this->change($m,'reviewing');$this->enqueue($m,'review',hash('sha256',$id.':review'));return;
            }
            try {$review=json_decode($output,true,16,JSON_THROW_ON_ERROR);} catch(\JsonException) {$review=[];}
            $verdict=$review['verdict']??null;
            if(!in_array($verdict,['approve','correct','blocked'],true)||!is_string($review['feedback']??null)||trim($review['feedback'])===''){$this->change($m,'blocked');$this->event($m['id'],'invalid_review',[]);return;}
            $this->db->insert('ai_review',['id'=>self::id(),'mission_id'=>$m['id'],'run_id'=>$id,'verdict'=>$verdict,'feedback'=>$review['feedback'],'reviewer_id'=>0,'created_at'=>$now]);
            if($verdict==='correct'&&(int)$m['corrections']<AiWorkflow::MAX_CORRECTIONS) {
                $this->change($m,'queued',['corrections'=>(int)$m['corrections']+1]);$this->enqueue($m,'correct',hash('sha256',$id.':correction'));
            } else $this->change($m,$verdict==='approve'?'awaiting_review':'blocked');
        });
    }
    public function reconcile(string $id,int $owner,int $version,string $key,string $proof): void
    {
        $this->action($id,$owner,$version,$key,'reconcile',function($m)use($proof){
            AiWorkflow::assertState($m['status'],['interrupted','cancelled']);
            if(strlen(trim($proof))<20||strlen($proof)>10000)throw new \InvalidArgumentException('Documentez le contrôle du processus arrêté et du dossier local.');
            $this->db->executeStatement("UPDATE ai_run SET status='interrupted',failure=? WHERE mission_id=? AND status='uncertain'",[$proof,$m['id']]);
            $this->change($m,$m['status']==='cancelled'?'cancelled':'blocked');$this->event($m['id'],'reconciliation_proof',['proof'=>$proof]);
        });
    }
}
