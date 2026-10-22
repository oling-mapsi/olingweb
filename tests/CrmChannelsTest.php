<?php
namespace App\Tests;
use App\Service\Crm\{CrmSchema,CrmStore,FormIntake,EmailExtractor};
use Doctrine\DBAL\{DriverManager,Schema\Schema};
use PHPUnit\Framework\TestCase;
final class CrmChannelsTest extends TestCase
{
 private CrmStore $store;
 protected function setUp():void {$db=DriverManager::getConnection(['driver'=>'pdo_sqlite','memory'=>true]);$s=new Schema();CrmSchema::add($s);foreach($s->toSql($db->getDatabasePlatform()) as $sql){$db->executeStatement($sql);}$this->store=new CrmStore($db);}
 public function testRejectedTenderCannotResurrectAndManualFieldsSurvive():void
 {
  $s=['origin'=>'watch','identity'=>'notice-1|lot-1','external_id'=>'detection','title'=>'AMOA fictive','kind'=>'public','fields'=>['buyer'=>'Acheteur fictif'],'follow'=>false];
  $id=$this->store->ingest($s)['id'];$this->store->decide($id,'reject',2,'admin');
  $d=$this->store->get($id);$this->store->edit($id,['next_action'=>'Action conservée','buyer'=>'Acheteur corrigé'],'discussion','public','Note conservée','admin',$d['version']);
  $s['external_id']='enrichissement';$s['follow']=true;$s['fields']['reference']='REF-1';$this->store->ingest($s);
  $d=$this->store->get($id);self::assertSame('reject',$d['decision']);self::assertSame('discussion',$d['status']);self::assertSame('Action conservée',$d['next_action']);self::assertSame('Acheteur corrigé',$d['buyer']);self::assertSame('REF-1',$d['reference']);
  self::assertSame('reject',$this->store->tenderDecisions()['items'][0]['decision']); self::assertArrayNotHasKey('email',$this->store->tenderDecisions()['items'][0]);
 }
 public function testDirectOlingThenNotificationOneRecordRealContactFullNeed():void
 {
  $p=['site'=>'OLING','reference'=>'a123456789abcdef','fields'=>['first_name'=>'Camille','last_name'=>'Exemple','email'=>'camille@example.invalid','summary'=>str_repeat('Besoin intégral. ',500),'organization'=>'Exemple']];
  $intake=new FormIntake($this->store);$id=$intake->receive($p)['id'];
  $m=['id'=>'notification','subject'=>'Nouveau message — Formulaire contact OLING','from'=>['emailAddress'=>['address'=>'demo@oling.fr']],'body'=>['content'=>"Référence de demande : a123456789abcdef\nPrénom: Camille\nNom: Exemple\nEmail: camille@example.invalid\n\nMessage:\n".$p['fields']['summary']."\n\nSource: test"],'webLink'=>'https://outlook.office.com/mail/id/test'];
  $s=(new EmailExtractor())->extract($m,'demo@oling.fr');self::assertNotNull($s);self::assertSame($id,$this->store->ingest($s)['id']);self::assertCount(1,$this->store->all());self::assertSame('Camille Exemple',$this->store->get($id)['contact']);self::assertSame($p['fields']['summary'],$this->store->get($id)['summary'].' ');self::assertCount(2,$this->store->history($id));
  $m['from']['emailAddress']['address']='attacker@example.invalid';self::assertNotSame('form',(new EmailExtractor())->extract($m,'demo@oling.fr')['origin']??null);
 }
 public function testMapsiNotificationThenDirectDedup():void
 {
  $p=['site'=>'MAPSI','reference'=>'b123456789abcdef','fields'=>['contact'=>'Alex Exemple','email'=>'alex@example.invalid','phone'=>'+33000000000','summary'=>'Projet fictif MAPSI']];
  $m=['id'=>'notification','subject'=>'Demande de démo - Mapsi','from'=>['emailAddress'=>['address'=>'florestan.rouet@oling.fr']],'body'=>['content'=>'CRM-MAPSI: '.base64_encode(json_encode($p)).' END-CRM']];
  $s=(new EmailExtractor())->extract($m,'florestan.rouet@oling.fr');$id=$this->store->ingest($s)['id'];self::assertSame($id,(new FormIntake($this->store))->receive($p)['id']);self::assertSame('alex@example.invalid',$this->store->get($id)['email']);self::assertSame('+33000000000',$this->store->get($id)['phone']);self::assertCount(1,$this->store->all());
 }
 public function testKeywordsCannotCreateAndDigestCannotAttach():void
 {
  $e=new EmailExtractor();$m=['id'=>'x','from'=>['emailAddress'=>['address'=>'someone@example.invalid']],'subject'=>'Appel d’offres devis proposition','body'=>['content'=>'Voici une consultation et un devis.']];
  self::assertFalse($e->extract($m,'demo@oling.fr')['commercial']);
  $m['subject']='Votre sélection d’appels d’offres';self::assertNull($e->extract($m,'demo@oling.fr'));
  $m['subject']='Re: Mission';$m['internetMessageHeaders']=[['name'=>'List-ID','value'=>'digest']];self::assertNull($e->extract($m,'demo@oling.fr'));
 }
 public function testLegacyReviewPreservesFollowedManualAndNotes():void
 {
  $a=$this->store->create(['title'=>'Ancien import'],'private','email');$b=$this->store->create(['title'=>'Affaire suivie'],'private','email');
  $this->store->edit($b,['summary'=>'Besoin initial'],'follow','private','Note précieuse','admin',1);
  self::assertSame(1,$this->store->reviewLegacy());self::assertTrue($this->store->get($a)['review_required']);self::assertFalse($this->store->get($b)['review_required']);self::assertSame('Note précieuse',$this->store->history($b)[0]['data']['note']);self::assertSame(0,$this->store->reviewLegacy());self::assertCount(2,$this->store->all());
 }
}
