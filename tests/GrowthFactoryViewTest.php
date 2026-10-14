<?php

namespace App\Tests;

use App\Service\GrowthFactory\GrowthFactoryView;
use PHPUnit\Framework\TestCase;

final class GrowthFactoryViewTest extends TestCase
{
    public function testTypeChannelCompatibilityIsPreserved(): void
    {
        $view = new GrowthFactoryView();

        self::assertSame(['mapsi_site', 'oling_site', 'linkedin_manual', 'email_manual'], $view->typeOptions()['MAPSI_USERS']['channels']);
        self::assertSame(['mapsi_site', 'oling_site', 'linkedin_manual', 'email_manual'], $view->typeOptions()['MAPSI_MARKETING']['channels']);
        self::assertSame(['mapsi_site', 'oling_site', 'linkedin_manual', 'email_manual'], $view->typeOptions()['OLING']['channels']);
        self::assertSame(['mapsi_site', 'oling_site'], $view->allowedChannels('OLING', ['mapsi_site', 'oling_site']));
    }

    public function testAssetsAreIndexedByChannelWithVersion(): void
    {
        $campaign = (new GrowthFactoryView())->normalizeCampaign([
            'id' => 'camp',
            'content_assets' => [
                ['id' => 'asset', 'channel' => 'email_manual', 'version' => 4, 'content_text' => 'Texte'],
            ],
        ]);

        self::assertSame('asset', $campaign['assets_by_channel']['email_manual']['id']);
        self::assertSame(4, $campaign['assets_by_channel']['email_manual']['version']);
    }
}
