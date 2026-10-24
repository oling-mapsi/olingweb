<?php
namespace App\Tests;
use App\Service\Crm\CrmDeadlines;
use PHPUnit\Framework\TestCase;
final class CrmDeadlinesTest extends TestCase
{
    private function deal(array $overrides=[]): array
    {
        return array_replace(['id'=>'a','title'=>'Marché fictif','kind'=>'public','origin'=>'watch','decision'=>'review','status'=>'preparing','deadline'=>'2026-10-25T10:00:00Z'],$overrides);
    }
    public function testBothMarketAndClientProgressCountWithoutWatchDecision(): void
    {
        $result=CrmDeadlines::summarize([
            $this->deal(['status'=>'follow']),
            $this->deal(['id'=>'client','kind'=>'private','origin'=>'manual']),
            $this->deal(['id'=>'chosen','status'=>'qualify','decision'=>'follow']),
            $this->deal(['id'=>'manual-progress','decision'=>'reject']),
        ],new \DateTimeImmutable('2026-10-24T12:00:00+02:00'));
        self::assertSame(4,$result['soon']);self::assertSame(0,$result['later']);self::assertSame(0,$result['awaiting']);
    }
    public function testSevenDayBoundaryAcrossDaylightSavingAndOverdue(): void
    {
        $now=new \DateTimeImmutable('2026-10-24T12:00:00+02:00');
        $rows=[
            $this->deal(['id'=>'now','deadline'=>'2026-10-24T10:00:00Z']),
            $this->deal(['id'=>'week','deadline'=>'2026-10-31T11:00:00Z']), // J+7, Paris changes to winter time.
            $this->deal(['id'=>'later','deadline'=>'2026-10-31T11:00:01Z']),
            $this->deal(['id'=>'late','deadline'=>'2026-10-24T09:59:59Z']),
            $this->deal(['id'=>'missing','deadline'=>'']),
            $this->deal(['id'=>'invalid','deadline'=>'invalid']),
            $this->deal(['id'=>'invalid-calendar','deadline'=>'2026-02-30T10:00:00Z']),
        ];
        $result=CrmDeadlines::summarize($rows,$now);
        self::assertSame(3,$result['soon']);self::assertSame(1,$result['later']);self::assertSame(1,$result['overdue']);self::assertSame(3,$result['missing']);
        self::assertSame(['late','now','week'],array_column($result['lists']['soon'],'id'));
        self::assertSame(['later'],array_column($result['lists']['later'],'id'));
        self::assertSame(4,$result['soon']+$result['later']);
    }
    public function testClosedUnselectedMergedAndLegacyRecordsAreExcluded(): void
    {
        $rows=[];
        foreach(['no_go','won','lost','archived','qualify'] as $status){$rows[]=$this->deal(['status'=>$status]);}
        $rows[]=$this->deal(['merged_into'=>'other']);$rows[]=$this->deal(['review_required'=>true]);
        $rows[]=$this->deal(['origin'=>'email']);$rows[]=$this->deal(['origin'=>'email','status'=>'sent']);
        $result=CrmDeadlines::summarize($rows);
        foreach(['soon','later','awaiting','missing','overdue'] as $key){self::assertSame(0,$result[$key]);}
    }
    public function testSentAndDiscussionNeedFollowupEvenWithoutDeadline(): void
    {
        $result=CrmDeadlines::summarize([
            $this->deal(['id'=>'sent','status'=>'sent','deadline'=>'','kind'=>'private','origin'=>'manual']),
            $this->deal(['id'=>'discussion','status'=>'discussion','deadline'=>'2020-01-01T10:00:00Z','reminder'=>'2026-10-24T09:00:00Z']),
            $this->deal(['id'=>'closed','status'=>'won']),
        ],new \DateTimeImmutable('2026-10-24T10:00:00Z'));
        self::assertSame(2,$result['awaiting']);self::assertSame(0,$result['soon']);self::assertSame(0,$result['later']);self::assertSame(0,$result['missing']);
        self::assertSame(['discussion','sent'],array_column($result['lists']['awaiting'],'id'));
        self::assertTrue($result['lists']['awaiting'][0]['overdue']);self::assertNull($result['lists']['awaiting'][1]['date']);
    }
    public function testListsContainEveryCountedRecord(): void
    {
        $rows=[];for($i=0;$i<12;++$i){$rows[]=$this->deal(['id'=>'test-'.$i]);}
        $result=CrmDeadlines::summarize($rows,new \DateTimeImmutable('2026-10-24T10:00:00Z'));
        self::assertSame(12,$result['soon']);self::assertCount(12,$result['lists']['soon']);
    }
}
