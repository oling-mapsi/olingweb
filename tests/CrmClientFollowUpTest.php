<?php
namespace App\Tests;

use App\Service\Crm\{ClientOfferFollowUp,CrmSchema,CrmStore};
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;

final class CrmClientFollowUpTest extends TestCase
{
    /** @dataProvider dateCases */
    public function testSevenCalendarDaysInParis(string $now, string $expected): void
    {
        $fields=ClientOfferFollowUp::onTransition(['status'=>'preparing','origin'=>'manual'],'sent','private',new \DateTimeImmutable($now));
        self::assertSame($expected,$fields['reminder']);
        self::assertSame('Relancer le client sur l’offre envoyée',$fields['next_action']);
    }
    public function dateCases(): array
    {
        return [
            ['2026-10-24T15:00:00+02:00','2026-10-31T14:00:00Z'],
            ['2026-03-28T15:00:00+01:00','2026-04-04T13:00:00Z'],
            ['2026-12-28T09:30:00+01:00','2027-01-04T08:30:00Z'],
        ];
    }
    public function testOnlyNewPrivateClientSentTransitionsQualify(): void
    {
        $now=new \DateTimeImmutable('2026-10-24T15:00:00+02:00');
        foreach (['manual','form','email'] as $origin) {
            self::assertNotEmpty(ClientOfferFollowUp::onTransition(['status'=>'follow','origin'=>$origin],'sent','private',$now));
        }
        foreach ([['preparing','manual','sent','public'],['preparing','watch','sent','private'],['sent','form','sent','private'],['sent','manual','discussion','private'],['follow','manual','preparing','private']] as [$from,$origin,$to,$kind]) {
            self::assertSame([],ClientOfferFollowUp::onTransition(['status'=>$from,'origin'=>$origin],$to,$kind,$now));
        }
    }
    public function testReminderIsAuditedAtomicEditableAndNotRescheduledOnSave(): void
    {
        $db=DriverManager::getConnection(['driver'=>'pdo_sqlite','memory'=>true]); $schema=new Schema(); CrmSchema::add($schema);
        foreach ($schema->toSql($db->getDatabasePlatform()) as $sql) { $db->executeStatement($sql); }
        $store=new CrmStore($db);
        $id=$store->create(['title'=>'Offre fictive','next_action'=>'Préparer le devis','reminder'=>'2020-01-01T10:00:00Z','deadline'=>'2026-10-01T10:00:00Z']);
        $before=new \DateTimeImmutable('now',new \DateTimeZone('Europe/Paris'));
        $store->edit($id,[],'sent','private','','test',1); $deal=$store->get($id);
        $after=new \DateTimeImmutable('now',new \DateTimeZone('Europe/Paris'));
        $reminder=new \DateTimeImmutable($deal['reminder']);
        self::assertGreaterThanOrEqual($before->modify('+7 days')->getTimestamp(),$reminder->getTimestamp());
        self::assertLessThanOrEqual($after->modify('+7 days')->getTimestamp(),$reminder->getTimestamp());
        self::assertSame('2026-10-01T10:00:00Z',$deal['deadline']);
        $change=$store->history($id)[0]['data']['changes'];
        self::assertSame('Préparer le devis',$change['next_action']['before']);
        self::assertSame($deal['reminder'],$change['reminder']['after']);
        $store->edit($id,['summary'=>'Texte corrigé'],'sent','private','','test',2);
        self::assertSame($deal['reminder'],$store->get($id)['reminder']);
        $store->edit($id,['reminder'=>'2026-11-20T10:00:00Z','next_action'=>'Appeler le responsable'],'sent','private','','test',3);
        self::assertSame('2026-11-20T10:00:00Z',$store->get($id)['reminder']);
        $store->edit($id,[],'discussion','private','','test',4);
        $snapshot=$store->get($id); $events=$store->history($id);
        try { $store->edit($id,[],'sent','private','','test',4); self::fail('A stale version must fail'); }
        catch (\RuntimeException) { self::assertSame($snapshot,$store->get($id)); self::assertSame($events,$store->history($id)); }
        $store->edit($id,[],'sent','private','','test',5);
        self::assertSame('Relancer le client sur l’offre envoyée',$store->get($id)['next_action']);
        self::assertNotSame('2026-11-20T10:00:00Z',$store->get($id)['reminder']);
    }
}
