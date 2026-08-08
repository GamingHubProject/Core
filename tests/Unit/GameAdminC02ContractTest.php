<?php

use Azuriom\Plugin\GamingHubCore\Support\GameAdminViewMode;
use PHPUnit\Framework\TestCase;

final class GameAdminC02ContractTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function test_view_mode_defaults_to_grid_and_only_accepts_list_explicitly(): void
    {
        self::assertSame('grid', GameAdminViewMode::fromQuery(null));
        self::assertSame('grid', GameAdminViewMode::fromQuery('grid'));
        self::assertSame('list', GameAdminViewMode::fromQuery('list'));
        self::assertSame('grid', GameAdminViewMode::fromQuery('unknown'));
    }

    public function test_game_is_server_independent_and_server_relation_is_optional(): void
    {
        $request = file_get_contents($this->root.'/src/Http/Requests/SaveGameRequest.php');
        $gameMigration = file_get_contents($this->root.'/database/migrations/2026_08_04_000000_create_gaminghub_games_table.php');
        $serverMigration = file_get_contents($this->root.'/database/migrations/2026_08_05_000000_create_gaminghub_servers_table.php');
        $model = file_get_contents($this->root.'/src/Models/Game.php');

        foreach (['server_id', 'provider_id', 'provider_instance_id', 'panel_connection_id', 'remote_server_id'] as $field) {
            self::assertStringNotContainsString("'{$field}' =>", $request);
        }
        self::assertStringNotContainsString('server_id', $gameMigration);
        self::assertStringContainsString("\$table->unsignedInteger('game_id')", $serverMigration);
        self::assertStringContainsString('public function servers(): HasMany', $model);
    }

    public function test_current_provider_runtime_is_server_owned_while_legacy_relationship_remains_deprecated(): void
    {
        $game = file_get_contents($this->root.'/src/Models/Game.php');
        $controller = file_get_contents($this->root.'/src/Controllers/Admin/ProviderController.php');
        $lifecycle = file_get_contents($this->root.'/src/Services/ProviderLifecycleManager.php');

        self::assertStringContainsString('@deprecated Provider instances are Server-owned', $game);
        self::assertStringContainsString('$server->providers()', $controller);
        self::assertStringContainsString('$server->providers()->create', $lifecycle);
        self::assertStringNotContainsString('$game->providers()', $controller.$lifecycle);
    }

    public function test_description_contract_is_current_fields_plus_non_destructive_legacy_fallback(): void
    {
        $request = file_get_contents($this->root.'/src/Http/Requests/SaveGameRequest.php');
        $form = file_get_contents($this->root.'/resources/views/admin/games/_form.blade.php');
        $model = file_get_contents($this->root.'/src/Models/Game.php');

        self::assertStringNotContainsString("'description' =>", $request);
        self::assertStringNotContainsString('name="description"', $form);
        self::assertStringNotContainsString('$game->short_description ?: $game->description', $form);
        self::assertStringContainsString('legacy_description_help', $form);
        self::assertStringContainsString("'description',", $model);
    }

    public function test_games_index_is_grid_first_with_list_mode_counts_artwork_and_safe_actions(): void
    {
        $controller = file_get_contents($this->root.'/src/Controllers/Admin/GameController.php');
        $index = file_get_contents($this->root.'/resources/views/admin/games/index.blade.php');
        $actions = file_get_contents($this->root.'/resources/views/admin/games/_actions.blade.php');

        self::assertStringContainsString('GameAdminViewMode::fromQuery', $controller);
        self::assertStringContainsString("->withCount('servers')", $controller);
        self::assertStringContainsString("@elseif(\$viewMode === 'list')", $index);
        self::assertStringContainsString('row-cols-md-2', $index);
        self::assertStringContainsString('row-cols-xl-3', $index);
        self::assertStringContainsString('$game->banner_url ?: $game->icon_url', $index);
        self::assertStringContainsString('onerror=', $index);
        self::assertStringContainsString('shortDescriptionForDisplay()', $index);
        self::assertStringNotContainsString('$game->uuid', $index);
        self::assertStringContainsString('confirm_delete', $actions);
    }

    public function test_active_metadata_is_current_and_manager_panel_boundaries_remain_intact(): void
    {
        $plugin = json_decode(file_get_contents($this->root.'/plugin.json'), true, 512, JSON_THROW_ON_ERROR);
        $provider = file_get_contents($this->root.'/src/Providers/GamingHubCoreServiceProvider.php');

        self::assertSame('0.7.11', $plugin['version'] ?? null);
        self::assertSame('https://github.com/GamingHubProject/Core', $plugin['url'] ?? null);
        self::assertStringNotContainsString('RosesOfDorns', json_encode($plugin, JSON_THROW_ON_ERROR));
        self::assertStringContainsString("isEnabled('gaming-hub-manager')", $provider);
        self::assertStringContainsString("Route::has('gaming-hub-manager.admin.overview')", $provider);
    }
}
