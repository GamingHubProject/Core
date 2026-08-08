<?php

use PHPUnit\Framework\TestCase;

final class PlatformOnlyV070ContractTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function test_package_lifecycle_surface_is_removed(): void
    {
        self::assertDirectoryDoesNotExist($this->root.'/resources/views/admin/extensions');
        self::assertFileDoesNotExist($this->root.'/src/Services/ExtensionInstaller.php');
        self::assertFileDoesNotExist($this->root.'/src/Services/ExtensionUninstaller.php');
        self::assertFileDoesNotExist($this->root.'/src/Controllers/Admin/ExtensionController.php');

        $routes = file_get_contents($this->root.'/routes/admin.php');
        self::assertStringNotContainsString('/extensions', $routes);
        self::assertStringNotContainsString('gaminghub.extensions.', $routes);
    }

    public function test_platform_routes_and_services_remain(): void
    {
        $routes = file_get_contents($this->root.'/routes/admin.php');
        self::assertStringContainsString('games.index', $routes);
        self::assertStringContainsString('games.servers.index', $routes);
        self::assertStringContainsString('games.servers.providers.index', $routes);

        $provider = file_get_contents($this->root.'/src/Providers/GamingHubCoreServiceProvider.php');
        self::assertStringContainsString('SharedDataGateway::class', $provider);
        self::assertStringContainsString('ProviderTypeRegistry::class', $provider);
        self::assertStringContainsString('GameRegistry::class', $provider);
    }

    public function test_manager_navigation_is_optional_and_direct(): void
    {
        $provider = file_get_contents($this->root.'/src/Providers/GamingHubCoreServiceProvider.php');
        self::assertStringContainsString("isEnabled('gaming-hub-manager')", $provider);
        self::assertStringContainsString("Route::has('gaming-hub-manager.admin.overview')", $provider);
        self::assertStringContainsString("'gaming-hub-manager.admin.overview'", $provider);
        self::assertStringContainsString("'gaminghub.manager.view'", $provider);
    }

    public function test_legacy_metadata_migrations_are_retained(): void
    {
        foreach ([
            '2026_08_05_020000_create_gaminghub_extension_sources_table.php',
            '2026_08_05_021000_create_gaminghub_installed_extensions_table.php',
            '2026_08_05_022000_create_gaminghub_extension_operations_table.php',
            '2026_08_05_023000_stabilize_gaminghub_extension_operations.php',
        ] as $migration) {
            self::assertFileExists($this->root.'/database/migrations/'.$migration);
        }
    }
}
