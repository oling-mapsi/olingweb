<?php
namespace App\Tests;
use App\Entity\User;
use App\Service\Crm\CrmSchema;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
final class CrmAccessTest extends WebTestCase
{
    public function testPrivateAccessCsrfAndManualWorkflow(): void
    {
        $_ENV['DATABASE_URL']=$_SERVER['DATABASE_URL']='sqlite:///:memory:';
        $_ENV['MAILER_DSN']=$_SERVER['MAILER_DSN']='null://null';
        $client=self::createClient();$client->disableReboot();$em=self::getContainer()->get('doctrine')->getManager();
        (new SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());$s=new Schema();CrmSchema::add($s);foreach($s->toSql($em->getConnection()->getDatabasePlatform()) as $sql) {$em->getConnection()->executeStatement($sql);}
        $client->request('GET','/admin/crm');self::assertResponseRedirects('/login');
        $client->request('POST','/api/crm/watch',[],[],['CONTENT_TYPE'=>'application/json'],'{}');self::assertResponseStatusCodeSame(403);
        $u=(new User())->setUsername('crm-test')->setEmail('crm@example.invalid')->setPassword('unused')->setRoles([]);$em->persist($u);$em->flush();$client->loginUser($u);
        $client->request('GET','/admin/crm');self::assertResponseStatusCodeSame(403);$u=$em->find(User::class,$u->getId());$u->setRoles(['ROLE_ADMIN']);$em->flush();$client->loginUser($u);
        $client->request('POST','/admin/crm/new',['fields'=>['title'=>'Invalid']]);self::assertResponseStatusCodeSame(403);
        $c=$client->request('GET','/admin/crm/new');self::assertResponseIsSuccessful();$token=$c->filter('input[name="_token"]')->attr('value');
        $client->request('POST','/admin/crm/new',['_token'=>$token,'fields'=>['title'=>'Affaire fictive','organization'=>'Exemple'],'kind'=>'public']);self::assertResponseRedirects();
        $url=$client->getResponse()->headers->get('Location');$c=$client->followRedirect();self::assertResponseIsSuccessful();self::assertSelectorTextContains('h1','Affaire fictive');
        $client->request('POST',$url,['_token'=>$token,'version'=>1,'status'=>'sent','kind'=>'public','note'=>'Réponse fictive déposée','fields'=>['deadline'=>'2020-01-01T12:00']]);self::assertResponseRedirects();
        $client->request('GET','/admin/crm?view=active&mode=list');self::assertResponseIsSuccessful();self::assertSelectorTextContains('table','Affaire fictive');
        $client->request('POST',$url.'/move',['status'=>'discussion','version'=>2]);self::assertResponseStatusCodeSame(403);
        $client->request('POST',$url.'/move',['_token'=>$token,'status'=>'discussion','version'=>2]);self::assertResponseIsSuccessful();self::assertSame(3,json_decode($client->getResponse()->getContent(),true)['version']);
        $client->request('POST',$url.'/move',['_token'=>$token,'status'=>'won','version'=>2]);self::assertResponseStatusCodeSame(409);
        $client->request('GET',$url);self::assertSelectorTextContains('.crm-badge','En discussion');
        $store=new \App\Service\Crm\CrmStore($em->getConnection());
        $private=$store->create(['title'=>'Client à qualifier'],'private','form');
        $market=$store->ingest(['origin'=>'watch','external_id'=>'test-market','identity'=>'test-market|lot1','title'=>'Marché à examiner','fields'=>[],'kind'=>'public'])['id'];
        $doubtful=$store->create(['title'=>'Ancien import douteux'],'private','email');$store->reviewLegacy();
        $client->request('GET','/admin/crm');self::assertSelectorTextContains('.board','Client à qualifier');self::assertSelectorTextContains('.board','Marché à examiner');self::assertSelectorTextNotContains('.board','Ancien import douteux');
        $client->request('GET','/admin/crm?view=clients');self::assertSelectorTextContains('.board','Client à qualifier');self::assertSelectorTextNotContains('.board','Affaire fictive');
        $client->request('GET','/admin/crm?segment=markets');self::assertSelectorTextContains('.board','Marché à examiner');self::assertSelectorTextNotContains('.board','Client à qualifier');
        $client->request('GET','/admin/crm?segment=clients&mode=list');self::assertSelectorTextContains('table','Client à qualifier');self::assertSelectorTextNotContains('table','Marché à examiner');
        $client->request('POST','/admin/crm/'.$market.'/move',['_token'=>$token,'version'=>2,'decision'=>'reject']);self::assertResponseIsSuccessful();self::assertSame('reject',$store->get($market)['decision']);
        $client->request('POST','/admin/crm/'.$market.'/move',['_token'=>$token,'version'=>2,'decision'=>'follow']);self::assertResponseStatusCodeSame(409);
        $client->request('POST','/admin/crm/'.$market.'/move',['_token'=>$token,'version'=>3,'status'=>'preparing']);self::assertResponseIsSuccessful();self::assertSame('reject',$store->get($market)['decision']);self::assertSame('preparing',$store->get($market)['status']);
        $client->request('POST','/admin/crm/'.$private.'/move',['_token'=>$token,'version'=>1,'decision'=>'follow']);self::assertResponseStatusCodeSame(409);
        $client->request('GET','/admin/crm?view=markets');self::assertSelectorExists('.deal-card[draggable="true"]');self::assertSelectorExists('.board[data-field="decision"]');
        $client->request('POST','/admin/crm/'.$private,['_token'=>$token,'version'=>1,'status'=>'follow','kind'=>'private','note'=>'Motif conservé','fields'=>['next_action'=>'Relance fictive','reminder'=>'2026-11-01T10:00']]);self::assertResponseRedirects();
        $client->request('GET','/admin/crm?view=todo');self::assertSelectorTextContains('.board','Client à qualifier');
        $client->request('POST','/admin/crm/'.$private.'/move',['_token'=>$token,'version'=>2,'status'=>'no_go']);self::assertResponseIsSuccessful();
        self::assertSame('no_go',$store->get($private)['status']);self::assertSame('Relance fictive',$store->get($private)['next_action']);
        $client->request('GET','/admin/crm?view=todo');self::assertSelectorTextNotContains('.board','Client à qualifier');
        $client->request('GET','/admin/crm');self::assertSelectorTextContains('.lane[data-value="no_go"]','Client à qualifier');
        $client->request('GET','/admin/crm/'.$private);self::assertSelectorTextContains('.crm-badge','Sans suite');self::assertSelectorTextContains('body','Motif conservé');
        for($i=0;$i<51;++$i) { $store->create(['title'=>'Client fictif '.$i],'private','form'); }
        $client->request('GET','/admin/crm');self::assertSelectorCount(54,'.deal-card');
        $client->request('GET','/admin/crm?mode=list');self::assertSelectorCount(50,'tbody tr');
        $store->edit($market,['deadline'=>'2099-10-24T10:00:00Z'],'preparing','public','','test',$store->get($market)['version']);
        $client->request('POST','/admin/crm/'.$market.'/move',['_token'=>$token,'version'=>$store->get($market)['version'],'decision'=>'follow']);
        self::assertResponseIsSuccessful();self::assertStringContainsString('Marché à examiner',json_decode($client->getResponse()->getContent(),true)['deadline_html']);
        $client->request('GET','/admin/crm?segment=clients');self::assertSelectorTextContains('#deadline-summary','Marché à examiner');
        $client->request('POST','/admin/crm/'.$market.'/move',['_token'=>$token,'version'=>$store->get($market)['version'],'status'=>'no_go']);
        self::assertResponseIsSuccessful();self::assertStringNotContainsString('Marché à examiner',json_decode($client->getResponse()->getContent(),true)['deadline_html']);
        $client->request('GET','/admin/crm/export.csv');self::assertResponseIsSuccessful();self::assertResponseHeaderSame('Content-Type','text/csv; charset=UTF-8');
    }
}
