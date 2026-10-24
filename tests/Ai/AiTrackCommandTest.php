<?php
namespace App\Tests\Ai;

use App\Command\AiTrackCommand;
use App\Service\Ai\{AiArtifacts,AiNativeRegistry,AiSchema,AiStore,AiTrackingSchema};
use Doctrine\DBAL\{Connection,DriverManager};
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class AiTrackCommandTest extends TestCase
{
    private const PILOT = '00000000-0000-0000-0000-000000000001';
    private const TASK = '00000000-0000-0000-0000-000000000010';
    private const REVIEW = '00000000-0000-0000-0000-000000000020';
    private Connection $db;
    private AiTrackCommand $command;
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir().'/ai-cli-'.bin2hex(random_bytes(6));
        mkdir($this->dir,0700);
        $this->db = DriverManager::getConnection(['driver'=>'pdo_sqlite','memory'=>true]);
        // No password/hash/email column: the wrapper must not request any of them.
        $this->db->executeStatement('CREATE TABLE app_user (id INTEGER PRIMARY KEY, username TEXT COLLATE NOCASE, roles TEXT)');
        foreach ([[1,'alpha',['ROLE_ADMIN']],[2,'beta',['ROLE_SUPER_ADMIN']],[3,'reader',['ROLE_USER']]] as [$id,$username,$roles]) {
            $this->db->insert('app_user',['id'=>$id,'username'=>$username,'roles'=>json_encode($roles)]);
        }
        $schema = new Schema(); AiSchema::add($schema); AiTrackingSchema::add($schema);
        foreach ($schema->toSql($this->db->getDatabasePlatform()) as $sql) $this->db->executeStatement($sql);
        $artifacts = new AiArtifacts($this->dir);
        $registry = new AiNativeRegistry($this->db,new AiStore($this->db,$artifacts),$artifacts);
        $this->command = new AiTrackCommand($this->db,$registry);
    }

    protected function tearDown(): void
    {
        $this->db->close();
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->dir,\FilesystemIterator::SKIP_DOTS),\RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        rmdir($this->dir);
    }

    private function raw(string $raw,array $options=[],bool $interactive=false): array
    {
        $tester = new CommandTester($this->command);
        $tester->setInputs([$raw]);
        $code = $tester->execute($options+['--owner-username'=>'alpha','--actor-task-id'=>self::PILOT],['interactive'=>$interactive,'verbosity'=>256]);
        $text = $tester->getDisplay();
        $value = json_decode($text,true,64,JSON_THROW_ON_ERROR);
        self::assertIsArray($value);
        return [$code,$value,$text];
    }

    private function call(string $op,array $args,array $options=[]): array
    {
        return $this->raw(json_encode(['operation'=>$op,'arguments'=>$args],JSON_THROW_ON_ERROR),$options);
    }

    private function create(): array
    {
        [$code,$response] = $this->call('ai_mission_create',['event_id'=>'fixture-cli-create','title'=>'Synthetic CLI mission','brief'=>'No real native execution','project'=>'fixture'],['--apply'=>true]);
        self::assertSame(0,$code);
        return $response['result'];
    }

    private static function base(array $m,string $event): array
    {
        return ['mission_id'=>$m['mission_id'],'version'=>$m['version'],'event_id'=>$event];
    }

    public function testFullCommandFlowAndExactReplaysDoNotDuplicateOrClose(): void
    {
        $args=['event_id'=>'fixture-cli-create','title'=>'Synthetic CLI mission','brief'=>'No real native execution','project'=>'fixture'];
        [$code,$first]=$this->call('ai_mission_create',$args,['--apply'=>true]);
        [$again,$replay]=$this->call('ai_mission_create',$args,['--apply'=>true]);
        self::assertSame(0,$code); self::assertSame(0,$again); self::assertSame($first,$replay);
        self::assertFalse($first['native_task_verified']);self::assertFalse($first['execution_triggered']);
        $m=$first['result'];
        $assoc=self::base($m,'fixture-cli-associate')+['task_id'=>self::TASK,'task_url'=>'codex://threads/'.self::TASK,'model'=>'fixture-not-executed','effort'=>'low'];
        [$code,$response]=$this->call('ai_task_associate',$assoc,['--apply'=>true,'--native-task-id'=>self::TASK]);
        self::assertSame(0,$code);
        [$code,$replay]=$this->call('ai_task_associate',$assoc,['--apply'=>true,'--native-task-id'=>self::TASK]);self::assertSame($response,$replay);
        $m=$response['result'];
        [$code,$started]=$this->call('ai_task_event',self::base($m,'fixture-cli-started')+['run_id'=>$m['run_id'],'event'=>'started'],['--apply'=>true]);self::assertSame(0,$code);
        $m=$started['result'];$marker=$this->dir.'/must-not-execute';
        $result=self::base($m,'fixture-cli-result')+['run_id'=>$m['run_id'],'summary'=>'<script>untrusted()</script> $(touch '.$marker.')','proofs'=>[['label'=>'Fixture only','url'=>'https://github.com/example/fixture']],'checks'=>[['label'=>'Synthetic claim','outcome'=>'passed']]];
        [$code,$received]=$this->call('ai_task_result',$result,['--apply'=>true]);self::assertSame(0,$code);
        [$code,$replay]=$this->call('ai_task_result',$result,['--apply'=>true]);self::assertSame($received,$replay);
        $m=$received['result'];
        [$code,$reviewed]=$this->call('ai_mission_review',self::base($m,'fixture-cli-review')+['run_id'=>$m['run_id'],'verdict'=>'approve','feedback'=>'Synthetic review, human decision still required.','review_task_id'=>self::REVIEW,'model'=>'fixture-review-not-executed','effort'=>'medium'],['--apply'=>true,'--native-task-id'=>self::REVIEW]);
        self::assertSame(0,$code);self::assertSame('ready_for_validation',$reviewed['result']['status']);
        self::assertNull($this->db->fetchOne('SELECT closure FROM ai_mission'));
        foreach (['ai_mission'=>1,'ai_run'=>1,'ai_asset'=>1,'ai_review'=>1,'ai_tracking_receipt'=>5,'ai_event'=>5] as $table=>$count) self::assertSame($count,(int)$this->db->fetchOne('SELECT COUNT(*) FROM '.$table),$table);
        self::assertFileDoesNotExist($marker);
        $events=$this->db->fetchAllAssociative("SELECT data FROM ai_event WHERE kind IN ('ai_task_associate','ai_mission_review')");
        foreach ($events as $event) self::assertSame('operator_declared_not_platform_verified',json_decode($event['data'],true)['task_reference_source']);
        [$code,$detail]=$this->call('ai_mission_get',['mission_id'=>$m['mission_id']]);self::assertSame(0,$code);
        self::assertSame('executor_report_not_independently_verified',$detail['result']['runs'][0]['report']['verification']);
    }

    public function testUnknownMetadataRemainsExplicitAndIdempotentIncludingReview(): void
    {
        $m=$this->create();
        $args=self::base($m,'fixture-unknown-assoc')+['task_id'=>self::TASK,'task_url'=>'codex://threads/'.self::TASK,'model'=>'unknown','effort'=>'unknown'];
        [$code,$a]=$this->call('ai_task_associate',$args,['--apply'=>true,'--native-task-id'=>self::TASK]);self::assertSame(0,$code);
        [$code,$again]=$this->call('ai_task_associate',$args,['--apply'=>true,'--native-task-id'=>self::TASK]);self::assertSame($a,$again);
        $m=$a['result'];
        [$code,$detail]=$this->call('ai_mission_get',['mission_id'=>$m['mission_id']]);self::assertSame(0,$code);
        $run=$detail['result']['runs'][0];self::assertSame('unknown',$run['model']);self::assertSame('unknown',$run['effort']);
        self::assertSame(['source'=>'operator_report','verification'=>'not_independently_verified','model'=>'not_provided','effort'=>'not_provided'],$run['metadata_quality']);
        [$code]=$this->call('ai_mission_get',['mission_id'=>$m['mission_id']],['--owner-username'=>'beta']);self::assertSame(3,$code);
        [$code,$received]=$this->call('ai_task_result',self::base($m,'fixture-unknown-result')+['run_id'=>$m['run_id'],'summary'=>'Synthetic result only','proofs'=>[['label'=>'Synthetic','url'=>'https://github.com/example/fixture']],'checks'=>[]],['--apply'=>true]);self::assertSame(0,$code);
        $m=$received['result'];
        [$code,$review]=$this->call('ai_mission_review',self::base($m,'fixture-unknown-review')+['run_id'=>$m['run_id'],'verdict'=>'approve','feedback'=>'Synthetic review only','review_task_id'=>self::REVIEW,'model'=>'unknown','effort'=>'unknown'],['--apply'=>true,'--native-task-id'=>self::REVIEW]);
        self::assertSame(0,$code);self::assertSame('ready_for_validation',$review['result']['status']);
        foreach ($this->db->fetchAllAssociative("SELECT data FROM ai_event WHERE kind IN ('ai_task_associate','ai_mission_review')") as $e) {
            $quality=json_decode($e['data'],true)['metadata_quality'];self::assertSame('not_provided',$quality['model']);self::assertSame('not_provided',$quality['effort']);
        }
        self::assertSame(1,(int)$this->db->fetchOne('SELECT COUNT(*) FROM ai_run'));
        self::assertSame(1,(int)$this->db->fetchOne('SELECT COUNT(*) FROM ai_asset'));
    }

    public function testKnownMixedAndInvalidMetadataPreserveValidation(): void
    {
        $quality=\App\Service\Ai\AiTrackingContract::metadataQuality('fixture-model','high');
        self::assertSame('reported',$quality['model']);self::assertSame('reported',$quality['effort']);
        self::assertSame('not_provided',\App\Service\Ai\AiTrackingContract::metadataQuality('fixture-model','unknown')['effort']);
        self::assertSame('not_provided',\App\Service\Ai\AiTrackingContract::metadataQuality('unknown','high')['model']);
        $m=$this->create();$base=self::base($m,'fixture-invalid-metadata')+['task_id'=>self::TASK,'task_url'=>'codex://threads/'.self::TASK,'model'=>'unknown','effort'=>'unknown'];
        foreach ([['model'=>null],['effort'=>null],['model'=>''],['effort'=>''],['model'=>str_repeat('x',101)],['effort'=>'maximum'],['effort'=>42],['model'=>[]]] as $invalid) {
            [$code]=$this->call('ai_task_associate',array_replace($base,$invalid),['--apply'=>true,'--native-task-id'=>self::TASK]);self::assertSame(2,$code);
        }
        self::assertSame(0,(int)$this->db->fetchOne('SELECT COUNT(*) FROM ai_run'));
        $schema=new Schema();AiSchema::add($schema);self::assertTrue($schema->getTable('ai_run')->getColumn('model')->getNotnull());self::assertTrue($schema->getTable('ai_run')->getColumn('effort')->getNotnull());
    }

    public function testOwnerAndActorAreOutsidePayloadAndMustBeAllowed(): void
    {
        foreach (['reader','absent','ALPHA'] as $name) {
            [$code,$r]=$this->call('ai_missions_list',[],['--owner-username'=>$name]);self::assertSame(3,$code);self::assertSame('owner_not_allowed',$r['error']);
        }
        $this->db->insert('app_user',['id'=>4,'username'=>'alpha','roles'=>'["ROLE_ADMIN"]']);
        [$code]=$this->call('ai_missions_list',[]);self::assertSame(3,$code);
        [$code]=$this->call('ai_missions_list',[],['--owner-username'=>'beta']);self::assertSame(0,$code);
        [$code]=$this->call('ai_missions_list',[],['--actor-task-id'=>'made-up-id']);self::assertSame(2,$code);
        [$code]=$this->raw('{"operation":"ai_missions_list","arguments":{},"owner_id":2}');self::assertSame(2,$code);
        [$code]=$this->call('ai_missions_list',['actor'=>'spoof']);self::assertSame(2,$code);
    }

    public function testMutationRequiresApplyAndTaskReferenceMustMatchInvocation(): void
    {
        [$code,$r]=$this->call('ai_mission_create',['event_id'=>'fixture-no-apply','title'=>'No write','brief'=>'Synthetic','project'=>'fixture']);
        self::assertSame(2,$code);self::assertSame('mutation_requires_apply',$r['error']);self::assertSame(0,(int)$this->db->fetchOne('SELECT COUNT(*) FROM ai_mission'));
        $m=$this->create();$args=self::base($m,'fixture-bad-associate')+['task_id'=>self::TASK,'task_url'=>'codex://threads/'.self::TASK,'model'=>'fixture','effort'=>'low'];
        foreach ([[],['--native-task-id'=>self::REVIEW]] as $option) {
            [$code,$r]=$this->call('ai_task_associate',$args,$option+['--apply'=>true]);self::assertSame(2,$code);self::assertSame('task_reference_mismatch',$r['error']);
        }
        self::assertSame(0,(int)$this->db->fetchOne('SELECT COUNT(*) FROM ai_run'));
        [$code]=$this->call('ai_missions_list',[],['--native-task-id'=>self::TASK]);self::assertSame(2,$code);
    }

    public function testCrossOwnerActorStaleVersionAndChangedReplayRefused(): void
    {
        $m=$this->create();
        [$code]=$this->call('ai_mission_get',['mission_id'=>$m['mission_id']],['--owner-username'=>'beta']);self::assertSame(3,$code);
        [$code]=$this->call('ai_mission_prompt',self::base($m,'fixture-foreign-prompt')+['content'=>'not mine'],['--owner-username'=>'beta','--apply'=>true]);self::assertSame(3,$code);
        [$code]=$this->call('ai_mission_create',['event_id'=>'fixture-cli-create','title'=>'Changed','brief'=>'No real native execution','project'=>'fixture'],['--apply'=>true]);self::assertSame(4,$code);
        $assoc=self::base($m,'fixture-cli-associate')+['task_id'=>self::TASK,'task_url'=>'codex://threads/'.self::TASK,'model'=>'fixture','effort'=>'low'];
        [$code,$r]=$this->call('ai_task_associate',$assoc,['--apply'=>true,'--native-task-id'=>self::TASK]);self::assertSame(0,$code);$m=$r['result'];
        [$code]=$this->call('ai_task_event',self::base($m,'fixture-foreign-actor')+['run_id'=>$m['run_id'],'event'=>'started'],['--apply'=>true,'--actor-task-id'=>self::REVIEW]);self::assertSame(4,$code);
        $stale=self::base($m,'fixture-stale-version')+['run_id'=>$m['run_id'],'event'=>'started'];$stale['version']=1;
        [$code]=$this->call('ai_task_event',$stale,['--apply'=>true]);self::assertSame(4,$code);
        self::assertSame('associated',$this->db->fetchOne('SELECT status FROM ai_mission'));
    }

    public function testInvalidPayloadsAndUnexpectedFailuresAreRedacted(): void
    {
        foreach (['{bad','[]','null',str_repeat('x',65537),'{"operation":"exec","arguments":{"command":"touch /tmp/no"}}','{"operation":"ai_missions_list","arguments":{"verified":true}}'] as $raw) {
            [$code,$r]=$this->raw($raw);self::assertSame(2,$code);self::assertFalse($r['ok']);
        }
        [$code,$r,$text]=$this->call('ai_mission_create',['event_id'=>'fixture-sensitive-input','title'=>'Sensitive','brief'=>'Bearer SYNTHETIC_DO_NOT_PRINT_CREDENTIAL','project'=>'fixture'],['--apply'=>true]);self::assertSame(2,$code);self::assertStringNotContainsString('SYNTHETIC_DO_NOT_PRINT',$text);
        [$code]=$this->raw('{"operation":"ai_missions_list","arguments":{}}',[],true);self::assertSame(2,$code);
        $this->db->executeStatement('DROP TABLE app_user');
        [$code,$r,$text]=$this->call('ai_missions_list',[]);self::assertSame(1,$code);self::assertSame('tracking_unavailable',$r['error']);self::assertStringNotContainsString('SELECT',$text);
    }
}
