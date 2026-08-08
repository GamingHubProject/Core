<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;

use PHPUnit\Framework\TestCase;

class GameDirectorySettingsRenderingMapTest extends TestCase
{
    public function test_settings_service_defines_all_rendering_classes_and_defaults(): void
    {
        $source = file_get_contents(__DIR__.'/../../src/Settings/GameDirectorySettings.php');

        foreach (['gh-games-grid--','gh-game-card--','gh-page-container-wide','gh-page-container-full','gh-page-container','gh-games-fallback--'] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
        $this->assertStringContainsString("choiceInt(self::PREFIX.'columns', 3", $source);
        $this->assertStringContainsString("choice(self::PREFIX.'density', 'compact'", $source);
        $this->assertStringContainsString("choice(self::PREFIX.'container_width', 'normal'", $source);
        $this->assertStringContainsString("choice(self::PREFIX.'fallback_style', 'compact'", $source);
    }

    public function test_save_invalidates_known_settings_caches(): void
    {
        $source = file_get_contents(__DIR__.'/../../src/Settings/GameDirectorySettings.php');
        $this->assertStringContainsString("Cache::forget('settings')", $source);
    }
}
