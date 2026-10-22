<?php
namespace App\Tests;
use App\Controller\CrmImportController;
use App\Service\Crm\{CrmConfig,CrmSchema,CrmStore,EmailExtractor};
use Doctrine\DBAL\{DriverManager,Schema\Schema};
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
final class CrmImportTest extends TestCase
{
 public function testAuthenticatedMcpImportEnrichmentReplayAndDryRun():void
 {
  $db=DriverManager::getConnection(['driver'=>'pdo_sqlite','memory'=>true]);$schema=new Schema();CrmSchema::add($schema);foreach($schema->toSql($db->getDatabasePlatform()) as $sql){$db->executeStatement($sql);}$store=new CrmStore($db);
  $config=new CrmConfig('test');(new \ReflectionProperty($config,'environment'))->setValue($config,'prod');(new \ReflectionProperty($config,'settings'))->setValue($config,['watch_enabled'=>true,'import_token'=>str_repeat('x',40)]);
  $controller=new CrmImportController();
  $call=function($method,$params=[], $auth=true, $origin=null)use($controller,$config,$store,$db){$headers=$auth?['HTTP_AUTHORIZATION'=>'Bearer '.str_repeat('x',40)]:[];if($origin){$headers['HTTP_ORIGIN']=$origin;}return $controller->mcp(Request::create('/api/crm/mcp','POST',[],[],[],$headers,json_encode(['jsonrpc'=>'2.0','id'=>1,'method'=>$method,'params'=>$params])),$config,$store,$db);};
  self::assertSame(401,$call('tools/list',[],false)->getStatusCode());self::assertSame(403,$call('tools/list',[],true,'https://evil.example')->getStatusCode());
  self::assertSame('2025-06-18',json_decode($call('initialize',['protocolVersion'=>'2025-06-18'])->getContent(),true)['result']['protocolVersion']);
  self::assertCount(3,json_decode($call('tools/list')->getContent(),true)['result']['tools']);
  $p=['name'=>'crm_import_tender','arguments'=>['id'=>'stable-1','title'=>'AMOA fictive','follow'=>false,'fields'=>[],'dry_run'=>true]];
  self::assertStringContainsString('"persisted":false',json_decode($call('tools/call',$p)->getContent(),true)['result']['content'][0]['text']);self::assertCount(0,$store->all());
  $p['arguments']['dry_run']=false;$call('tools/call',$p);self::assertCount(1,$store->all());$id=$store->all()[0]['id'];
  $p['arguments']['fields']=['buyer'=>'Acheteur Exemple','reference'=>'REF-1','lot'=>'1'];$p['arguments']['follow']=true;$call('tools/call',$p);
  self::assertCount(1,$store->all());self::assertSame('follow',$store->get($id)['status']);self::assertCount(2,$store->history($id));
  $call('tools/call',$p);self::assertCount(2,$store->history($id));
  $p['arguments']['id']='another-source-id';$call('tools/call',$p);self::assertCount(1,$store->all());
  $p['arguments']['id']='lot-2';$p['arguments']['fields']['lot']='2';$call('tools/call',$p);self::assertCount(2,$store->all());
  $p['arguments']['fields']['notice_url']='javascript:alert(1)';self::assertTrue(json_decode($call('tools/call',$p)->getContent(),true)['result']['isError']);
 }
 public function testStructuredWebsiteNotificationAndAutomatedDeposit():void
 {
  $extractor=new EmailExtractor();$m=['id'=>'form-1','subject'=>'Nouveau message — Formulaire contact OLING','from'=>['emailAddress'=>['address'=>'demo@oling.fr']],'body'=>['content'=>"Référence de demande : abcdef1234567890\nPrénom: Camille\nNom: Exemple\nSociété: Exemple SA\nEmail: camille@example.invalid\n\nMessage:\nNous recherchons une expertise RGPD.\n\nAttribution de la demande:\nSource: test"]];
  $s=$extractor->extract($m,'demo@oling.fr');self::assertSame('form',$s['origin']);self::assertSame('Camille Exemple',$s['fields']['contact']);self::assertSame('camille@example.invalid',$s['fields']['email']);
  $m['from']['emailAddress']['address']='no-reply@example.invalid';$m['subject']='Confirmation de dépôt de votre offre';$m['body']['content']='Votre offre a bien été déposée.';$s=$extractor->extract($m,'demo@oling.fr');self::assertSame('sent',$s['suggested_status']);self::assertFalse($s['commercial']);
 }
}
