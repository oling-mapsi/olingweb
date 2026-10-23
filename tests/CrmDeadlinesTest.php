<?php
namespace App\Tests;
use App\Service\Crm\CrmDeadlines;
use PHPUnit\Framework\TestCase;
final class CrmDeadlinesTest extends TestCase
{
    public function testEligibilityAndTimeBoundaries(): void
    {
        $now=new \DateTimeImmutable('2026-10-23T12:00:00+02:00');
        $base=['id'=>'a','title'=>'Marché fictif','kind'=>'public','origin'=>'watch','decision'=>'follow','status'=>'qualify','deadline'=>'2026-10-24T10:00:00Z'];
        $excluded=[];
        foreach (['no_go','won','lost','archived','sent','discussion'] as $status) { $excluded[]=array_replace($base,['status'=>$status]); }
        foreach (['review','reject'] as $decision) { $excluded[]=array_replace($base,['decision'=>$decision]); }
        $excluded[]=array_replace($base,['merged_into'=>'other']);
        $excluded[]=array_replace($base,['review_required'=>true]);
        $excluded[]=array_replace($base,['origin'=>'manual','kind'=>'private','status'=>'follow']);
        $excluded[]=array_replace($base,['origin'=>'manual','status'=>'qualify']);
        self::assertSame(['soon'=>0,'overdue'=>0,'missing'=>0,'total'=>0,'next'=>[]],CrmDeadlines::summarize($excluded,$now));
        $rows=[$base,
            array_replace($base,['id'=>'now','deadline'=>'2026-10-23T10:00:00Z']),
            array_replace($base,['id'=>'week','deadline'=>'2026-10-30T10:00:00Z']),
            array_replace($base,['id'=>'later','deadline'=>'2026-10-30T10:00:01Z']),
            array_replace($base,['id'=>'late','deadline'=>'2026-10-23T09:59:59Z']),
            array_replace($base,['id'=>'missing','deadline'=>'']),
            array_replace($base,['id'=>'invalid','deadline'=>'invalid']),
            array_replace($base,['id'=>'manual','origin'=>'manual','status'=>'preparing','deadline'=>'2026-10-25T12:00:00+01:00'])];
        $result=CrmDeadlines::summarize([...$rows,...$excluded],$now);
        self::assertSame(4,$result['soon']); self::assertSame(1,$result['overdue']); self::assertSame(2,$result['missing']); self::assertSame(5,$result['total']);
        self::assertSame(['now','a','manual'],array_column($result['next'],'id'));
    }
}
