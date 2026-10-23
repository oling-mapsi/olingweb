<?php
namespace App\Service\Crm;

final class CrmDeadlines
{
    /** Deadlines belong to the whole portfolio, independently of display filters. */
    public static function summarize(array $deals, ?\DateTimeImmutable $now=null): array
    {
        $now ??= new \DateTimeImmutable('now');
        $week=$now->modify('+7 days'); $upcoming=[]; $overdue=0; $soon=0; $missing=0;
        foreach ($deals as $d) {
            if (!empty($d['merged_into']) || !empty($d['review_required'])) { continue; }
            if (($d['kind']??'')!=='public' && ($d['origin']??'')!=='watch') { continue; }
            if (in_array($d['status'],['no_go','won','lost','archived','sent','discussion'],true)) { continue; }
            // Watch decisions are explicit; manually entered tenders use their commercial stage.
            $selected=($d['origin']??'')==='watch' ? ($d['decision']??'review')==='follow' : in_array($d['status'],['follow','preparing'],true);
            if (!$selected) { continue; }
            if (empty($d['deadline'])) { ++$missing; continue; }
            try { $date=new \DateTimeImmutable($d['deadline']); } catch (\Exception) { ++$missing; continue; }
            if ($date<$now) { ++$overdue; continue; }
            if ($date<=$week) { ++$soon; }
            $upcoming[]=['id'=>$d['id'],'title'=>$d['title'],'deadline'=>$date->format(DATE_ATOM)];
        }
        usort($upcoming,fn($a,$b)=>strtotime($a['deadline'])<=>strtotime($b['deadline']) ?: strcmp($a['id'],$b['id']));
        return ['soon'=>$soon,'overdue'=>$overdue,'missing'=>$missing,'total'=>count($upcoming),'next'=>array_slice($upcoming,0,3)];
    }
}
