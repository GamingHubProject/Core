<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;

use Azuriom\Plugin\GamingHubCore\Settings\GameDirectorySettings;
use PHPUnit\Framework\TestCase;

class GameDirectorySettingsContractTest extends TestCase
{
    public function test_allowed_values_match_public_contract(): void
    {
        self::assertSame([1, 2, 3, 4], GameDirectorySettings::COLUMNS);
        self::assertSame(['compact', 'standard'], GameDirectorySettings::DENSITIES);
        self::assertSame(['normal', 'wide', 'full'], GameDirectorySettings::WIDTHS);
        self::assertSame(['compact', 'media'], GameDirectorySettings::FALLBACKS);
    }

    public function test_setting_namespace_is_plugin_scoped(): void
    {
        self::assertSame('gaming-hub-core.directory.', GameDirectorySettings::PREFIX);
    }
}
