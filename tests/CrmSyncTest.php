<?php
namespace App\Tests;
use App\Service\Crm\{CrmConfig,CrmStore,CrmSchema,CrmSync,EmailExtractor,GraphMailbox};
use Doctrine\DBAL\{DriverManager,Schema\Schema};
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\{MockHttpClient,Response\MockResponse};
final class CrmSyncTest extends TestCase
{
 public function testFormsOnlyPolicyNeverReadsMailboxEvenIfEmailFlagIsEnabled():void
 {
  $config=new CrmConfig('test');(new \ReflectionProperty($config,'environment'))->setValue($config,'prod');
  $settings=['email_enabled'=>true,'forms_enabled'=>true,'scheduler_enabled'=>true,'client_secret'=>'fake','tenant_id'=>'test','client_id'=>'test','mailbox'=>'demo@oling.fr'];
  (new \ReflectionProperty($config,'settings'))->setValue($config,$settings);
  self::assertFalse($config->graphReady());self::assertTrue($config->enabled('forms'));self::assertTrue($config->enabled('scheduler'));
  $db=DriverManager::getConnection(['driver'=>'pdo_sqlite','memory'=>true]);$store=new CrmStore($db);
  $http=new MockHttpClient(function(){self::fail('No Microsoft request allowed in forms-only mode.');});
  $sync=new CrmSync($config,$store,new GraphMailbox($http,$config),new EmailExtractor(),'/tmp');
  $this->expectException(\RuntimeException::class);$sync->run();
 }
 public function testTwoPassHistoryAndRepeatedSyncAreIdempotent():void
 {
  $db=DriverManager::getConnection(['driver'=>'pdo_sqlite','memory'=>true]);$s=new Schema();CrmSchema::add($s);foreach($s->toSql($db->getDatabasePlatform()) as $sql){$db->executeStatement($sql);}$store=new CrmStore($db);
  $config=new CrmConfig('test');$r=new \ReflectionProperty($config,'environment');$r->setValue($config,'prod');$r=new \ReflectionProperty($config,'settings');$r->setValue($config,['client_intake'=>'email_and_forms','email_enabled'=>true,'client_secret'=>'fake','tenant_id'=>'test','client_id'=>'test','mailbox'=>'demo@oling.fr','lock_dir'=>sys_get_temp_dir()]);
  $base=['from'=>['emailAddress'=>['address'=>'client@example.invalid']],'conversationId'=>'same-project','receivedDateTime'=>gmdate('Y-m-d\TH:i:s\Z')];
  $reply=$base+['id'=>'reply','subject'=>'RE: Projet','body'=>['content'=>'Merci pour votre retour.']];
  $request=$base+['id'=>'request','subject'=>'Demande de devis','body'=>['content'=>'Nous souhaitons un accompagnement. Pouvez-vous nous proposer une mission ?']];
  $http=new MockHttpClient(function($method,$url)use($reply,$request){
   if(str_contains($url,'/token')){self::assertSame('POST',$method);return new MockResponse('{"access_token":"fake"}');}
   self::assertSame('GET',$method);self::assertStringStartsWith('https://graph.microsoft.com/v1.0/',$url);return new MockResponse(json_encode(['value'=>[$reply,$request]]));
  });
  $sync=new CrmSync($config,$store,new GraphMailbox($http,$config),new EmailExtractor(),'/tmp');
  self::assertTrue($sync->run()['pending']);self::assertCount(1,$store->all());$id=$store->all()[0]['id'];self::assertCount(1,$store->history($id));
  self::assertFalse($sync->run()['pending']);self::assertCount(2,$store->history($id));
  self::assertSame(0,$sync->run()['new_events']);self::assertCount(1,$store->all());self::assertCount(2,$store->history($id));self::assertNotEmpty($store->state('email')['last_success']);
 }
}
