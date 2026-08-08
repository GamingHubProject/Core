<?php

declare(strict_types=1);

namespace Illuminate\Support\Facades {
    final class Log
    {
        /** @var list<array{0:string,1:array<string,mixed>}> */
        public static array $info = [];

        /** @var list<array{0:string,1:array<string,mixed>}> */
        public static array $error = [];

        /** @param array<string,mixed> $context */
        public static function info(string $message, array $context = []): void
        {
            self::$info[] = [$message, $context];
        }

        /** @param array<string,mixed> $context */
        public static function error(string $message, array $context = []): void
        {
            self::$error[] = [$message, $context];
        }
    }
}

namespace {
    use Azuriom\Plugin\GamingHubCore\Services\ProviderCreationTrace;
    use Illuminate\Support\Facades\Log;

    $root = dirname(__DIR__);
    $tests = 0;
    $failures = [];

    $check = static function (bool $condition, string $label) use (&$tests, &$failures): void {
        $tests++;
        if (! $condition) {
            $failures[] = $label;
        }
    };

    $source = static fn (string $path): string => (string) file_get_contents($root.'/'.$path);

    // Config helpers for testing the actual production config and trace class.
    $envOverrides = [];
    function env(string $key, mixed $default = null): mixed
    {
        global $envOverrides;

        return array_key_exists($key, $envOverrides) ? $envOverrides[$key] : $default;
    }

    $runtimeConfig = false;
    function config(string $key, mixed $default = null): mixed
    {
        global $runtimeConfig;

        return $key === 'gaming-hub-core.providers.trace_creation' ? $runtimeConfig : $default;
    }

    // A. Active package lifecycle ownership is absent. These are the concrete
    // old Core runtime surfaces; historical migration files are intentionally excluded.
    foreach ([
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
    ] as $activePackageSurface) {
        $check(! file_exists($root.'/'.$activePackageSurface), 'active package surface absent: '.$activePackageSurface);
    }

    $adminRoutes = $source('routes/admin.php');
    foreach (['/extensions', 'extensions.install', 'extensions.update', 'extensions.reinstall', 'extensions.uninstall'] as $routeSurface) {
        $check(! str_contains($adminRoutes, $routeSurface), 'package lifecycle route absent: '.$routeSurface);
    }

    $activeProduction = '';
    foreach (['src', 'routes', 'config', 'resources/views'] as $directory) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$directory));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $activeProduction .= "\n".file_get_contents($file->getPathname());
            }
        }
    }
    foreach (['gaminghub_extension_sources', 'gaminghub_installed_extensions', 'gaminghub_extension_operations'] as $legacyTable) {
        $check(! str_contains($activeProduction, $legacyTable), 'active Core runtime does not use legacy package table '.$legacyTable);
    }

    // B. Historical compatibility remains intact.
    foreach ([
        '2026_08_05_020000_create_gaminghub_extension_sources_table.php',
        '2026_08_05_021000_create_gaminghub_installed_extensions_table.php',
        '2026_08_05_022000_create_gaminghub_extension_operations_table.php',
        '2026_08_05_023000_stabilize_gaminghub_extension_operations.php',
    ] as $migration) {
        $check(file_exists($root.'/database/migrations/'.$migration), 'historical package migration retained: '.$migration);
    }

    // C/F. Core schema and runtime stay generic and do not hard-depend on Panel.
    $genericBoundarySource = '';
    foreach (['src', 'routes', 'config', 'database/migrations'] as $directory) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$directory));
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }
            $path = str_replace('\\', '/', $file->getPathname());
            if (str_contains($path, '2026_08_05_020000_create_gaminghub_extension_sources_table.php')
                || str_contains($path, '2026_08_05_021000_create_gaminghub_installed_extensions_table.php')
                || str_contains($path, '2026_08_05_022000_create_gaminghub_extension_operations_table.php')
                || str_contains($path, '2026_08_05_023000_stabilize_gaminghub_extension_operations.php')) {
                continue;
            }
            $genericBoundarySource .= "\n".file_get_contents($file->getPathname());
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
        $check(! str_contains($genericBoundarySource, $panelSpecific), 'Core has no Panel-owned dependency/column: '.$panelSpecific);
    }
    $check(! preg_match('/use\s+[^;]*(?:Pelican|Pterodactyl|GamingHubPanel)[^;]*;/i', $genericBoundarySource), 'Core imports no Panel/Pelican/Pterodactyl runtime classes');

    $composer = json_decode($source('composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $check(($composer['require'] ?? []) === ['php' => '>=8.2'], 'Core Composer runtime has no Manager/Panel dependency');

    // D/E. Trace is off by default, opt-in works, and actual trace sanitization
    // redacts representative casing/separator/credential variants.
    $envOverrides = [];
    $defaultTraceConfig = require $root.'/config/providers.php';
    $check(($defaultTraceConfig['trace_creation'] ?? null) === false, 'provider trace production default is false');

    $envOverrides = ['GAMING_HUB_PROVIDER_TRACE' => 'true'];
    $optInTraceConfig = require $root.'/config/providers.php';
    $check(($optInTraceConfig['trace_creation'] ?? null) === true, 'provider trace can be explicitly opted in');

    require $root.'/src/Services/ProviderCreationTrace.php';
    $trace = new ProviderCreationTrace();
    $runtimeConfig = false;
    Log::$info = [];
    $trace->stage('disabled_check', ['token' => 'must-not-log']);
    $check(Log::$info === [], 'disabled provider trace emits no log record');

    $runtimeConfig = true;
    Log::$info = [];
    $trace->stage('redaction_check', [
        'Api-Key' => 'secret-one',
        'credential' => 'secret-two',
        'authorizationHeader' => 'secret-three',
        'nested' => [
            'client_token_override' => 'secret-four',
            'safe' => 'credential=secret-five password:secret-six api key=secret-seven Bearer secret-eight',
        ],
        'ordinary' => 'visible',
    ]);
    $check(count(Log::$info) === 1, 'opt-in provider trace emits one diagnostic record');
    $logged = Log::$info[0][1] ?? [];
    $check(($logged['Api-Key'] ?? null) === '[redacted]', 'API key variant is redacted');
    $check(($logged['credential'] ?? null) === '[redacted]', 'credential key is redacted');
    $check(($logged['authorizationHeader'] ?? null) === '[redacted]', 'authorization key variant is redacted');
    $check(($logged['nested']['client_token_override'] ?? null) === '[redacted]', 'token key with separators/suffix is redacted');
    $serializedLog = json_encode($logged, JSON_THROW_ON_ERROR);
    foreach (['secret-one', 'secret-two', 'secret-three', 'secret-four', 'secret-five', 'secret-six', 'secret-seven', 'secret-eight'] as $secret) {
        $check(! str_contains($serializedLog, $secret), 'trace output excludes '.$secret);
    }
    $check(($logged['ordinary'] ?? null) === 'visible', 'ordinary trace context remains available');

    // G. Manager navigation is optional and defensive, not package ownership.
    $serviceProvider = $source('src/Providers/GamingHubCoreServiceProvider.php');
    $check(str_contains($serviceProvider, 'class_exists(PluginManager::class)'), 'Manager navigation tolerates missing plugin manager class');
    $check(str_contains($serviceProvider, "isEnabled('gaming-hub-manager')"), 'Manager navigation requires Manager enabled state');
    $check(str_contains($serviceProvider, "Route::has('gaming-hub-manager.admin.overview')"), 'Manager navigation requires Manager route');
    $check(str_contains($serviceProvider, 'catch (\\Throwable)'), 'Manager navigation fails closed');
    foreach (['ExtensionInstaller', 'ExtensionUninstaller', 'GitHubReleaseClient'] as $packageService) {
        $check(! str_contains($serviceProvider, $packageService), 'Core does not invoke Manager/package service '.$packageService);
    }

    // Serverless/manual games remain valid at the Core model/request boundary.
    $gameRequest = $source('src/Http/Requests/SaveGameRequest.php');
    foreach (['server_id', 'provider_id', 'panel_connection_id', 'api_token', 'rcon'] as $unwantedRequirement) {
        $check(! str_contains($gameRequest, "'{$unwantedRequirement}' =>"), 'Game request does not require '.$unwantedRequirement);
    }
    $gameMigration = $source('database/migrations/2026_08_04_000000_create_gaminghub_games_table.php');
    $check(! str_contains($gameMigration, 'server_id'), 'Game table does not require a Server');
    $check(! str_contains($gameMigration, 'provider_id'), 'Game table does not require a Provider');

    // Active repository/package metadata belongs to GamingHubProject.
    $pluginMetadata = json_decode($source('plugin.json'), true, 512, JSON_THROW_ON_ERROR);
    $check(($pluginMetadata['url'] ?? null) === 'https://github.com/GamingHubProject/Core', 'active repository metadata points to GamingHubProject/Core');
    $check(! str_contains(json_encode($pluginMetadata, JSON_THROW_ON_ERROR), 'RosesOfDorns'), 'active package metadata contains no obsolete RosesOfDorns repository');

    // Version/package contract.
    $plugin = json_decode($source('plugin.json'), true, 512, JSON_THROW_ON_ERROR);
    $check(($plugin['version'] ?? null) === '0.7.11', 'plugin version is exactly 0.7.11');
    $check(str_starts_with($source('README.md'), '# Gaming Hub Core v0.7.11'), 'README identifies v0.7.11');
    $check(str_contains($source('CHANGELOG.md'), "## 0.7.11\n"), 'v0.7.11 changelog entry exists');

    if ($failures !== []) {
        fwrite(STDERR, "FAILED ".count($failures)." / {$tests}\n - ".implode("\n - ", $failures)."\n");
        exit(1);
    }

    echo "PASS {$tests} C0.1 responsibility-boundary checks\n";
}
