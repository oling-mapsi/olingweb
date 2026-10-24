<?php
namespace App\Tests\Ai;
use App\Entity\User;
use App\Service\Ai\{AiSchema,AiStore};
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
final class AiAccessTest extends WebTestCase
{
    public function testHostsSessionsCsrfOwnershipAndPrivateAssets():void
    {
        $client=self::createClient();$client->disableReboot();self::getContainer()->get('cache.rate_limiter')->clear();$em=self::getContainer()->get('doctrine')->getManager();
        (new SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
        $s=new Schema();AiSchema::add($s);\App\Service\Ai\AiTrackingSchema::add($s);foreach($s->toSql($em->getConnection()->getDatabasePlatform()) as $sql)$em->getConnection()->executeStatement($sql);
        $u=(new User())->setUsername('ai-fixture')->setEmail('ai@example.invalid')->setRoles(['ROLE_ADMIN']);
        $u->setPassword(self::getContainer()->get('security.user_password_hasher')->hashPassword($u,'Fixture-only-42!'));$em->persist($u);$em->flush();
        $client->request('GET','https://oling.fr/admin/ai');self::assertResponseStatusCodeSame(404);
        $client->request('GET','https://crm.oling.fr/admin/ai');self::assertResponseStatusCodeSame(404);
        $client->request('GET','https://ai.oling.fr/');self::assertResponseRedirects('/admin/ai');
        $client->request('GET','https://ai.oling.fr/admin/ai');self::assertResponseRedirects('/ai/login');
        $c=$client->followRedirect();self::assertResponseIsSuccessful();self::assertSelectorTextContains('h1','Tour de contrôle IA');
        $cookie=$client->getCookieJar()->get('OLINGAISESSID','/','ai.oling.fr');self::assertNotNull($cookie);
        $client->request('POST','https://ai.oling.fr/ai/login',['_username'=>'ai-fixture','_password'=>'Fixture-only-42!','_csrf_token'=>'invalid']);self::assertResponseRedirects('/ai/login');
        $c=$client->followRedirect();$client->submit($c->selectButton('Se connecter')->form(),['_username'=>'ai-fixture','_password'=>'Fixture-only-42!']);self::assertResponseRedirects('/admin/ai');
        $c=$client->followRedirect();self::assertResponseIsSuccessful();self::assertSelectorTextContains('h1','Aujourd’hui');self::assertSelectorTextContains('body','Pilotage natif');
        foreach(['/admin/users','/contact','/uploads/private.txt','/api/crm/mcp'] as $path){$client->request('GET','https://ai.oling.fr'.$path);self::assertResponseStatusCodeSame(404);}
        $client->request('GET','https://oling.fr/admin');self::assertResponseRedirects('/login');
        $client->request('POST','https://ai.oling.fr/admin/ai/missions',['title'=>'Invalid']);self::assertResponseStatusCodeSame(403);
        $c=$client->request('GET','https://ai.oling.fr/admin/ai');$form=$c->selectButton('Enregistrer la demande')->form(['title'=>'Mission navigateur synthétique','brief'=>'Livrer une preuve fictive.','project'=>'demo']);$client->submit($form);self::assertResponseRedirects();
        $url=$client->getResponse()->headers->get('Location');$c=$client->followRedirect();self::assertSelectorTextContains('h1','Mission navigateur synthétique');
        $store=self::getContainer()->get(AiStore::class);$mission=$store->all($u->getId())[0];
        $native=self::getContainer()->get(\App\Service\Ai\AiNativeRegistry::class);
        $a=$native->call('ai_task_associate',['mission_id'=>$mission['id'],'version'=>1,'event_id'=>'access-associate-fixture','task_id'=>'fixture-native-access','task_url'=>'codex://threads/fixture-native-access','model'=>'fixture-model','effort'=>'medium'],$u->getId(),'fixture-pilot');
        $native->call('ai_task_result',['mission_id'=>$mission['id'],'version'=>$a['version'],'event_id'=>'access-result-fixture','run_id'=>$a['run_id'],'summary'=>'Private synthetic fixture','proofs'=>[['label'=>'Synthetic evidence','url'=>'https://github.com/oling-mapsi/olingweb/pull/29']],'checks'=>[]],$u->getId(),'fixture-pilot');
        $asset=$store->assets($u->getId())[0];$client->request('GET','https://ai.oling.fr/admin/ai/assets/'.$asset['id']);self::assertResponseIsSuccessful();self::assertResponseHeaderSame('X-Content-Type-Options','nosniff');self::assertStringContainsString('private',$client->getResponse()->headers->get('Cache-Control'));
        $client->request('GET','https://oling.fr/admin/ai/assets/'.$asset['id']);self::assertResponseStatusCodeSame(404);
        foreach(['home','missions','assets','prompts'] as $view){$client->request('GET','https://ai.oling.fr/admin/ai?view='.$view);self::assertResponseIsSuccessful();self::assertSelectorCount(4,'nav a');}
        $client->request('POST','https://ai.oling.fr/api/ai-relay',[],[],['CONTENT_TYPE'=>'application/json'],'{"action":"claim"}');self::assertResponseStatusCodeSame(403);
        $c=$client->request('GET','https://ai.oling.fr/admin/ai');$client->submit($c->selectButton('Déconnexion')->form());self::assertResponseRedirects('/ai/login');
        $client->request('GET','https://ai.oling.fr/admin/ai/assets/'.$asset['id']);self::assertResponseRedirects('/ai/login');
        $u=$em->find(User::class,$u->getId());$u->setRoles([]);$em->flush();$c=$client->request('GET','https://ai.oling.fr/ai/login');$client->submit($c->selectButton('Se connecter')->form(),['_username'=>'ai-fixture','_password'=>'Fixture-only-42!']);$client->followRedirect();self::assertResponseStatusCodeSame(403);
    }
}
