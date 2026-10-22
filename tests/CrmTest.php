<?php
namespace App\Tests;

use App\Service\Crm\{CrmSchema,CrmStore,CrmConfig,EmailExtractor,GraphMailbox};
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;

final class CrmTest extends TestCase
{
    private CrmStore $store;
    protected function setUp(): void
    {
        $db=DriverManager::getConnection(['driver'=>'pdo_sqlite','memory'=>true]); $schema=new Schema(); CrmSchema::add($schema);
        foreach($schema->toSql($db->getDatabasePlatform()) as $sql) { $db->executeStatement($sql); } $this->store=new CrmStore($db);
    }
    private function source(string $id='message-1',string $thread='thread-1'): array { return ['origin'=>'email','external_id'=>$id,'identity'=>$thread,'title'=>'Demande de devis','fields'=>['organization'=>'Exemple','email'=>'contact@example.invalid'],'url'=>'https://outlook.office.com/mail/id/demo']; }
    public function testIdempotencyAndManualChangesSurviveNewEmails(): void
    {
        $r=$this->store->ingest($this->source()); $id=$r['id'];
        self::assertTrue($this->store->ingest($this->source())['duplicate']); self::assertCount(1,$this->store->all()); self::assertCount(1,$this->store->history($id));
        $this->store->edit($id,['organization'=>'Correction manuelle','next_action'=>'Appeler','summary'=>''],'discussion','private','Note à conserver','admin',2);
        $next=$this->source('message-2'); $next['suggested_status']='won'; $next['fields']['summary']='Résumé automatique'; $this->store->ingest($next);
        $d=$this->store->get($id); self::assertSame('Correction manuelle',$d['organization']); self::assertSame('Appeler',$d['next_action']); self::assertSame('discussion',$d['status']);
        self::assertCount(3,$this->store->history($id));
        $events=$this->store->history($id); self::assertNotEmpty(array_filter($events,fn($e)=>($e['data']['note']??'')==='Note à conserver'));
    }
    public function testSeparateProjectsAndValidatedMerge(): void
    {
        $one=$this->store->ingest($this->source()); $two=$this->store->ingest($this->source('message-2','thread-2'));
        self::assertCount(2,$this->store->all()); self::assertCount(1,$this->store->candidates($this->store->get($two['id'])));
        $this->store->merge($two['id'],$one['id'],'admin');
        $third=$this->store->ingest($this->source('message-3','thread-2')); self::assertSame($one['id'],$third['id']); self::assertSame('archived',$this->store->get($two['id'])['status']);
    }
    public function testWatchFollowAndUpdateSuggestStatus(): void
    {
        $s=$this->source(); $s['origin']='watch'; $s['kind']='public'; $s['follow']=false;
        $id=$this->store->ingest($s)['id']; self::assertSame('qualify',$this->store->get($id)['status']);
        $s['external_id']='update'; $s['follow']=true; $this->store->ingest($s);
        self::assertSame('follow',$this->store->get($id)['status']);
    }
    public function testStaleEditsDoNotOverwrite(): void
    {
        $id=$this->store->create(['title'=>'Exemple']); $this->store->edit($id,[],'follow','private','Note','admin',1);
        $this->expectException(\RuntimeException::class); $this->store->edit($id,[],'lost','private','','admin',1);
    }
    public function testInvalidSourceLinkRejected(): void
    {
        $s=$this->source();$s['url']='javascript:alert(1)';$this->expectException(\InvalidArgumentException::class);$this->store->ingest($s);
    }
    public function testLocalAutomationNeverEnabled(): void
    {
        foreach(['dev','test'] as $env) { $c=new CrmConfig($env); self::assertFalse($c->graphReady()); self::assertFalse($c->enabled('watch')); self::assertFalse($c->enabled('scheduler')); }
    }
    public function testDirectRequestSentProposalAndNewsletter(): void
    {
        $e=new EmailExtractor(); $m=['id'=>'1','subject'=>'Accompagnement Vulcain fictif','from'=>['emailAddress'=>['address'=>'client@example.invalid','name'=>'Contact fictif']],'body'=>['content'=>'Nous souhaitons un accompagnement. Pouvez-vous proposer une mission ?'],'conversationId'=>'thread'];
        $s=$e->extract($m,'demo@oling.fr'); self::assertTrue($s['commercial']); self::assertSame('',$s['fields']['organization']??'');
        $m['from']['emailAddress']['address']='demo@oling.fr';$m['toRecipients']=[['emailAddress'=>['address'=>'client@example.invalid']]];$m['body']['content']='Veuillez trouver ci-joint notre proposition commerciale.';
        self::assertSame('sent',$e->extract($m,'demo@oling.fr')['suggested_status']);
        $m['subject']='Projet ERP — Notre proposition d’accompagnement AMOA';$m['body']['content']='Veuillez trouver ci-joint notre proposition pour le projet ERP.';
        self::assertTrue($e->extract($m,'demo@oling.fr')['commercial']);
        $m['body']['content']='Newsletter : demande de devis'; self::assertNull($e->extract($m,'demo@oling.fr'));
    }
    public function testGraphRejectsForeignPagination(): void
    {
        $g=new GraphMailbox(new MockHttpClient(),new CrmConfig('test')); $this->expectException(\RuntimeException::class);$g->page('https://evil.example/token','secret');
    }
}
