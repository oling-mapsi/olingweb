<?php
namespace App\Tests;

use App\Entity\User;
use App\Service\Crm\{CrmSchema,CrmStore};
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CrmCcTest extends WebTestCase
{
    public function testB2bCadrageIsManualReversibleAndIndependentFromDce(): void
    {
        $_ENV['DATABASE_URL']=$_SERVER['DATABASE_URL']='sqlite:///:memory:';
        $_ENV['MAILER_DSN']=$_SERVER['MAILER_DSN']='null://null';
        $client=self::createClient(); $client->disableReboot();
        $em=self::getContainer()->get('doctrine')->getManager();
        (new SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
        $schema=new Schema(); CrmSchema::add($schema);
        foreach ($schema->toSql($em->getConnection()->getDatabasePlatform()) as $sql) { $em->getConnection()->executeStatement($sql); }
        $user=(new User())->setUsername('cc-test')->setEmail('cc@example.invalid')->setPassword('unused')->setRoles(['ROLE_ADMIN']);
        $em->persist($user); $em->flush(); $client->loginUser($user);
        $store=new CrmStore($em->getConnection());
        $source=['origin'=>'form','external_id'=>'cc-1','identity'=>'cc-client','title'=>'Cadrage fictif','kind'=>'private','fields'=>['next_action'=>'Préparer une proposition','reminder'=>'2026-12-01T10:00Z','deadline'=>'2026-12-04T12:00Z','cc_received'=>true]];
        $id=$store->ingest($source)['id'];
        $market=$store->create(['title'=>'Marché fictif'],'public');
        $watch=$store->create(['title'=>'Veille historique'],'private','watch');
        $c=$client->request('GET','/admin/crm'); self::assertResponseIsSuccessful();
        self::assertSelectorCount(1,'.cc-toggle'); self::assertSelectorCount(2,'.dce-toggle');
        self::assertSelectorExists('[data-id="'.$id.'"] .cc-toggle[aria-pressed="false"]');
        self::assertFalse($store->get($id)['cc_received']);
        $token=$c->filter('.board')->attr('data-token'); $url='/admin/crm/'.$id.'/cc'; $before=$store->get($id);
        foreach (['yes','',null] as $value) {
            $client->request('POST',$url,['_token'=>$token,'version'=>$before['version'],'received'=>$value]);
            self::assertResponseStatusCodeSame(409); self::assertSame($before,$store->get($id));
        }
        $client->request('POST',$url,['_token'=>'invalid','version'=>$before['version'],'received'=>'1']);
        self::assertResponseStatusCodeSame(403); self::assertSame($before,$store->get($id));
        $client->request('POST',$url,['_token'=>$token,'version'=>$before['version'],'received'=>'1']);
        self::assertResponseIsSuccessful(); $after=$store->get($id);
        self::assertTrue($after['cc_received']); self::assertFalse($after['dce_received']);
        self::assertSame($before['version']+1,$after['version']);
        foreach (['status','decision','kind','deadline','reminder','next_action'] as $field) { self::assertSame($before[$field],$after[$field]); }
        $history=$store->history($id); self::assertCount(1,array_filter($history,fn($e)=>isset($e['data']['changes']['cc_received'])));
        $client->request('POST',$url,['_token'=>$token,'version'=>$before['version'],'received'=>'0']);
        self::assertResponseStatusCodeSame(409); self::assertSame($after,$store->get($id)); self::assertCount(count($history),$store->history($id));
        $source['external_id']='cc-2'; $source['fields']['cc_received']=false; $store->ingest($source);
        self::assertTrue($store->get($id)['cc_received']);
        $client->request('POST','/admin/crm/'.$id.'/move',['_token'=>$token,'version'=>$store->get($id)['version'],'status'=>'follow']);
        self::assertResponseIsSuccessful(); $move=json_decode($client->getResponse()->getContent(),true);
        self::assertStringContainsString('aria-pressed="true"',$move['card_html']);
        $client->request('GET','/admin/crm?segment=clients'); self::assertSelectorExists('.cc-toggle[aria-pressed="true"]'); self::assertSelectorNotExists('.dce-toggle');
        $client->request('POST',$url,['_token'=>$token,'version'=>$store->get($id)['version'],'received'=>'0']);
        self::assertResponseIsSuccessful(); self::assertFalse($store->get($id)['cc_received']);
        $source['external_id']='cc-3'; $source['fields']['cc_received']=true; $store->ingest($source);
        self::assertFalse($store->get($id)['cc_received']);
        foreach ([$market,$watch] as $publicId) {
            $publicBefore=$store->get($publicId);
            $client->request('POST','/admin/crm/'.$publicId.'/cc',['_token'=>$token,'version'=>$publicBefore['version'],'received'=>'1']);
            self::assertResponseStatusCodeSame(409); self::assertSame($publicBefore,$store->get($publicId));
        }
        $reader=(new User())->setUsername('cc-reader')->setEmail('cc-reader@example.invalid')->setPassword('unused')->setRoles(['ROLE_USER']);
        $em->persist($reader); $em->flush(); $client->loginUser($reader); $before=$store->get($id);
        $client->request('POST',$url,['_token'=>$token,'version'=>$before['version'],'received'=>'1']);
        self::assertResponseStatusCodeSame(403); self::assertSame($before,$store->get($id));
    }
}
