<?php
namespace App\Tests\Ai;
use App\Service\Ai\{AiSchema,AiTrackingSchema,AiStore,AiArtifacts,AiNativeRegistry,AiTrackingContract};
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;
final class AiNativeRegistryTest extends TestCase
{
    private $db;private AiNativeRegistry $r;private string $dir;
    protected function setUp():void
    {
        $this->dir=sys_get_temp_dir().'/ai-native-'.bin2hex(random_bytes(6));mkdir($this->dir,0700);
        $this->db=DriverManager::getConnection(['driver'=>'pdo_sqlite','path'=>$this->dir.'/fixture.sqlite']);$this->db->executeStatement('PRAGMA busy_timeout=3000');
        $this->db->executeStatement('CREATE TABLE app_user (id INTEGER PRIMARY KEY)');foreach([1,2] as $id)$this->db->insert('app_user',['id'=>$id]);
        $s=new Schema();AiSchema::add($s);AiTrackingSchema::add($s);foreach($s->toSql($this->db->getDatabasePlatform()) as $sql)$this->db->executeStatement($sql);
        $a=new AiArtifacts($this->dir);$this->r=new AiNativeRegistry($this->db,new AiStore($this->db,$a),$a);
    }
    protected function tearDown():void
    {$this->db->close();$i=new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->dir,\FilesystemIterator::SKIP_DOTS),\RecursiveIteratorIterator::CHILD_FIRST);foreach($i as $p)$p->isDir()?rmdir($p->getPathname()):unlink($p->getPathname());rmdir($this->dir);}
    private function create(string $key='native-create-fixture'):array {return $this->r->call('ai_mission_create',['event_id'=>$key,'title'=>'Native fixture','brief'=>'Synthetic only','project'=>'demo'],1,'pilot-one');}
    private function associate(array $m,string $key='native-associate-fixture',string $actor='pilot-one'):array
    {return $this->r->call('ai_task_associate',['mission_id'=>$m['mission_id'],'version'=>$m['version'],'event_id'=>$key,'task_id'=>'fixture-native-task','task_url'=>'codex://threads/fixture-native-task','model'=>'fixture-model','effort'=>'medium'],1,$actor);}
    private function result(array $m,string $key):array
    {return $this->r->call('ai_task_result',['mission_id'=>$m['mission_id'],'version'=>$m['version'],'event_id'=>$key,'run_id'=>$m['run_id'],'summary'=>'<script>untrusted result</script>','proofs'=>[['label'=>'Synthetic fixture reference','url'=>'https://github.com/example/fixture/commit/abc']],'checks'=>[['label'=>'Reported check','outcome'=>'passed']]],1,'pilot-one');}
    private function review(array $m,string $key,string $verdict):array
    {return $this->r->call('ai_mission_review',['mission_id'=>$m['mission_id'],'version'=>$m['version'],'event_id'=>$key,'run_id'=>$m['run_id'],'review_task_id'=>'fixture-review-task','model'=>'fixture-review-model','effort'=>'high','verdict'=>$verdict,'feedback'=>'Synthetic review, not a live Codex result.'],1,'pilot-one');}
    public function testFullTrackingCorrectionAndHumanClosure():void
    {
        $m=$this->create();self::assertSame('requested',$m['status']);self::assertFalse($m['execution_triggered']);self::assertSame($m,$this->create());
        $a=$this->associate($m);self::assertSame('associated',$a['status']);self::assertSame($a,$this->associate($m));
        $start=['mission_id'=>$a['mission_id'],'version'=>$a['version'],'event_id'=>'native-start-fixture','run_id'=>$a['run_id'],'event'=>'started'];
        $m=$this->r->call('ai_task_event',$start,1,'pilot-one');self::assertSame('external_running',$m['status']);
        $previous=$m;$m=$this->result($m,'native-result-fixture');self::assertSame($m,$this->result($previous,'native-result-fixture'));
        $d=$this->r->detail($m['mission_id'],1);self::assertCount(1,$d['assets']);self::assertArrayNotHasKey('storage_key',$d['assets'][0]);
        self::assertStringContainsString('executor_report_not_independently_verified',$d['runs'][0]['output']);
        $m=$this->review($m,'native-review-correct','correct');self::assertSame('correction_requested',$m['status']);
        $m=$this->associate($m,'native-associate-correct');$m=$this->result($m,'native-result-correct');$m=$this->review($m,'native-review-approve','approve');self::assertSame('ready_for_validation',$m['status']);
        try{$this->r->human($m['mission_id'],1,$m['version'],'native-human-no-proof','approve','Checked',false);self::fail();}catch(\DomainException){self::assertTrue(true);}
        $this->r->human($m['mission_id'],1,$m['version'],'native-human-close','approve','Human fixture review of the linked proof.',true);
        $d=$this->r->detail($m['mission_id'],1);self::assertSame('closed',$d['mission']['status']);self::assertSame(1,(int)$d['mission']['corrections']);self::assertCount(2,$d['runs']);
        self::assertStringContainsString('verified_by_human',json_encode($d['events']));
    }
    public function testOwnerStaleReplayConflictsAndSingleExecutor():void
    {
        $m=$this->create();
        try{$this->r->detail($m['mission_id'],2);self::fail();}catch(\OutOfBoundsException){self::assertTrue(true);}
        try{$this->r->call('ai_mission_create',['event_id'=>'native-create-fixture','title'=>'Different','brief'=>'Synthetic only','project'=>'demo'],1,'pilot-one');self::fail();}catch(\DomainException){self::assertTrue(true);}
        $a=$this->associate($m);
        try{$this->associate($m,'native-stale-associate');self::fail();}catch(\DomainException){self::assertTrue(true);}
        try{$this->r->call('ai_task_event',['mission_id'=>$a['mission_id'],'version'=>$a['version'],'event_id'=>'native-foreign-event','run_id'=>$a['run_id'],'event'=>'started'],1,'pilot-two');self::fail();}catch(\DomainException){self::assertTrue(true);}
        self::assertCount(1,$this->r->detail($m['mission_id'],1)['runs']);
    }
    public function testCancellationDoesNotPretendToStopExternalTask():void
    {
        $m=$this->associate($this->create());$this->r->human($m['mission_id'],1,$m['version'],'native-human-cancel','cancel');
        $d=$this->r->detail($m['mission_id'],1);self::assertSame('cancel_requested',$d['mission']['status']);
        $done=$this->r->call('ai_task_event',['mission_id'=>$m['mission_id'],'version'=>(int)$d['mission']['version'],'event_id'=>'native-stop-confirmed','run_id'=>$m['run_id'],'event'=>'stopped'],1,'pilot-one');self::assertSame('cancelled',$done['status']);
        $this->expectException(\DomainException::class);$this->result($done,'native-late-result');
    }
    public function testValidationRejectsSecretsUnsafeUrlsUnknownFieldsAndFalseVerification():void
    {
        $m=$this->associate($this->create());
        $base=['mission_id'=>$m['mission_id'],'version'=>$m['version'],'event_id'=>'native-invalid-result','run_id'=>$m['run_id'],'summary'=>'fixture','proofs'=>[['label'=>'Proof','url'=>'https://github.com/example/fixture']],'checks'=>[]];
        $cases=[$base+['verified'=>true],array_replace($base,['summary'=>'Bearer SYNTHETIC_CREDENTIAL_0123456789']),array_replace($base,['summary'=>str_repeat('x',20001)]),array_replace($base,['proofs'=>[['label'=>'bad','url'=>'javascript:alert(1)']]]),array_replace($base,['proofs'=>[['label'=>'bad','url'=>'https://github.com/example?token=demo']]]),array_replace($base,['checks'=>[['label'=>'false claim','outcome'=>'verified']]])];
        foreach($cases as $args){try{$this->r->call('ai_task_result',$args,1,'pilot-one');self::fail();}catch(\InvalidArgumentException){self::assertTrue(true);}}
        self::assertCount(0,$this->r->detail($m['mission_id'],1)['assets']);self::assertStringNotContainsString('SYNTHETIC_CREDENTIAL',json_encode($this->db->fetchAllAssociative('SELECT * FROM ai_event')));
    }
    public function testCorrectionLimitAndStaleRun():void
    {
        $m=$this->create();$old=null;
        for($n=0;$n<3;$n++) {
            $m=$this->associate($m,'native-associate-limit-'.$n);if($n===0)$old=$m['run_id'];
            $m=$this->result($m,'native-result-limit-'.$n);
            if($n<2)$m=$this->review($m,'native-review-limit-'.$n,'correct');
        }
        try{$this->review($m,'native-third-correct','correct');self::fail();}catch(\DomainException){self::assertTrue(true);}
        $this->expectException(\DomainException::class);$this->r->call('ai_mission_review',['mission_id'=>$m['mission_id'],'version'=>$m['version'],'run_id'=>$old,'event_id'=>'native-old-run-review','review_task_id'=>'fixture-review-task','model'=>'fixture-review-model','effort'=>'high','verdict'=>'approve','feedback'=>'stale'],1,'pilot-one');
    }
    public function testConcurrentAssociationsHaveOneWinner():void
    {
        if(!function_exists('pcntl_fork'))self::markTestSkipped('pcntl unavailable');$m=$this->create();$this->db->close();$children=[];
        foreach([1,2] as $n) {
            $pid=pcntl_fork();if($pid===0) {
                $db=DriverManager::getConnection(['driver'=>'pdo_sqlite','path'=>$this->dir.'/fixture.sqlite']);$db->executeStatement('PRAGMA busy_timeout=3000');$a=new AiArtifacts($this->dir);$registry=new AiNativeRegistry($db,new AiStore($db,$a),$a);
                try{$registry->call('ai_task_associate',['mission_id'=>$m['mission_id'],'version'=>1,'event_id'=>'concurrent-native-'.$n,'task_id'=>'task-'.$n,'task_url'=>'codex://threads/task-'.$n,'model'=>'fixture','effort'=>'low'],1,'pilot-'.$n);$result='accepted';}catch(\DomainException){$result='conflict';}
                file_put_contents($this->dir.'/result-'.$n,$result);exit(0);
            }if($pid<0)throw new \RuntimeException('fork failed');$children[]=$pid;
        }
        foreach($children as $pid){pcntl_waitpid($pid,$status);self::assertSame(0,pcntl_wexitstatus($status));}
        $results=[file_get_contents($this->dir.'/result-1'),file_get_contents($this->dir.'/result-2')];sort($results);self::assertSame(['accepted','conflict'],$results);
        self::assertSame(1,(int)$this->db->fetchOne('SELECT COUNT(*) FROM ai_native_binding'));self::assertSame(1,(int)$this->db->fetchOne('SELECT COUNT(*) FROM ai_run'));
    }
}
