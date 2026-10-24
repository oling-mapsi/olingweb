<?php
namespace App\Tests;

use App\Service\Crm\CrmFeedStatus;
use PHPUnit\Framework\TestCase;

final class CrmFeedStatusTest extends TestCase
{
    public function testOnlyEnabledFeedsAppearAndConnectionIsNotAnImport(): void
    {
        $now=strtotime('2026-10-24T18:00:00Z');
        self::assertSame([],CrmFeedStatus::summarize([],[],[],[],[],$now));
        $feeds=CrmFeedStatus::summarize(['watch'=>true],[],['last_success'=>'2026-10-24T17:00:00Z'],[],[],$now);
        self::assertCount(1,$feeds);
        self::assertSame('Actif · connexion confirmée',$feeds[0]['status']);
        $feeds=CrmFeedStatus::summarize(['watch'=>true],['last_success'=>'2026-10-23T17:00:00Z'],[],[],[],$now);
        self::assertSame('Dernier import réussi',$feeds[0]['detail']);
    }

    public function testFormsRequireRecentHealthyRecoveryAndMapsReceipt(): void
    {
        $now=strtotime('2026-10-24T18:00:00Z');
        $config=['forms'=>true,'scheduler'=>true];
        $forms=['last_success'=>'2026-10-24T17:59:00Z'];
        $mapsi=['last_success'=>'2026-10-23T10:00:00Z'];
        $feeds=CrmFeedStatus::summarize($config,[],[],$forms,$mapsi,$now);
        self::assertSame(['Formulaires oling.fr','Formulaires mapsi.fr'],array_column($feeds,'name'));
        self::assertSame('Actifs · contrôle opérationnel',$feeds[0]['status']);
        self::assertSame('Actifs · réception confirmée',$feeds[1]['status']);
        foreach ([['last_success'=>'2026-10-24T16:00:00Z'],$forms+['pending'=>['receipt-1']],$forms+['error'=>'failed']] as $unhealthy) {
            $feeds=CrmFeedStatus::summarize($config,[],[],$unhealthy,$mapsi,$now);
            self::assertSame('Actifs · contrôle à vérifier',$feeds[0]['status']);
            self::assertSame('Actifs · réception à vérifier',$feeds[1]['status']);
        }
        $feeds=CrmFeedStatus::summarize(['forms'=>true,'scheduler'=>false],[],[],$forms,$mapsi,$now);
        self::assertSame('Actifs · contrôle à vérifier',$feeds[0]['status']);
        $feeds=CrmFeedStatus::summarize($config,[],[],$forms,[],$now);
        self::assertSame('Actifs · réception à vérifier',$feeds[1]['status']);
    }
}
