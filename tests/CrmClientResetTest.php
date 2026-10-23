<?php
namespace App\Tests;
use App\Service\Crm\{CrmStore,CrmSchema,CrmClientReset,FormIntake};
use Doctrine\DBAL\{Connection,DriverManager,Schema\Schema};
use PHPUnit\Framework\TestCase;
final class CrmClientResetTest extends TestCase
{
    private Connection $db;private CrmStore $store;
    protected function setUp():void
    {
        $this->db=DriverManager::getConnection(['driver'=>'pdo_sqlite','memory'=>true]);$schema=new Schema();CrmSchema::add($schema);
        foreach($schema->toSql($this->db->getDatabasePlatform()) as $sql) { $this->db->executeStatement($sql); }
        $this->store=new CrmStore($this->db);
    }
    public function testResetRemovesOnlyFrozenClientsAndPreventsReplays():void
    {
        $email=['origin'=>'email','identity'=>'old-client','external_id'=>'cv-message','title'=>'CV fictif','fields'=>[]];
        $e=$this->store->ingest($email)['id'];
        $payload=['site'=>'OLING','reference'=>'old-form-123','fields'=>['email'=>'demo@example.invalid','summary'=>'Ancien formulaire fictif']];
        $form=new FormIntake($this->store);$f=$form->receive($payload)['id'];
        $market=$this->store->ingest(['origin'=>'watch','identity'=>'tender-1','external_id'=>'tender-1','title'=>'Marché conservé','kind'=>'public','fields'=>[]])['id'];
        $this->store->edit($market,['next_action'=>'Répondre'],'preparing','public','Note conservée','test',2);
        $before=$this->store->get($market);$history=$this->store->history($market);
        // Arrives after the frozen selection: must survive, even though it is a client.
        $new=$form->receive(array_replace($payload,['site'=>'MAPSI','reference'=>'new-form-123']))['id'];
        $r=(new CrmClientReset($this->db,$this->store))->purge([$e,$f],'test');
        self::assertSame(2,$r['deleted']);self::assertSame($before,$this->store->get($market));self::assertSame($history,$this->store->history($market));
        foreach(['crm_deal'=>'id','crm_event'=>'deal_id','crm_identity'=>'deal_id','crm_source'=>'deal_id'] as $table=>$key) { self::assertSame(0,(int)$this->db->fetchOne('SELECT COUNT(*) FROM '.$table.' WHERE '.$key.' IN (?,?)',[$e,$f])); }
        self::assertTrue($this->store->ingest($email)['deleted']);
        self::assertTrue($this->store->ingest(array_replace($email,['external_id'=>'retry-new-message']))['deleted']);
        self::assertTrue($form->receive($payload)['deleted']);self::assertSame($f,$form->receive($payload)['id']);
        self::assertSame($e,$this->store->ingest($email)['id']);
        self::assertSame($new,$form->receive(array_replace($payload,['site'=>'MAPSI','reference'=>'new-form-123']))['id']);
        self::assertCount(2,$this->store->all());
        $manual=$this->store->create(['title'=>'Saisie manuelle fictive']);self::assertSame('manual',$this->store->get($manual)['origin']);
    }
    public function testPublicOrQuarantinedRecordCancelsWholeReset():void
    {
        $client=$this->store->create(['title'=>'Client fictif']);
        $public=$this->store->create(['title'=>'Marché manuel'],'public');
        $legacy=$this->store->create(['title'=>'Ancien import'],'private','email');$this->store->reviewLegacy();
        $before=$this->store->all();
        foreach([$public,$legacy] as $protected) {
            try { (new CrmClientReset($this->db,$this->store))->purge([$client,$protected],'test');self::fail('Protected record must cancel transaction.'); }
            catch(\RuntimeException $e) { self::assertStringContainsString('périmètre',$e->getMessage()); }
            self::assertSame($before,$this->store->all());self::assertSame([],$this->store->state('client_reset'));
        }
    }
}
