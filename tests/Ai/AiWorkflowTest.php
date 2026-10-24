<?php
namespace App\Tests\Ai;
use App\Service\Ai\{AiSchema,AiStore,AiArtifacts};
use Doctrine\DBAL\{DriverManager,Connection};
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;
final class AiWorkflowTest extends TestCase
{
    private Connection $db;private AiStore $store;private string $root;
    protected function setUp():void
    {
        $this->db=DriverManager::getConnection(['driver'=>'pdo_sqlite','memory'=>true]);
        $this->db->executeStatement('PRAGMA foreign_keys=ON');$this->db->executeStatement('CREATE TABLE app_user (id INTEGER PRIMARY KEY)');$this->db->insert('app_user',['id'=>1]);$this->db->insert('app_user',['id'=>2]);
        $schema=new Schema();AiSchema::add($schema);foreach($schema->toSql($this->db->getDatabasePlatform()) as $sql)$this->db->executeStatement($sql);
        $this->root=sys_get_temp_dir().'/oling-ai-test-'.bin2hex(random_bytes(5));$this->store=new AiStore($this->db,new AiArtifacts($this->root));
    }
    protected function tearDown():void {foreach(glob($this->root.'/var/ai-private/*')?:[] as $f)unlink($f);if(is_dir($this->root.'/var/ai-private'))rmdir($this->root.'/var/ai-private');if(is_dir($this->root.'/var'))rmdir($this->root.'/var');if(is_dir($this->root))rmdir($this->root);$this->db->close();}
    private function mission():string {return $this->store->create(1,'Mission synthétique','Livrer une fonction et son test.','demo','create-operation-1');}
    private function launch():string {$id=$this->mission();$this->store->launch($id,1,1,'launch-operation-1');return $id;}
    private function complete(array $run,string $output):void {$this->store->finish($run['id'],1,'mac-local',['status'=>'completed','output'=>$output],time());}
    public function testRoundtripAndIdempotentHumanClosure():void
    {
        $id=$this->launch();$this->store->launch($id,1,1,'launch-operation-1');self::assertCount(1,$this->store->detail($id,1)['runs']);
        $run=$this->store->claim(1,'mac-local',['demo'],time());self::assertSame('execute',$run['role']);self::assertNull($this->store->claim(1,'mac-local',['demo'],time()));
        $this->complete($run,'Résultat fictif avec preuve de test.');$this->complete($run,'Rejeu ignoré');self::assertCount(1,$this->store->assets(1));
        $review=$this->store->claim(1,'mac-local',['demo'],time());self::assertSame('review',$review['role']);$this->complete($review,'{"verdict":"approve","feedback":"Test synthétique vérifié."}');
        $m=$this->store->mission($id,1);self::assertSame('awaiting_review',$m['status']);
        $this->store->review($id,1,$m['version'],'human-approve-operation','approve','Preuve relue : asset privé, résultat conforme.');
        $this->store->review($id,1,$m['version'],'human-approve-operation','approve','Rejeu');self::assertSame('closed',$this->store->mission($id,1)['status']);
    }
    public function testOfflineAllowlistAndStaleVersion():void
    {
        $id=$this->launch();self::assertFalse($this->store->online(1));self::assertNull($this->store->claim(1,'other',['demo'],time()));self::assertNull($this->store->claim(1,'mac-local',['unknown'],time()));
        self::assertSame('queued',$this->store->mission($id,1)['status']);$this->expectException(\DomainException::class);$this->store->cancel($id,1,1,'stale-cancel-request');
    }
    public function testOwnerIsolation():void {$id=$this->mission();self::assertSame([],$this->store->all(2));$this->expectException(\OutOfBoundsException::class);$this->store->mission($id,2);}
    public function testLeaseExpiryRequiresReconciliation():void
    {
        $id=$this->launch();$run=$this->store->claim(1,'mac-local',['demo'],time()-100);self::assertNull($this->store->claim(1,'mac-local',['demo'],time()));$m=$this->store->mission($id,1);self::assertSame('interrupted',$m['status']);
        try {$this->store->review($id,1,$m['version'],'unsafe-resume-operation','correct','Relancer');self::fail('Must refuse uncertain replay');}catch(\DomainException){}
        try {$this->complete($run,'Late result');self::fail('Must reject late completion');}catch(\DomainException){}
        $this->store->reconcile($id,1,$m['version'],'reconcile-operation-1','Processus vérifié arrêté, dossier synthétique contrôlé.');$m=$this->store->mission($id,1);
        $this->store->review($id,1,$m['version'],'safe-resume-operation','correct','Reprendre après vérification documentée.');self::assertNotNull($this->store->claim(1,'mac-local',['demo'],time()));
    }
    public function testCancellationDoesNotAdvanceWorkflow():void
    {
        $id=$this->launch();$run=$this->store->claim(1,'mac-local',['demo'],time());$m=$this->store->mission($id,1);$this->store->cancel($id,1,$m['version'],'cancel-operation-1');
        self::assertSame('cancel_requested',$this->store->heartbeat($run['id'],1,'mac-local',time()));$this->complete($run,'Late output');self::assertSame('cancelled',$this->store->mission($id,1)['status']);self::assertCount(0,$this->store->assets(1));
    }
    public function testApprovalDenialBlocksAndIsLogged():void
    {
        $id=$this->launch();$run=$this->store->claim(1,'mac-local',['demo'],time());$this->store->finish($run['id'],1,'mac-local',['status'=>'completed','output'=>'no action','approval'=>true],time());
        self::assertSame('blocked',$this->store->mission($id,1)['status']);self::assertSame('denied',$this->store->detail($id,1)['runs'][0]['status']);self::assertNull($this->store->claim(1,'mac-local',['demo'],time()));
    }
    public function testAtMostTwoCorrectionCycles():void
    {
        $id=$this->launch();for($cycle=0;$cycle<3;$cycle++) {
            $run=$this->store->claim(1,'mac-local',['demo'],time());$this->complete($run,'Résultat cycle '.$cycle);
            $review=$this->store->claim(1,'mac-local',['demo'],time());$this->complete($review,'{"verdict":"correct","feedback":"Corriger le critère synthétique."}');
        }
        $m=$this->store->mission($id,1);self::assertSame(2,(int)$m['corrections']);self::assertSame('blocked',$m['status']);self::assertNull($this->store->claim(1,'mac-local',['demo'],time()));self::assertCount(6,$this->store->detail($id,1)['runs']);
    }
    public function testImmutablePromptVersionsAndPrivateArtifactIntegrity():void
    {
        $id=$this->mission();$this->store->promptVersion($id,1,1,'Nouvelle version synthétique.','prompt-version-key-1');self::assertCount(2,$this->store->prompts(1));
        $artifacts=new AiArtifacts($this->root);$a=$artifacts->put('Private fixture');self::assertStringContainsString('/var/ai-private/',$artifacts->path($a));file_put_contents($artifacts->path($a),'tampered');
        $this->expectException(\RuntimeException::class);$artifacts->path($a);
    }
    public function testHeartbeatAloneExpiresSuspendedRunWithoutNewClaim():void
    {
        $id=$this->launch();$run=$this->store->claim(1,'mac-local',['demo'],time()-46);
        self::assertSame('uncertain',$this->store->heartbeat($run['id'],1,'mac-local',time()));
        self::assertSame('interrupted',$this->store->mission($id,1)['status']);
        self::assertSame('uncertain',$this->store->heartbeat($run['id'],1,'mac-local',time()+1));
        self::assertCount(1,array_filter($this->store->detail($id,1)['events'],fn($e)=>$e['kind']==='lease_expired'));
        $this->expectException(\DomainException::class);$this->complete($run,'Late result must not succeed');
    }
    public function testFinishPersistsExpiryBeforeRejectingLateDelivery():void
    {
        $id=$this->launch();$run=$this->store->claim(1,'mac-local',['demo'],time()-46);
        try {$this->complete($run,'Late result');self::fail('Must reject');}catch(\DomainException){}
        self::assertSame('uncertain',$this->store->detail($id,1)['runs'][0]['status']);
        self::assertSame('interrupted',$this->store->mission($id,1)['status']);
    }
    public function testMalformedReviewBlocksWithoutCorrection():void
    {
        $id=$this->launch();$this->complete($this->store->claim(1,'mac-local',['demo'],time()),'Fixture');$this->complete($this->store->claim(1,'mac-local',['demo'],time()),'approve maybe');self::assertSame('blocked',$this->store->mission($id,1)['status']);self::assertSame(0,(int)$this->store->mission($id,1)['corrections']);
    }
}
