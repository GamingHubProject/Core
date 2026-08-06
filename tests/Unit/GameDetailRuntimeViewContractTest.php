<?php
namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;
use PHPUnit\Framework\TestCase;
final class GameDetailRuntimeViewContractTest extends TestCase
{
    public function test_controller_uses_private_versioned_runtime_view(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/src/Controllers/GameController.php');
        $provider = file_get_contents(dirname(__DIR__, 2).'/src/Providers/GamingHubCoreServiceProvider.php');
        $this->assertStringContainsString("gaming-hub-core-runtime-v043::games.show-v043", $controller);
        $this->assertStringContainsString("View::addNamespace('gaming-hub-core-runtime-v043'", $provider);
        $this->assertStringNotContainsString("view('gaming-hub-core::games.show'", $controller);
    }

    public function test_runtime_view_contains_server_registry_and_no_legacy_panels(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/runtime/games/show-v043.blade.php');
        $this->assertStringContainsString('data-gh-public-server-count', $view);
        $this->assertStringContainsString('gh-server-card', $view);
        $this->assertStringContainsString('data-gh-core-game-view', $view);
        $this->assertStringNotContainsString('Enabled providers', $view);
        $this->assertStringNotContainsString('Future capabilities', $view);
    }

    public function test_hero_supports_banner_and_fallback_contracts(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/runtime/games/show-v043.blade.php');
        $this->assertStringContainsString('gh-game-hero--image', $view);
        $this->assertStringContainsString('background-image:', $view);
        $this->assertStringContainsString('radial-gradient', $view);
        $this->assertStringContainsString('gh-game-icon', $view);
    }
}
