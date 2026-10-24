<?php
namespace App\Service\Crm;

final class CrmDeadlines
{
    /** Whole portfolio; commercial progress is authoritative, independently of view filters. */
    public static function summarize(array $deals, ?\DateTimeImmutable $now=null): array
    {
        $zone=new \DateTimeZone('Europe/Paris');
        $now=($now ?? new \DateTimeImmutable('now',$zone))->setTimezone($zone);
        $week=$now->modify('+7 days');
        $lists=['soon'=>[],'later'=>[],'awaiting'=>[]];$missing=[];$overdue=0;
        foreach ($deals as $d) {
            if (!empty($d['merged_into']) || !empty($d['review_required']) || ($d['origin']??'')==='email') { continue; }
            $status=$d['status']??'qualify';
            $item=['id'=>$d['id'],'title'=>$d['title'],'kind'=>($d['kind']??'')==='public'||($d['origin']??'')==='watch'?'Marché public':'Proposition client','date'=>null,'overdue'=>false];
            if (in_array($status,['sent','discussion'],true)) {
                $date=self::date($d['reminder']??'',$zone);
                $item['date']=$date?->format(DATE_ATOM);$item['overdue']=$date!==null && $date<$now;
                $lists['awaiting'][]=$item;
                continue;
            }
            $selected=in_array($status,['follow','preparing'],true) || ($status==='qualify' && ($d['origin']??'')==='watch' && ($d['decision']??'')==='follow');
            if (!$selected) { continue; }
            $date=self::date($d['deadline']??'',$zone);
            if (!$date) { $missing[]=$item;continue; }
            $item['date']=$date->format(DATE_ATOM);$item['overdue']=$date<$now;
            if ($item['overdue']) { ++$overdue; }
            $lists[$date<=$week?'soon':'later'][]=$item;
        }
        foreach ($lists as &$items) {
            usort($items,fn($a,$b)=>(($a['date']?strtotime($a['date']):PHP_INT_MAX)<=>($b['date']?strtotime($b['date']):PHP_INT_MAX)) ?: strcmp($a['title'],$b['title']) ?: strcmp($a['id'],$b['id']));
        }
        unset($items);
        return ['soon'=>count($lists['soon']),'later'=>count($lists['later']),'awaiting'=>count($lists['awaiting']),'overdue'=>$overdue,'missing'=>count($missing),'missing_items'=>$missing,'lists'=>$lists];
    }
    private static function date(string $value,\DateTimeZone $zone): ?\DateTimeImmutable
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}(T.*)?$/D',$value)) { return null; }
        try { $date=new \DateTimeImmutable($value,$zone);return \DateTimeImmutable::getLastErrors()===false?$date:null; }
        catch (\Exception) { return null; }
    }
}
