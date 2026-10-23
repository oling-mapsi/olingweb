<?php
namespace App\Tests;
use App\Controller\CrmImportController;
use App\Service\Crm\{CrmConfig,CrmStore,CrmSchema};
use Doctrine\DBAL\{Connection,DriverManager,Schema\Schema};
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
final class CrmDeleteTest extends TestCase
{
    private Connection $db;private CrmStore $store;private CrmConfig $config;
    protected function setUp():void
    {
        $this->db=DriverManager::getConnection(['driver'=>'pdo_sqlite','memory'=>true]);$schema=new Schema();CrmSchema::add($schema);
        foreach($schema->toSql($this->db->getDatabasePlatform()) as $sql) {$this->db->executeStatement($sql);}
        $this->store=new CrmStore($this->db);
        $this->config=new CrmConfig('test');
        (new \ReflectionProperty($this->config,'environment'))->setValue($this->config,'prod');
        (new \ReflectionProperty($this->config,'settings'))->setValue($this->config,['watch_enabled'=>true,'import_token'=>str_repeat('t',40)]);
    }
    private function import(array $payload):array
    {
        $r=Request::create('/api/crm/watch','POST',[],[],[],['HTTP_AUTHORIZATION'=>'Bearer '.str_repeat('t',40)],json_encode($payload));
        $response=(new CrmImportController())->import($r,$this->config,$this->store);
        self::assertLessThan(300,$response->getStatusCode());return json_decode($response->getContent(),true);
    }
    public function testDeletedTenderCannotReturnViaChangedIdOrEnrichmentAndOtherLotsSurvive():void
    {
        $p=['id'=>'notice-fictive','title'=>'Mission fictive','fields'=>['buyer'=>'Acheteur Test','reference'=>'REF-42','lot'=>'1']];
        $id=$this->import($p)['id'];$d=$this->store->get($id);
        $this->store->edit($id,['buyer'=>'Acheteur corrigé'],'preparing','public','Note à effacer','test',$d['version']);
        $this->store->delete($id,$this->store->get($id)['version']);
        foreach(['crm_deal','crm_event','crm_source','crm_identity'] as $table) {self::assertSame(0,(int)$this->db->fetchOne('SELECT COUNT(*) FROM '.$table));}
        self::assertSame([],$this->store->tenderDecisions()['items']);
        self::assertTrue($this->import($p)['deleted']);
        $p['fields']['summary']='Enrichissement du lendemain';$p['follow']=true;
        $r=$this->import($p);self::assertTrue($r['ignored']);self::assertSame('deleted_by_user',$r['reason']);self::assertArrayNotHasKey('url',$r);
        $p['id']='new-agent-id';$p['fields']['buyer']=' ACHETEUR   TEST ';$p['fields']['reference']='ref-42';
        self::assertTrue($this->import($p)['deleted']);self::assertCount(0,$this->store->all());
        $p['id']='notice-fictive';$p['fields']['lot']='2';self::assertTrue($this->import($p)['created']);
        $p['id']='different-tender';$p['fields']['reference']='REF-43';$p['fields']['lot']='1';self::assertTrue($this->import($p)['created']);
        self::assertCount(2,$this->store->all());
    }
    public function testLotIdentityWithoutBuyerReferenceDoesNotBlockAnotherLot():void
    {
        $p=['id'=>'same-notice','title'=>'Fictif','fields'=>['lot'=>'1']];$id=$this->import($p)['id'];
        $this->store->delete($id,$this->store->get($id)['version']);
        $p['fields']['lot']='2';self::assertTrue($this->import($p)['created']);
        $p['fields']['lot']='1';$p['title']='Objet enrichi';self::assertTrue($this->import($p)['deleted']);
    }
    public function testLegacyAgentAliasDoesNotBlockOtherLotsAndMcpAcknowledgesDeletion():void
    {
        $id=$this->store->ingest(['origin'=>'watch','external_id'=>'old-source','identity'=>'notice|legacy|lot|1','aliases'=>['agent-id|legacy','legacy'],'title'=>'Ancien marché fictif','kind'=>'public','fields'=>['notice_id'=>'legacy','lot'=>'1']])['id'];
        $this->store->delete($id,$this->store->get($id)['version']);
        self::assertTrue($this->import(['id'=>'legacy','title'=>'Lot distinct','fields'=>['lot'=>'2']])['created']);
        $r=Request::create('/api/crm/mcp','POST',[],[],[],['HTTP_AUTHORIZATION'=>'Bearer '.str_repeat('t',40)],json_encode(['jsonrpc'=>'2.0','id'=>1,'method'=>'tools/call','params'=>['name'=>'crm_import_tender','arguments'=>['id'=>'legacy','title'=>'Retour du lendemain','fields'=>['lot'=>'1']]]]));
        $response=(new CrmImportController())->mcp($r,$this->config,$this->store,$this->db);
        $rpc=json_decode($response->getContent(),true);self::assertArrayNotHasKey('isError',$rpc['result']);
        $value=json_decode($rpc['result']['content'][0]['text'],true);self::assertTrue($value['ignored']);self::assertTrue($value['deleted']);self::assertArrayNotHasKey('url',$value);
    }
    public function testStaleDeleteIsRejectedAndMergedContentIsFullyRemoved():void
    {
        $a=['origin'=>'form','identity'=>'first','external_id'=>'form-1','title'=>'Ancienne fiche','fields'=>[]];
        $b=['origin'=>'form','identity'=>'second','external_id'=>'form-2','title'=>'Fiche principale','fields'=>[]];
        $first=$this->store->ingest($a)['id'];$second=$this->store->ingest($b)['id'];$this->store->merge($first,$second,'test');
        try {$this->store->delete($second,2);self::fail('Stale deletion must fail');}catch(\RuntimeException $e){self::assertStringContainsString('changé',$e->getMessage());}
        self::assertCount(2,$this->store->all());self::assertSame([],$this->store->state('deleted_i:'.hash('sha256','form|second')));
        try {$this->store->delete($first,$this->store->get($first)['version']);self::fail('Merged source must point to primary');}catch(\InvalidArgumentException $e){self::assertStringContainsString('principale',$e->getMessage());}
        $this->store->delete($second,$this->store->get($second)['version']);
        foreach(['crm_deal','crm_event','crm_source','crm_identity'] as $table){self::assertSame(0,(int)$this->db->fetchOne('SELECT COUNT(*) FROM '.$table));}
        self::assertTrue($this->store->ingest($a)['deleted']);self::assertTrue($this->store->ingest($b)['deleted']);
        $raw=$this->db->fetchFirstColumn('SELECT data FROM crm_state');self::assertStringNotContainsString('Ancienne fiche',implode('',$raw));
    }
}
