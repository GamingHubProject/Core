<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$passed = 0;

$check = static function (bool $condition, string $message) use (&$passed): void {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }

    $passed++;
};

$removed = [
    'config/extensions.php',
    'resources/views/admin/extensions',
    'src/Controllers/Admin/ExtensionController.php',
    'src/Controllers/Admin/ExtensionInstallController.php',
    'src/Controllers/Admin/ExtensionSourceController.php',
    'src/Controllers/Admin/ExtensionUninstallController.php',
    'src/Controllers/Admin/InstalledExtensionController.php',
    'src/Models/ExtensionSource.php',
    'src/Models/InstalledExtension.php',
    'src/Models/ExtensionOperation.php',
    'src/Services/ExtensionInstaller.php',
    'src/Services/ExtensionUninstaller.php',
    'src/Services/GitHubReleaseClient.php',
    'src/Services/ExtensionSourceManager.php',
    'src/Services/InstalledExtensionResolver.php',
    'tests/run-extension-lifecycle.php',
    'docs/EXTENSION_REGISTRY.md',
    'docs/EXTENSION_INSTALLER_STABILIZATION.md',
];

foreach ($removed as $path) {
    $check(! file_exists($root.'/'.$path), $path.' is removed');
}

$routes = file_get_contents($root.'/routes/admin.php');
foreach (['/games', '/servers', '/providers', '/settings/directory', '/settings/public-data'] as $platformPath) {
    $check(str_contains($routes, $platformPath), 'platform route remains: '.$platformPath);
}
foreach (['/extensions', 'extensions.index', 'extensions.install', 'extensions.update', 'extensions.uninstall'] as $legacyRoute) {
    $check(! str_contains($routes, $legacyRoute), 'legacy package route removed: '.$legacyRoute);
}

$provider = file_get_contents($root.'/src/Providers/GamingHubCoreServiceProvider.php');
foreach ([
    'GameRegistry::class',
    'ProviderTypeRegistry::class',
    'SharedDataGateway::class',
    'ProviderLifecycleManager::class',
    'registerBuiltInProviderTypes',
    'registerBuiltInCapabilityReaders',
    'registerBuiltInGameNavigation',
] as $platformContract) {
    $check(str_contains($provider, $platformContract), 'platform registration remains: '.$platformContract);
}
foreach ([
    'ExtensionInstaller::class',
    'ExtensionUninstaller::class',
    'GitHubReleaseClient::class',
    'gaminghub.extensions.',
    'config/extensions.php',
] as $legacyContract) {
    $check(! str_contains($provider, $legacyContract), 'package registration removed: '.$legacyContract);
}

$check(str_contains($provider, "isEnabled('gaming-hub-manager')"), 'Manager link is conditional on enabled plugin');
$check(str_contains($provider, "Route::has('gaming-hub-manager.admin.overview')"), 'Manager link requires the Manager route to exist');
$check(str_contains($provider, "'gaming-hub-manager.admin.overview'"), 'Manager link targets Manager overview');
$check(str_contains($provider, "'gaminghub.manager.view'"), 'Manager link respects Manager view permission');
$check(str_contains($provider, 'catch (\\Throwable)'), 'Manager detection fails closed');

foreach ([
    'database/migrations/2026_08_05_020000_create_gaminghub_extension_sources_table.php',
    'database/migrations/2026_08_05_021000_create_gaminghub_installed_extensions_table.php',
    'database/migrations/2026_08_05_022000_create_gaminghub_extension_operations_table.php',
    'database/migrations/2026_08_05_023000_stabilize_gaminghub_extension_operations.php',
] as $legacyMigration) {
    $check(file_exists($root.'/'.$legacyMigration), 'legacy migration retained: '.$legacyMigration);
}

$composer = json_decode(file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
$check(($composer['require'] ?? []) === ['php' => '>=8.2'], 'package-only Composer requirements removed');
$check(! isset($composer['suggest']), 'package-only Composer suggestions removed');

$plugin = json_decode(file_get_contents($root.'/plugin.json'), true, 512, JSON_THROW_ON_ERROR);
$check(($plugin['version'] ?? null) === '0.7.11', 'plugin version is 0.7.11');
$check(! str_contains(strtolower((string) ($plugin['description'] ?? '')), 'lifecycle'), 'manifest no longer claims package lifecycle');

// Gaming Hub Panel v0.2.0 compatibility contracts must remain present.
foreach ([
    'src/Contracts/CapabilityReader.php',
    'src/Contracts/CapabilityReaderRegistry.php',
    'src/Contracts/ProviderInstances.php',
    'src/Contracts/ProviderTypeRegistry.php',
    'src/Contracts/PublicDataPolicyResolver.php',
    'src/Data/MetricsData.php',
    'src/Data/ProviderConfigurationField.php',
    'src/Data/ProviderInstanceData.php',
    'src/Data/ProviderType.php',
    'src/Data/ServerStatusData.php',
    'src/Data/SharedDataResult.php',
    'src/Models/Game.php',
    'src/Models/ProviderInstance.php',
    'src/Models/Server.php',
    'src/Validation/ProviderConfigurationValidator.php',
] as $panelContract) {
    $check(file_exists($root.'/'.$panelContract), 'Panel integration contract remains: '.$panelContract);
}

echo "PASS {$passed} platform cleanup checks\n";
