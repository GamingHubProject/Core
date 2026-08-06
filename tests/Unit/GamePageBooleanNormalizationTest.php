<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class GamePageBooleanNormalizationTest extends TestCase
{
    public function test_settings_use_explicit_boolean_normalization_and_true_default(): void
    {
        $settings = file_get_contents(dirname(__DIR__, 2).'/src/Settings/GamePageSettings.php');

        $this->assertStringContainsString("'show_servers' => \$this->boolean(self::PREFIX.'show_servers', true)", $settings);
        foreach (["'1', 'true', 'yes', 'on'", "'0', 'false', 'no', 'off', ''"] as $contract) {
            $this->assertStringContainsString($contract, $settings);
        }
        $this->assertStringNotContainsString("(bool) setting(self::PREFIX.'show_servers'", $settings);
    }
}
