<?php
namespace App\Tests;

use App\Entity\User;
use App\Service\Crm\{CrmSchema,CrmStore};
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CrmDceTest extends WebTestCase
{
    public function testManualDceConfirmationPersistsWithoutChangingTheDealOrImports(): void
    {
        $_ENV['DATABASE_URL']=$_SERVER['DATABASE_URL']='sqlite:///:memory:';
        $_ENV['MAILER_DSN']=$_SERVER['MAILER_DSN']='null://null';
        $client=self::createClient(); $client->disableReboot();
        $em=self::getContainer()->get('doctrine')->getManager();
        (new SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
        $schema=new Schema(); CrmSchema::add($schema);
        foreach ($schema->toSql($em->getConnection()->getDatabasePlatform()) as $sql) { $em->getConnection()->executeStatement($sql); }
        $admin=(new User())->setUsername('dce-admin')->setEmail('dce@example.invalid')->setPassword('unused')->setRoles(['ROLE_ADMIN']);
        $em->persist($admin); $em->flush(); $client->loginUser($admin);
        $store=new CrmStore($em->getConnection());
        $source=['origin'=>'watch','external_id'=>'notice-1','identity'=>'notice','title'=>'Consultation fictive','kind'=>'public','fields'=>['deadline'=>'2026-12-01T12:00Z','reminder'=>'2026-11-20T10:00Z','next_action'=>'Analyser le dossier','dce_received'=>true]];
        $id=$store->ingest($source)['id'];
        $private=$store->create(['title'=>'Client fictif'],'private');
        $manual=$store->create(['title'=>'Marché manuel'],'public');
        $legacyWatch=$store->create(['title'=>'Veille historique'],'private','watch');
        self::assertFalse($store->get($id)['dce_received']); // Source cannot confirm receipt.
        self::assertFalse($store->get($manual)['dce_received']); // Legacy JSON needs no migration.
        foreach (['','?segment=markets','?view=todo&mode=board'] as $query) {
            $c=$client->request('GET','/admin/crm'.$query); self::assertResponseIsSuccessful();
            if ($query!=='?view=todo&mode=board') { self::assertSelectorCount(3,'.dce-toggle'); }
        }
        $c=$client->request('GET','/admin/crm');
        self::assertSelectorNotExists('[data-id="'.$private.'"] .dce-toggle');
        $token=$c->filter('.board')->attr('data-token');
        $url='/admin/crm/'.$id.'/dce'; $before=$store->get($id);
        foreach ([[],['received'=>'yes'],['received'=>'2']] as $invalid) {
            $client->request('POST',$url,['_token'=>$token,'version'=>$before['version']]+$invalid);
            self::assertResponseStatusCodeSame(409); self::assertSame($before,$store->get($id));
        }
        $client->request('POST',$url,['_token'=>'invalid','version'=>$before['version'],'received'=>'1']);
        self::assertResponseStatusCodeSame(403); self::assertSame($before,$store->get($id));
        $client->request('POST',$url,['_token'=>$token,'version'=>$before['version'],'received'=>'1']);
        self::assertResponseIsSuccessful();
        $result=json_decode($client->getResponse()->getContent(),true); self::assertTrue($result['dce_received']);
        $updated=$store->get($id); self::assertSame($before['version']+1,$result['version']);
        foreach (['status','decision','kind','deadline','reminder','next_action'] as $key) { self::assertSame($before[$key],$updated[$key]); }
        self::assertTrue($updated['manual_fields']['dce_received']);
        $history=$store->history($id);
        self::assertCount(1,array_filter($history,fn($e)=>isset($e['data']['changes']['dce_received'])));
        // Stale DCE and status requests must neither overwrite nor add an event.
        $client->request('POST',$url,['_token'=>$token,'version'=>$before['version'],'received'=>'0']);
        self::assertResponseStatusCodeSame(409); self::assertSame($updated,$store->get($id)); self::assertCount(count($history),$store->history($id));
        $client->request('POST','/admin/crm/'.$id.'/move',['_token'=>$token,'version'=>$before['version'],'status'=>'follow']);
        self::assertResponseStatusCodeSame(409); self::assertSame($updated,$store->get($id));
        $source['external_id']='notice-2'; $source['fields']['dce_received']=false; $store->ingest($source);
        self::assertTrue($store->get($id)['dce_received']);
        $client->request('GET','/admin/crm?segment=markets');
        self::assertSelectorExists('[data-id="'.$id.'"] .dce-toggle[aria-pressed="true"]');
        $client->request('POST','/admin/crm/'.$id.'/move',['_token'=>$token,'version'=>$store->get($id)['version'],'status'=>'follow']);
        self::assertResponseIsSuccessful();
        $move=json_decode($client->getResponse()->getContent(),true);
        self::assertStringContainsString('aria-pressed="true"',$move['card_html']);
        $client->request('POST',$url,['_token'=>$token,'version'=>$move['version'],'received'=>'0']);
        self::assertResponseIsSuccessful(); self::assertFalse($store->get($id)['dce_received']);
        $source['external_id']='notice-3'; $source['fields']['dce_received']=true; $store->ingest($source);
        self::assertFalse($store->get($id)['dce_received']);
        foreach ([$manual,$legacyWatch] as $publicId) {
            $client->request('POST','/admin/crm/'.$publicId.'/dce',['_token'=>$token,'version'=>$store->get($publicId)['version'],'received'=>'1']);
            self::assertResponseIsSuccessful(); self::assertTrue($store->get($publicId)['dce_received']);
        }
        $privateBefore=$store->get($private);
        $client->request('POST','/admin/crm/'.$private.'/dce',['_token'=>$token,'version'=>$privateBefore['version'],'received'=>'1']);
        self::assertResponseStatusCodeSame(409); self::assertSame($privateBefore,$store->get($private));
        $reader=(new User())->setUsername('dce-reader')->setEmail('reader@example.invalid')->setPassword('unused')->setRoles(['ROLE_USER']);
        $em->persist($reader); $em->flush(); $client->loginUser($reader); $before=$store->get($id);
        $client->request('POST',$url,['_token'=>$token,'version'=>$before['version'],'received'=>'1']);
        self::assertResponseStatusCodeSame(403); self::assertSame($before,$store->get($id));
    }
}
