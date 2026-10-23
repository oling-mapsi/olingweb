<?php
namespace App\Tests;
use App\Entity\User;
use App\Service\Crm\{CrmConfig,CrmSchema,CrmStore,FormIntake,FormQueue};
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CrmChatFormTest extends WebTestCase
{
    private string $queueDir;
    protected function tearDown():void
    {
        if(isset($this->queueDir)){foreach(glob($this->queueDir.'/*')?:[] as $file){unlink($file);}rmdir($this->queueDir);}
        parent::tearDown();
    }
    public function testRealChatFormRouteCreatesOneClientAndRecoversMissedDelivery():void
    {
        $_ENV['DATABASE_URL']=$_SERVER['DATABASE_URL']='sqlite:///:memory:';
        $_ENV['MAILER_DSN']=$_SERVER['MAILER_DSN']='null://null';
        $client=self::createClient();$client->disableReboot();$container=self::getContainer();$em=$container->get('doctrine')->getManager();$db=$em->getConnection();
        (new SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());$schema=new Schema();CrmSchema::add($schema);foreach($schema->toSql($db->getDatabasePlatform()) as $sql){$db->executeStatement($sql);}
        $this->queueDir=sys_get_temp_dir().'/crm-chat-test-'.bin2hex(random_bytes(6));mkdir($this->queueDir,0700);
        $config=$container->get(CrmConfig::class);(new \ReflectionProperty($config,'environment'))->setValue($config,'prod');(new \ReflectionProperty($config,'settings'))->setValue($config,['forms_enabled'=>true,'scheduler_enabled'=>true,'forms_dir'=>$this->queueDir]);
        $store=new CrmStore($db);$queue=new FormQueue($config,new FormIntake($store),$store,$db);
        // Real CSRF-protected HTTP route, persisted lead and registered response subscriber.
        $startSession=function() use($container,$client) {
            $session=$container->get('session.factory')->createSession();$csrf='fixture-chat-token';
            $session->set('_csrf/chat_widget',$csrf);$session->save();
            $client->getCookieJar()->set(new \Symfony\Component\BrowserKit\Cookie($session->getName(),$session->getId()));return $csrf;
        };
        $csrf=$startSession();
        $headers=['CONTENT_TYPE'=>'application/json','HTTP_X_CSRF_TOKEN'=>$csrf];
        $client->request('POST','/api/chat/conversations',[],[],$headers,json_encode(['sourcePath'=>'/contact','sourceUrl'=>'https://oling.fr/contact','locale'=>'fr']));self::assertResponseIsSuccessful();
        $token=json_decode($client->getResponse()->getContent(),true)['token'];
        self::assertCount(0,$store->all());
        $payload=['fullName'=>'Camille Test','email'=>'camille@example.invalid','phone'=>'0100000000','company'=>'Société Fictive','needDescription'=>str_repeat('Besoin complet de conseil. ',15),'rgpdConsent'=>false];
        $url='/api/chat/conversations/'.$token.'/lead';
        $client->request('POST',$url,[],[],$headers,json_encode($payload));self::assertResponseStatusCodeSame(400);self::assertCount(0,$store->all());
        $payload['rgpdConsent']=true;
        $client->request('POST',$url,[],[],$headers,json_encode($payload));self::assertResponseIsSuccessful();self::assertSame('delivered',json_decode($client->getResponse()->getContent(),true)['crm_status']);
        self::assertCount(1,$store->all());$deal=$store->all()[0];$id=$deal['id'];
        self::assertSame('private',$deal['kind']);self::assertSame('form',$deal['origin']);self::assertSame('qualify',$deal['status']);self::assertSame('chat_contact',$deal['form_name']);self::assertSame('Camille Test',$deal['contact']);self::assertSame('camille@example.invalid',$deal['email']);self::assertSame('0100000000',$deal['phone']);self::assertSame(trim($payload['needDescription']),$deal['summary']);self::assertStringNotContainsString($token,json_encode($deal));
        // Replay and durable recovery must use exactly the same identity.
        $client->request('POST',$url,[],[],$headers,json_encode($payload));self::assertResponseIsSuccessful();self::assertCount(1,$store->all());self::assertCount(1,$store->history($id));
        $store->setState('chat_forms',['cursor'=>0]);$queue->flush();self::assertCount(1,$store->all());self::assertSame(1,$store->state('chat_forms')['cursor']);
        // Real CRM view: it is visible in the client column to qualify.
        $u=(new User())->setUsername('crm-chat-test')->setEmail('admin@example.invalid')->setPassword('unused')->setRoles(['ROLE_ADMIN']);$em->persist($u);$em->flush();$client->loginUser($u);
        $client->request('GET','/admin/crm?view=clients');self::assertResponseIsSuccessful();self::assertSelectorTextContains('.lane[data-value="qualify"]','Société Fictive');
        // A persisted lead survives a missing queue directory; scheduled replay restores it.
        $headers['HTTP_X_CSRF_TOKEN']=$startSession();
        $client->request('POST','/api/chat/conversations',[],[],$headers,'{}');self::assertResponseIsSuccessful();$token2=json_decode($client->getResponse()->getContent(),true)['token'];
        (new \ReflectionProperty($config,'settings'))->setValue($config,['forms_enabled'=>true,'scheduler_enabled'=>true,'forms_dir'=>$this->queueDir.'/missing/nested']);
        $payload['company']='Lead à reprendre';$client->request('POST','/api/chat/conversations/'.$token2.'/lead',[],[],$headers,json_encode($payload));self::assertResponseIsSuccessful();self::assertSame('pending',json_decode($client->getResponse()->getContent(),true)['crm_status']);self::assertCount(1,$store->all());
        (new \ReflectionProperty($config,'settings'))->setValue($config,['forms_enabled'=>true,'scheduler_enabled'=>true,'forms_dir'=>$this->queueDir]);
        $db->delete('crm_state',['name'=>'chat_forms']);$queue->flush();self::assertCount(1,$store->all());
        $store->setState('chat_forms',['cursor'=>1]);$queue->flush();self::assertCount(2,$store->all());self::assertSame(2,$store->state('chat_forms')['cursor']);
        $store->delete($id,$store->get($id)['version']);$store->setState('chat_forms',['cursor'=>0]);$queue->flush();self::assertCount(1,$store->all());self::assertSame('Lead à reprendre',$store->all()[0]['organization']);
    }
}
