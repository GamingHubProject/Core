<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class GameDetailServerDataContractTest extends TestCase
{
    public function test_controller_uses_collision_resistant_server_variable(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/src/Controllers/GameController.php');
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/runtime/games/show-v043.blade.php');

        $this->assertStringContainsString("'publicGameServers' => \$servers", $controller);
        $this->assertStringContainsString('@forelse($publicGameServers as $server)', $view);
        $this->assertStringContainsString('$publicGameServers->count()', $view);
        $this->assertStringNotContainsString('@forelse($servers as $server)', $view);
    }

    public function test_query_is_explicit_and_provider_presentation_does_not_filter_servers(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/src/Controllers/GameController.php');

        foreach (["->servers()", "->with('game')", '->enabled()', '->public()', '->ordered()', '->get()', '->map(fn (Server $server)'] as $contract) {
            $this->assertStringContainsString($contract, $controller);
        }

        $this->assertStringNotContainsString('->filter(', $controller);
    }

    public function test_missing_provider_returns_unknown_instead_of_dropping_server(): void
    {
        $presenter = file_get_contents(dirname(__DIR__, 2).'/src/Services/PublicServerPresenter.php');

        $this->assertStringContainsString("return ['unknown', null];", $presenter);
        $this->assertStringContainsString('catch (Throwable)', $presenter);
    }
}
