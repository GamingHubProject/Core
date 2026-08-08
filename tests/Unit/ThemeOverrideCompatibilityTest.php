<?php
namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;
use PHPUnit\Framework\TestCase;
final class ThemeOverrideCompatibilityTest extends TestCase
{
    public function test_directory_and_game_detail_use_core_owned_renderers(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/src/Controllers/GameController.php');
        $provider = file_get_contents(dirname(__DIR__, 2).'/src/Providers/GamingHubCoreServiceProvider.php');
        $this->assertStringContainsString("view()->file(dirname(__DIR__,2).'/resources/views/games/index.blade.php'", $controller);
        $this->assertStringContainsString("view('gaming-hub-core-runtime-v043::games.show-v043'", $controller);
        $this->assertStringContainsString("View::addNamespace('gaming-hub-core-runtime-v043'", $provider);
        $this->assertStringNotContainsString("view('gaming-hub-core::games.show'", $controller);
    }
}
