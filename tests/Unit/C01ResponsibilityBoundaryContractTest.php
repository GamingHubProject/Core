<?php

use PHPUnit\Framework\TestCase;

final class C01ResponsibilityBoundaryContractTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function test_active_repository_metadata_points_to_gaminghubproject_core(): void
    {
        $plugin = json_decode(file_get_contents($this->root.'/plugin.json'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('https://github.com/GamingHubProject/Core', $plugin['url'] ?? null);
        self::assertStringNotContainsString('RosesOfDorns', json_encode($plugin, JSON_THROW_ON_ERROR));
    }

    public function test_package_lifecycle_is_not_an_active_core_surface(): void
    {
        foreach ([
            'config/extensions.php',
            'resources/views/admin/extensions',
            'src/Controllers/Admin/ExtensionController.php',
            'src/Services/ExtensionInstaller.php',
            'src/Services/ExtensionUninstaller.php',
            'src/Services/GitHubReleaseClient.php',
        ] as $path) {
            self::assertFileDoesNotExist($this->root.'/'.$path);
        }

        $routes = file_get_contents($this->root.'/routes/admin.php');
        self::assertStringNotContainsString('/extensions', $routes);
        self::assertStringNotContainsString('extensions.install', $routes);
        self::assertStringNotContainsString('extensions.update', $routes);
        self::assertStringNotContainsString('extensions.uninstall', $routes);
    }

    public function test_legacy_package_migrations_are_preserved_but_not_used_by_runtime(): void
    {
        $runtime = '';
        foreach (['src', 'routes', 'config'] as $directory) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root.'/'.$directory));
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $runtime .= file_get_contents($file->getPathname());
                }
            }
        }

        foreach ([
            'gaminghub_extension_sources' => '2026_08_05_020000_create_gaminghub_extension_sources_table.php',
            'gaminghub_installed_extensions' => '2026_08_05_021000_create_gaminghub_installed_extensions_table.php',
            'gaminghub_extension_operations' => '2026_08_05_022000_create_gaminghub_extension_operations_table.php',
        ] as $table => $migration) {
            self::assertFileExists($this->root.'/database/migrations/'.$migration);
            self::assertStringNotContainsString($table, $runtime);
        }
    }

    public function test_core_schema_does_not_own_panel_connections_or_credentials(): void
    {
        $production = '';
        foreach (['src', 'config', 'routes'] as $directory) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root.'/'.$directory));
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $production .= file_get_contents($file->getPathname());
                }
            }
        }

        foreach ([
            'panel_connection_id',
            'pelican_connection_id',
            'pterodactyl_connection_id',
            'panel_api_token',
            'GamingHubPanel',
            'PanelConnection',
        ] as $panelSpecific) {
            self::assertStringNotContainsString($panelSpecific, $production);
        }
    }

    public function test_provider_trace_is_opt_in_and_redaction_contract_is_hardened(): void
    {
        $config = file_get_contents($this->root.'/config/providers.php');
        $trace = file_get_contents($this->root.'/src/Services/ProviderCreationTrace.php');

        self::assertStringContainsString("env('GAMING_HUB_PROVIDER_TRACE', false)", $config);
        self::assertStringContainsString("config('gaming-hub-core.providers.trace_creation', false)", $trace);
        self::assertStringContainsString('credential', $trace);
        self::assertStringContainsString("preg_replace('/[^a-z0-9]+/i'", $trace);
        self::assertStringContainsString("'[redacted]'", $trace);
    }

    public function test_manager_navigation_is_optional_and_core_has_no_runtime_manager_dependency(): void
    {
        $provider = file_get_contents($this->root.'/src/Providers/GamingHubCoreServiceProvider.php');
        $composer = json_decode(file_get_contents($this->root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);

        self::assertStringContainsString('class_exists(PluginManager::class)', $provider);
        self::assertStringContainsString("isEnabled('gaming-hub-manager')", $provider);
        self::assertStringContainsString("Route::has('gaming-hub-manager.admin.overview')", $provider);
        self::assertStringContainsString('catch (\\Throwable)', $provider);
        self::assertSame(['php' => '>=8.2'], $composer['require'] ?? []);
    }

    public function test_serverless_game_contract_remains_possible(): void
    {
        $request = file_get_contents($this->root.'/src/Http/Requests/SaveGameRequest.php');
        $migration = file_get_contents($this->root.'/database/migrations/2026_08_04_000000_create_gaminghub_games_table.php');

        self::assertStringNotContainsString("'server_id' =>", $request);
        self::assertStringNotContainsString("'provider_id' =>", $request);
        self::assertStringNotContainsString("'panel_connection_id' =>", $request);
        self::assertStringNotContainsString('server_id', $migration);
        self::assertStringNotContainsString('provider_id', $migration);
    }
}
