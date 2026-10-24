<?php
namespace App\Tests;

use App\Entity\User;
use App\Service\Crm\{CrmSchema,CrmStore};
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CrmWorkspaceTest extends WebTestCase
{
    public function testPresentationFiltersPriorityAndEditPreservation(): void
    {
        $_ENV['DATABASE_URL']=$_SERVER['DATABASE_URL']='sqlite:///:memory:';
        $_ENV['MAILER_DSN']=$_SERVER['MAILER_DSN']='null://null';
        $client=self::createClient(); $client->disableReboot();
        $em=self::getContainer()->get('doctrine')->getManager();
        (new SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
        $schema=new Schema(); CrmSchema::add($schema);
        foreach($schema->toSql($em->getConnection()->getDatabasePlatform()) as $sql) { $em->getConnection()->executeStatement($sql); }
        $user=(new User())->setUsername('ux-test')->setEmail('ux@example.invalid')->setPassword('unused')->setRoles(['ROLE_ADMIN']);
        $em->persist($user); $em->flush(); $client->loginUser($user);
        $store=new CrmStore($em->getConnection());
        $now=new \DateTimeImmutable('now',new \DateTimeZone('Europe/Paris'));
        $create=function(string $title,string $status,array $fields=[]) use($store):string {
            $id=$store->create(['title'=>$title,'organization'=>'Démonstration',...$fields],'public');
            $store->edit($id,[],$status,'public','','test',1);
            return $id;
        };
        $deposit=$create('Dépôt prioritaire','preparing',['deadline'=>$now->modify('-1 day')->format(DATE_ATOM),'reminder'=>$now->modify('+6 days')->format(DATE_ATOM),'next_action'=>'Finaliser le dossier']);
        $today=$create('Relance du jour','follow',['reminder'=>$now->setTime(23,59)->format(DATE_ATOM),'next_action'=>'Appeler']);
        $sent=$create('Déjà envoyé','sent',['deadline'=>$now->modify('-5 days')->format(DATE_ATOM),'reminder'=>$now->modify('+2 days')->format(DATE_ATOM)]);
        $undated=$create('Action sans date','follow',['next_action'=>'Planifier un échange']);
        $closed=$create('Classé sans suite','no_go',['next_action'=>'Ne pas relancer','deadline'=>$now->modify('-3 days')->format(DATE_ATOM)]);
        $before=$store->all();
        $c=$client->request('GET','/admin/crm?view=todo'); self::assertResponseIsSuccessful(); self::assertSelectorNotExists('.board');
        self::assertSame(['Dépôt prioritaire','Relance du jour','Déjà envoyé','Action sans date'],$c->filter('tbody tr td:first-child strong')->each(fn($node)=>$node->text()));
        self::assertSelectorTextContains('thead','Relance commerciale'); self::assertSelectorTextContains('thead','Dépôt / remise');
        self::assertStringNotContainsString('En retard',$c->filter('tbody tr')->eq(2)->text());
        self::assertStringContainsString('Non planifiée',$c->filter('tbody tr')->eq(3)->text());
        $client->request('GET','/admin/crm?view=todo&due=overdue'); self::assertSelectorCount(1,'tbody tr'); self::assertSelectorTextContains('tbody','Dépôt prioritaire');
        $client->request('GET','/admin/crm?view=todo&due=today'); self::assertSelectorCount(1,'tbody tr'); self::assertSelectorTextContains('tbody','Relance du jour');
        $client->request('GET','/admin/crm?view=todo&due=week'); self::assertSelectorCount(2,'tbody tr'); self::assertSelectorTextNotContains('tbody','Dépôt prioritaire');
        $client->request('GET','/admin/crm?view=todo&due=undated'); self::assertSelectorCount(1,'tbody tr'); self::assertSelectorTextContains('tbody','Action sans date');
        $client->request('GET','/admin/crm?view=todo&mode=board'); self::assertSelectorCount(5,'.lane'); self::assertSelectorCount(4,'.deal-card');
        $client->request('GET','/admin/crm?scope=closed&mode=list'); self::assertSelectorCount(1,'tbody tr'); self::assertSelectorTextContains('tbody','Classé sans suite');
        $client->request('GET','/admin/crm?status=no_go'); self::assertSelectorTextContains('.board','Classé sans suite');
        $client->request('GET','/admin/crm?q=introuvable'); self::assertSelectorCount(0,'.deal-card');
        $client->request('GET','/admin/crm?q=introuvable&mode=list'); self::assertSelectorTextContains('.empty-state','Aucune fiche');
        self::assertSame($before,$store->all()); // All display choices are read-only.

        $fields=['contact'=>'Contact fictif','owner'=>'Équipe Démo','amount'=>'2500','amount_kind'=>'estimated','lot'=>'1','notice_url'=>'https://example.invalid/avis','documents'=>'Document fictif','summary'=>'Besoin fictif'];
        $store->edit($deposit,$fields,'preparing','public','Note conservée','test',$store->get($deposit)['version']);
        $snapshot=$store->get($deposit);
        $c=$client->request('GET','/admin/crm/'.$deposit);
        self::assertSelectorExists('details.danger-zone a'); self::assertSelectorExists('details.secondary-actions button[value="sent"]');
        self::assertSame(1,$c->filter('textarea[name="note"]')->count());
        $form=$c->selectButton('Enregistrer')->form();
        $client->submit($form,['fields[next_action]'=>'Action corrigée','note'=>'Note UX']); self::assertResponseRedirects();
        $updated=$store->get($deposit);
        foreach([...array_keys($fields),'reminder','deadline','status','kind','decision'] as $key) {
            // datetime-local controls intentionally have minute precision.
            self::assertSame(in_array($key,['reminder','deadline'])?substr($snapshot[$key],0,16):$snapshot[$key],in_array($key,['reminder','deadline'])?substr($updated[$key],0,16):$updated[$key],$key);
        }
        self::assertSame('Action corrigée',$updated['next_action']);
        $c=$client->followRedirect(); self::assertSelectorTextContains('body','Note conservée'); self::assertSelectorTextContains('body','Note UX');
        $token=$c->filter('input[name="_token"]')->first()->attr('value');
        $client->request('POST','/admin/crm/'.$deposit.'/move',['_token'=>$token,'version'=>$updated['version'],'status'=>'sent']);
        self::assertResponseIsSuccessful(); $data=json_decode($client->getResponse()->getContent(),true);
        self::assertStringContainsString('value="sent" selected',$data['card_html']);
        self::assertStringNotContainsString('En retard',$data['card_html']); // Past submission is no longer pending.
        self::assertStringContainsString('Action corrigée',$data['card_html']);
        self::assertFalse($data['due_matches']['overdue']);
        self::assertTrue($data['due_matches']['week']);
    }
}
