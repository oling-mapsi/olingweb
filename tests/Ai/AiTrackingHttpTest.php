<?php
namespace App\Tests\Ai;
use App\Entity\User;
use App\Service\Ai\{AiSchema,AiTrackingSchema,AiTrackingAccess,AiTrackingContract};
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
final class AiTrackingHttpTest extends WebTestCase
{
    public function testDisabledByDefaultAndScopedFixtureMcp():void
    {
        $c=self::createClient();$c->disableReboot();$em=self::getContainer()->get('doctrine')->getManager();
        (new SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());$s=new Schema();AiSchema::add($s);AiTrackingSchema::add($s);foreach($s->toSql($em->getConnection()->getDatabasePlatform()) as $sql)$em->getConnection()->executeStatement($sql);
        $u=(new User())->setUsername('native-admin')->setEmail('native@example.invalid')->setPassword('unused')->setRoles(['ROLE_ADMIN']);$em->persist($u);$em->flush();
        $rpc=function(string $method,array $params=[],string $origin='https://chatgpt.com',string $host='https://ai.oling.fr',string $auth='Bearer SYNTHETIC_ONLY')use($c){$c->request('POST',$host.'/api/ai/mcp',[],[],['CONTENT_TYPE'=>'application/json','HTTP_AUTHORIZATION'=>$auth,'HTTP_ORIGIN'=>$origin],json_encode(['jsonrpc'=>'2.0','id'=>1,'method'=>$method,'params'=>$params]));return json_decode($c->getResponse()->getContent(),true);};
        self::assertFalse((new AiTrackingAccess())->available());
        $access=new class($u->getId()) extends AiTrackingAccess {
            public bool $active=false;public array $scopes=AiTrackingContract::SCOPES;public function __construct(public int $owner){}
            public function available():bool{return $this->active;}
            public function resolve(Request $r):?array{return $r->headers->get('Authorization')==='Bearer SYNTHETIC_ONLY'?['owner'=>$this->owner,'actor'=>'fixture-pilot','scopes'=>$this->scopes]:null;}
        };
        self::getContainer()->set(AiTrackingAccess::class,$access);
        $rpc('initialize');self::assertResponseStatusCodeSame(503);$access->active=true;
        $rpc('initialize',[],'https://chatgpt.com','https://ai.oling.fr','Bearer WRONG');self::assertResponseStatusCodeSame(401);
        $rpc('initialize',[],'https://evil.invalid');self::assertResponseStatusCodeSame(403);
        $rpc('initialize',[],'https://chatgpt.com','http://ai.oling.fr');self::assertResponseStatusCodeSame(403);
        $rpc('initialize',[],'https://chatgpt.com','https://oling.fr');self::assertResponseStatusCodeSame(404);
        $result=$rpc('tools/list');self::assertCount(8,$result['result']['tools']);
        $args=['event_id'=>'http-native-create-fixture','title'=>'MCP fixture','brief'=>'Synthetic only','project'=>'demo'];
        $reply=$rpc('tools/call',['name'=>'ai_mission_create','arguments'=>$args]);$created=json_decode($reply['result']['content'][0]['text'],true);self::assertFalse($created['execution_triggered']);
        self::assertSame($reply,$rpc('tools/call',['name'=>'ai_mission_create','arguments'=>$args]));
        $args['title']='changed';self::assertSame(-32009,$rpc('tools/call',['name'=>'ai_mission_create','arguments'=>$args])['error']['code']);
        $access->scopes=['missions:read'];self::assertCount(2,$rpc('tools/list')['result']['tools']);self::assertSame(-32003,$rpc('tools/call',['name'=>'ai_mission_create','arguments'=>$args])['error']['code']);
        $c->request('POST','https://ai.oling.fr/api/ai/mcp',[],[],['CONTENT_TYPE'=>'application/json','HTTP_AUTHORIZATION'=>'Bearer SYNTHETIC_ONLY'],str_repeat('x',65537));self::assertResponseStatusCodeSame(413);
        $reader=(new User())->setUsername('native-reader')->setEmail('reader@example.invalid')->setPassword('unused')->setRoles(['ROLE_USER']);$em->persist($reader);$em->flush();$access->owner=$reader->getId();$rpc('tools/list');self::assertResponseStatusCodeSame(403);
    }
}
