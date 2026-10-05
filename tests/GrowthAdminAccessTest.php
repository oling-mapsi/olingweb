<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GrowthAdminAccessTest extends WebTestCase
{
    public function testGrowthDashboardRequiresAdminRole(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/growth');

        self::assertTrue(in_array($client->getResponse()->getStatusCode(), [302, 401, 403], true));
    }
}
