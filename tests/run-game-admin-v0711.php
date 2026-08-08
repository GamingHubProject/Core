<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$tests = 0;
$failures = [];

$check = static function (bool $condition, string $message) use (&$tests, &$failures): void {
    $tests++;
    if (! $condition) {
        $failures[] = $message;
    }
};

$source = static fn (string $path): string => file_get_contents($root.'/'.$path) ?: '';

// Release and C0.1 finalization.
$plugin = json_decode($source('plugin.json'), true, 512, JSON_THROW_ON_ERROR);
$check(($plugin['version'] ?? null) === '0.7.11', 'Core version is 0.7.11');
$check(($plugin['url'] ?? null) === 'https://github.com/GamingHubProject/Core', 'active repository metadata uses GamingHubProject/Core');
$check(! str_contains(json_encode($plugin, JSON_THROW_ON_ERROR), 'RosesOfDorns'), 'active plugin metadata contains no RosesOfDorns repository');

foreach ([
    'src/Controllers/Admin/ExtensionController.php',
    'src/Services/ExtensionInstaller.php',
    'src/Services/ExtensionUpdater.php',
    'src/Services/ExtensionUninstaller.php',
    'src/Services/GitHubReleaseClient.php',
    'config/extensions.php',
] as $obsoletePackageSurface) {
    $check(! file_exists($root.'/'.$obsoletePackageSurface), 'package lifecycle surface absent: '.$obsoletePackageSurface);
}
$adminRoutes = $source('routes/admin.php');
$check(! str_contains($adminRoutes, '/extensions'), 'Core has no package lifecycle admin routes');
$check(! str_contains($adminRoutes, 'registry'), 'Core has no package registry admin routes');

$serviceProvider = $source('src/Providers/GamingHubCoreServiceProvider.php');
$check(str_contains($serviceProvider, "isEnabled('gaming-hub-manager')"), 'Manager navigation remains optional by enabled-state check');
$check(str_contains($serviceProvider, "Route::has('gaming-hub-manager.admin.overview')"), 'Manager navigation remains optional by route check');
$check(str_contains($serviceProvider, 'catch (\\Throwable)'), 'Manager navigation fails closed');

foreach ([
    '2026_08_05_020000_create_gaminghub_extension_sources_table.php',
    '2026_08_05_021000_create_gaminghub_installed_extensions_table.php',
    '2026_08_05_022000_create_gaminghub_extension_operations_table.php',
    '2026_08_05_023000_stabilize_gaminghub_extension_operations.php',
] as $legacyMigration) {
    $check(file_exists($root.'/database/migrations/'.$legacyMigration), 'legacy package migration preserved: '.$legacyMigration);
}

$production = '';
foreach (['src', 'config', 'routes'] as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$directory));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $production .= file_get_contents($file->getPathname());
        }
    }
}
foreach (['panel_connection_id', 'pelican_connection_id', 'pterodactyl_connection_id', 'panel_api_token', 'GamingHubPanel', 'PanelConnection'] as $panelSpecific) {
    $check(! str_contains($production, $panelSpecific), 'Core runtime has no Panel-owned field/class: '.$panelSpecific);
}

// Game model/server independence.
$gameModel = $source('src/Models/Game.php');
$gameRequest = $source('src/Http/Requests/SaveGameRequest.php');
$gameController = $source('src/Controllers/Admin/GameController.php');
$gameMigration = $source('database/migrations/2026_08_04_000000_create_gaminghub_games_table.php');
$serverMigration = $source('database/migrations/2026_08_05_000000_create_gaminghub_servers_table.php');

$check(str_contains($gameModel, 'public function servers(): HasMany'), 'Game owns optional hasMany Servers relation');
$check(str_contains($gameModel, 'return $this->hasMany(Server::class);'), 'Game Servers relation targets Server');
$check(! str_contains($gameMigration, 'server_id'), 'Game table has no Server foreign key');
$check(! str_contains($gameMigration, 'provider_id'), 'Game table has no Provider foreign key');
$check(str_contains($serverMigration, "\$table->unsignedInteger('game_id')"), 'Server table owns Game foreign key');

foreach (['server_id', 'provider_id', 'provider_instance_id', 'panel_connection_id', 'remote_server_id'] as $forbiddenGameInput) {
    $check(! str_contains($gameRequest, "'{$forbiddenGameInput}' =>"), 'Game save does not require '.$forbiddenGameInput);
}
$check(str_contains($gameController, 'Game::create($request->validated())'), 'Game create persists validated Game fields directly');
$check(! str_contains($gameController, 'servers()->create'), 'Game creation does not create a Server');
$check(! str_contains($gameController, 'providers()->create'), 'Game creation does not create a Provider');

// Provider ownership compatibility: active CRUD is Server-owned; Game relationship is deprecated only.
$providerController = $source('src/Controllers/Admin/ProviderController.php');
$providerLifecycle = $source('src/Services/ProviderLifecycleManager.php');
$providerModel = $source('src/Models/ProviderInstance.php');
$check(str_contains($providerController, '$server->providers()'), 'provider administration reads Providers through Server ownership');
$check(str_contains($providerLifecycle, '$server->providers()->create'), 'provider lifecycle creates Providers through Server ownership');
$check(str_contains($providerModel, 'public function server(): BelongsTo'), 'ProviderInstance has current Server owner relation');
$check(str_contains($gameModel, '@deprecated Provider instances are Server-owned'), 'legacy Game Provider relationship is explicitly deprecated');
$check(! str_contains($providerController, '$game->providers()'), 'active Provider controller does not use deprecated Game Provider ownership');
$check(! str_contains($providerLifecycle, '$game->providers()'), 'active Provider lifecycle does not use deprecated Game Provider ownership');

// Description contract and non-destructive legacy handling.
$form = $source('resources/views/admin/games/_form.blade.php');
$publicPresenter = $source('src/Services/PublicGamePresenter.php');
$check(! str_contains($gameRequest, "'description' =>"), 'legacy description is no longer accepted as a current admin field');
$check(! str_contains($form, 'name="description"'), 'legacy description is not rendered as a competing admin form field');
$check(! str_contains($form, '$game->short_description ?: $game->description'), 'legacy description is not silently copied into the current short-description field');
$check(str_contains($form, 'legacy_description_help'), 'edit form explains legacy description display compatibility without exposing a competing field');
$check(! str_contains($form, '$game->long_description ?? $game->description'), 'long description no longer aliases legacy description in the edit form');
$check(str_contains($gameModel, "'description',"), 'legacy description remains model-compatible and is not destructively removed');
$check(str_contains($gameModel, 'shortDescriptionForDisplay'), 'Game exposes compact description compatibility fallback');
$check(str_contains($gameModel, 'longDescriptionForDisplay'), 'Game exposes detailed description compatibility fallback');
$check(str_contains($publicPresenter, '$game->shortDescriptionForDisplay()'), 'public presenter uses centralized short-description compatibility');
$check(str_contains($publicPresenter, '$game->longDescriptionForDisplay()'), 'public presenter uses centralized long-description compatibility');

// View-mode behavior is pure and directly executable without Azuriom runtime.
require $root.'/src/Support/GameAdminViewMode.php';
$modeClass = \Azuriom\Plugin\GamingHubCore\Support\GameAdminViewMode::class;
$check($modeClass::fromQuery(null) === 'grid', 'missing view query defaults to Grid');
$check($modeClass::fromQuery('grid') === 'grid', 'explicit Grid renders Grid');
$check($modeClass::fromQuery('list') === 'list', 'explicit List renders List');
$check($modeClass::fromQuery('invalid') === 'grid', 'invalid view query safely falls back to Grid');
$check($modeClass::fromQuery(['list']) === 'grid', 'non-string view query safely falls back to Grid');

$index = $source('resources/views/admin/games/index.blade.php');
$actions = $source('resources/views/admin/games/_actions.blade.php');
$check(str_contains($gameController, "GameAdminViewMode::fromQuery(\$request->query('view'))"), 'Games controller normalizes view query');
$check(str_contains($gameController, "'viewMode' => \$viewMode"), 'Games controller passes normalized view mode');
$check(str_contains($gameController, "->withCount('servers')"), 'Games index loads informational Server counts without N+1 queries');
$check(str_contains($index, "@elseif(\$viewMode === 'list')"), 'Games index provides List branch');
$check(str_contains($index, 'row row-cols-1 row-cols-md-2 row-cols-xl-3'), 'Grid is responsive across mobile/tablet/desktop');
$check(str_contains($index, "['view' => 'grid']"), 'Grid switch is explicit');
$check(str_contains($index, "['view' => 'list']"), 'List switch is explicit');

// Card/list content contracts.
$check(str_contains($index, '$game->banner_url ?: $game->icon_url'), 'Game card prefers existing banner/icon URL artwork');
$check(str_contains($index, 'onerror='), 'broken external artwork falls back without breaking the card');
$check(str_contains($index, '$game->accent_color'), 'no-artwork fallback uses existing accent color');
$check(str_contains($index, '$initials'), 'no-artwork fallback renders Game initials');
$check(str_contains($index, '$game->shortDescriptionForDisplay()'), 'Grid/List uses short description contract');
$check(str_contains($index, "states.'.(\$game->enabled ? 'enabled' : 'disabled')"), 'Enabled/Disabled state is textual, not color-only');
$check(str_contains($index, "trans_choice('gaming-hub-core::admin.games.server_count'"), 'Server count is informational in Grid/List');
$check(! str_contains($index, '$game->uuid'), 'List/Grid does not expose UUID as a primary field');
$check(str_contains($index, '<code>{{ $game->slug }}</code>'), 'slug remains secondary technical information');

foreach ([
    'gaming-hub-core.admin.games.move',
    'gaming-hub-core.admin.games.toggle',
    'gaming-hub-core.admin.games.servers.index',
    'gaming-hub-core.admin.games.edit',
    'gaming-hub-core.admin.games.destroy',
] as $requiredActionRoute) {
    $check(str_contains($actions, $requiredActionRoute), 'compact actions preserve '.$requiredActionRoute);
}
$check(str_contains($actions, 'confirm_delete'), 'delete retains destructive confirmation');
$check(str_contains($actions, "[\$game, 'up']"), 'move-up ordering remains available');
$check(str_contains($actions, "[\$game, 'down']"), 'move-down ordering remains available');

// Empty state and form grouping.
$english = require $root.'/resources/lang/en/admin.php';
$check(($english['games']['empty_title'] ?? '') === 'No games have been created yet.', 'empty state has explicit first-Game title');
$check(str_contains((string) ($english['games']['empty_help'] ?? ''), 'Server is not required'), 'empty state does not imply a Server requirement');
$check(str_contains($form, "games.sections.identity"), 'Create/Edit has Identity section');
$check(str_contains($form, "games.sections.presentation"), 'Create/Edit has Presentation section');
$check(str_contains($form, "games.sections.state"), 'Create/Edit has State section');
foreach (['server_id', 'provider_id', 'panel_connection_id', 'remote_server_id', 'api_token'] as $outOfScopeField) {
    $check(! str_contains($form, 'name="'.$outOfScopeField.'"'), 'Game form has no out-of-scope field '.$outOfScopeField);
}

// Existing route/public contracts remain present.
foreach ([
    "name('games.index')",
    "name('games.store')",
    "name('games.update')",
    "name('games.toggle')",
    "name('games.move')",
    "name('games.destroy')",
    "name('games.servers.index')",
] as $routeContract) {
    $check(str_contains($adminRoutes, $routeContract), 'admin route preserved: '.$routeContract);
}
$webRoutes = $source('routes/web.php');
$check(str_contains($webRoutes, "name('games.index')"), 'public Game Directory route remains');
$check(str_contains($webRoutes, "name('games.show')"), 'public Game detail route remains');
$check(str_contains($webRoutes, "name('servers.show')"), 'public Server detail route remains');
$check(str_contains($serviceProvider, "'gaming-hub-core.games.index'"), 'navbar Game Directory integration remains registered');

// No C0.2 migration was introduced.
$migrations = array_values(array_filter(scandir($root.'/database/migrations') ?: [], static fn (string $name): bool => str_ends_with($name, '.php')));
$check(count($migrations) === 11, 'C0.2 introduces no database migration');

if ($failures !== []) {
    fwrite(STDERR, "FAILED ".count($failures)." / {$tests}\n - ".implode("\n - ", $failures)."\n");
    exit(1);
}

echo "PASS {$tests} C0.2 Game/admin checks\n";
