<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class NavbarRouteDescriptionTest extends TestCase
{
    public function test_service_provider_registers_named_game_directory_target(): void
    {
        $provider = file_get_contents(dirname(__DIR__, 2).'/src/Providers/GamingHubCoreServiceProvider.php');

        self::assertIsString($provider);
        self::assertStringContainsString('$this->registerRouteDescriptions();', $provider);
        self::assertStringContainsString("'gaming-hub-core.games.index'", $provider);
        self::assertStringNotContainsString("'/games' =>", $provider);
    }
}
