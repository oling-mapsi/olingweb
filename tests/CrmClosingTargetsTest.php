<?php
namespace App\Tests;

use App\Entity\User;
use App\Service\Crm\{CrmSchema,CrmStore};
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CrmClosingTargetsTest extends WebTestCase
{
    public function testClosingPersistsAndCountsRespectPortfolioContext(): void
    {
        $_ENV['DATABASE_URL']=$_SERVER['DATABASE_URL']='sqlite:///:memory:';
        $_ENV['MAILER_DSN']=$_SERVER['MAILER_DSN']='null://null';
        $client=self::createClient(); $client->disableReboot();
        $em=self::getContainer()->get('doctrine')->getManager();
        (new SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
        $schema=new Schema(); CrmSchema::add($schema);
        foreach ($schema->toSql($em->getConnection()->getDatabasePlatform()) as $sql) { $em->getConnection()->executeStatement($sql); }
        $user=(new User())->setUsername('closing-test')->setEmail('closing@example.invalid')->setPassword('unused')->setRoles(['ROLE_ADMIN']);
        $em->persist($user); $em->flush(); $client->loginUser($user);
        $store=new CrmStore($em->getConnection());
        $create=function($title,$status,$kind='private') use($store) {
            $id=$store->create(['title'=>$title,'organization'=>'Fixture'],$kind);
            $store->edit($id,[],$status,$kind,'','fixture',1); return $id;
        };
        $won=$create('Alpha gagnée','won'); $lost=$create('Alpha perdue','lost');
        $moving=$create('Alpha à clôturer','sent');
        $create('Beta gagnée','won'); $create('Alpha marché','won','public'); $create('Alpha sans suite','no_go');
        $query='?view=portfolio&segment=clients&q=Alpha&scope=active';
        $c=$client->request('GET','/admin/crm'.$query); self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('[data-closed-count="won"]','1'); self::assertSelectorTextSame('[data-closed-count="lost"]','1');
        self::assertSelectorCount(1,'.deal-card'); self::assertSelectorExists('.card-status option[value="won"]');
        self::assertSame('/admin/crm/export.csv',$c->selectLink('Exporter CSV')->attr('href'));
        self::assertSame('lost',$c->filter('.crm-actions > .closure-targets')->children()->first()->attr('data-close-status'));
        if ($preview=getenv('CRM_PREVIEW_HTML')) { file_put_contents($preview,$client->getResponse()->getContent()); }
        $token=$c->filter('.board')->attr('data-token');
        foreach (['lost','won','follow'] as $status) {
            $version=$store->get($moving)['version'];
            $client->request('POST','/admin/crm/'.$moving.'/move'.$query,['_token'=>$token,'version'=>$version,'status'=>$status]);
            self::assertResponseIsSuccessful(); $data=json_decode($client->getResponse()->getContent(),true);
            self::assertTrue($data['saved']); self::assertSame($status,$store->get($moving)['status']);
            self::assertSame(['lost'=>$status==='lost'?2:1,'won'=>$status==='won'?2:1],$data['closed_counts']);
            $history=count($store->history($moving));
            // A duplicate/stale request must not apply a second transition or event.
            $client->request('POST','/admin/crm/'.$moving.'/move'.$query,['_token'=>$token,'version'=>$version,'status'=>$status]);
            self::assertResponseStatusCodeSame(409); self::assertSame($history,count($store->history($moving)));
            $client->request('GET','/admin/crm'.$query);
            self::assertSelectorTextSame('[data-closed-count="won"]',(string)$data['closed_counts']['won']);
            self::assertSelectorTextSame('[data-closed-count="lost"]',(string)$data['closed_counts']['lost']);
            self::assertSelectorCount($status==='follow'?1:0,'.deal-card');
        }
        $before=$store->get($moving);
        $client->request('POST','/admin/crm/'.$moving.'/move'.$query,['_token'=>'invalid','version'=>$before['version'],'status'=>'won']);
        self::assertResponseStatusCodeSame(403); self::assertSame($before,$store->get($moving));
        $client->request('POST','/admin/crm/'.$moving.'/move'.$query,['_token'=>$token,'version'=>$before['version'],'status'=>'unknown']);
        self::assertResponseStatusCodeSame(409); self::assertSame($before,$store->get($moving));
        foreach (['all','closed','active'] as $scope) {
            $client->request('GET','/admin/crm?view=portfolio&segment=clients&q=Alpha&scope='.$scope);
            self::assertSelectorTextSame('[data-closed-count="won"]','1'); self::assertSelectorTextSame('[data-closed-count="lost"]','1');
        }
        $client->request('GET','/admin/crm?segment=markets&q=Alpha');
        self::assertSelectorTextSame('[data-closed-count="won"]','1'); self::assertSelectorTextSame('[data-closed-count="lost"]','0');
        $client->request('GET','/admin/crm?segment=clients&q=absent'); self::assertSelectorTextSame('[data-closed-count="won"]','0');
        $client->request('GET','/admin/crm?segment=clients&origin=watch'); self::assertSelectorTextSame('[data-closed-count="won"]','0');
        $client->request('GET','/admin/crm?segment=clients&due=week'); self::assertSelectorTextSame('[data-closed-count="won"]','0');
        $client->request('GET','/admin/crm?mode=list'); self::assertSelectorNotExists('.closure-targets');
        $client->request('GET','/admin/crm/export.csv'); self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type','text/csv; charset=UTF-8');
        $csv=$client->getInternalResponse()->getContent(); self::assertStringContainsString('Alpha gagnée',$csv); self::assertStringContainsString('Alpha perdue',$csv);
        $reader=(new User())->setUsername('closing-reader')->setEmail('reader@example.invalid')->setPassword('unused')->setRoles(['ROLE_USER']);
        $em->persist($reader); $em->flush(); $client->loginUser($reader);
        $client->request('POST','/admin/crm/'.$moving.'/move',['_token'=>$token,'version'=>$before['version'],'status'=>'won']);
        self::assertResponseStatusCodeSame(403); self::assertSame($before,$store->get($moving));
    }
}
