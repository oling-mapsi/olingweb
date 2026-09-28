<?php

namespace App\Tests;

use App\Service\Chat\ChatOwnerRouter;
use PHPUnit\Framework\TestCase;

final class ChatOwnerRouterTest extends TestCase
{
    /** @dataProvider ownerCases */
    public function testRoutesDemandToTheExpectedOwner(string $id, string $query, string $expected): void
    {
        self::assertSame($expected, (new ChatOwnerRouter())->resolveOwnerUrl($query), $id);
    }

    public static function ownerCases(): iterable
    {
        yield 'ERP-001' => ['ERP-001', 'Nous voulons remplacer notre ERP', ChatOwnerRouter::ERP];
        yield 'ERP-002' => ['ERP-002', 'AMOA choix ERP', ChatOwnerRouter::ERP];
        yield 'ERP-003' => ['ERP-003', 'cahier des charges progiciel', ChatOwnerRouter::ERP];
        yield 'ERP-004' => ['ERP-004', 'consultation intégrateur SAP', ChatOwnerRouter::ERP];
        yield 'ERP-005' => ['ERP-005', 'reprise de données Sage X3', ChatOwnerRouter::ERP];
        yield 'ERP-006' => ['ERP-006', 'recette et mise en production Divalto', ChatOwnerRouter::ERP];
        yield 'ERP-007' => ['ERP-007', 'choisir entre ERP et progiciel métier', ChatOwnerRouter::ERP];
        yield 'ERP-008' => ['ERP-008', 'AMOA cahier des charges consultation intégrateur', ChatOwnerRouter::ERP];
        yield 'ERP-009' => ['ERP-009', 'pilotage d un projet Cegid', ChatOwnerRouter::ERP];
        yield 'ERP-010' => ['ERP-010', 'hypercare après migration ERP', ChatOwnerRouter::ERP];
        yield 'FIN-001' => ['FIN-001', 'AMOA SI finance', ChatOwnerRouter::FINANCE];
        yield 'FIN-002' => ['FIN-002', 'remplacer notre système financier', ChatOwnerRouter::FINANCE];
        yield 'FIN-003' => ['FIN-003', 'ERP finance et clôture comptable', ChatOwnerRouter::FINANCE];
        yield 'CRM-001' => ['CRM-001', 'AMOA CRM', ChatOwnerRouter::CRM];
        yield 'CRM-002' => ['CRM-002', 'refonte du système d information client', ChatOwnerRouter::CRM];
        yield 'CRM-003' => ['CRM-003', 'outil de relation client', ChatOwnerRouter::CRM];
        yield 'GMAO-001' => ['GMAO-001', 'choix d une GMAO', ChatOwnerRouter::GMAO];
        yield 'GMAO-002' => ['GMAO-002', 'gestion des interventions de maintenance', ChatOwnerRouter::GMAO];
        yield 'GMAO-003' => ['GMAO-003', 'cahier des charges maintenance assistée', ChatOwnerRouter::GMAO];
        yield 'RFE-001' => ['RFE-001', 'AMOA facturation électronique', ChatOwnerRouter::RFE];
        yield 'RFE-002' => ['RFE-002', 'choisir une plateforme de dématérialisation', ChatOwnerRouter::RFE];
        yield 'SICLIENT-001' => ['SICLIENT-001', 'AMOA SI client', ChatOwnerRouter::CRM];
        yield 'SICLIENT-002' => ['SICLIENT-002', 'moderniser notre SI client', ChatOwnerRouter::CRM];
        yield 'SIRH-001' => ['SIRH-001', 'AMOA transverse pour un SIRH', ChatOwnerRouter::AMOA];
        yield 'DATA-001' => ['DATA-001', 'AMOA transverse pour notre plateforme data', ChatOwnerRouter::AMOA];
    }
}
