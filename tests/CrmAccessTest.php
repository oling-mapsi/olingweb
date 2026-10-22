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
        $url=$client->getResponse()->headers->get('Location');$c=$client->followRedirect();self::assertResponseIsSuccessful();self::assertSelectorTextContains('h2','Affaire fictive');
        $client->request('POST',$url,['_token'=>$token,'version'=>1,'status'=>'sent','kind'=>'public','note'=>'Réponse fictive déposée','fields'=>['deadline'=>'2020-01-01T12:00']]);self::assertResponseRedirects();
        $client->request('GET','/admin/crm?view=active');self::assertResponseIsSuccessful();self::assertSelectorTextContains('table','Affaire fictive');
        $client->request('GET','/admin/crm/export.csv');self::assertResponseIsSuccessful();self::assertResponseHeaderSame('Content-Type','text/csv; charset=UTF-8');
    }
}
