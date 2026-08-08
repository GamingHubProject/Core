<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;

use Azuriom\Plugin\GamingHubCore\Services\NavbarGameRoutes;
use PHPUnit\Framework\TestCase;

class NavbarGameRoutesContractTest extends TestCase
{
    public function test_individual_targets_have_a_stable_grouped_prefix(): void
    {
        self::assertSame('gaming-hub-core.games.game.', NavbarGameRoutes::PREFIX);
    }

    public function test_existing_directory_target_is_preserved(): void
    {
        $provider = file_get_contents(__DIR__.'/../../src/Providers/GamingHubCoreServiceProvider.php');
        self::assertStringContainsString("'gaming-hub-core.games.index'", $provider);
    }

    public function test_dynamic_routes_are_registered_before_generic_web_routes(): void
    {
        $provider = file_get_contents(__DIR__.'/../../src/Providers/RouteServiceProvider.php');
        self::assertLessThan(
            strpos($provider, "require plugin_path"),
            strpos($provider, 'enabledGames()')
        );
    }
}
