<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require $root.'/src/Services/ProviderPositionSequence.php';

use Azuriom\Plugin\GamingHubCore\Services\ProviderPositionSequence;

$tests = 0;
$failures = [];

function check(bool $condition, string $label): void
{
    global $tests, $failures;
    $tests++;

    if (! $condition) {
        $failures[] = $label;
    }
}

function source(string $path): string
{
    global $root;

    $contents = file_get_contents($root.'/'.$path);
    if ($contents === false) {
        throw new RuntimeException('Unable to read '.$path);
    }

    return $contents;
}

// Production sequence algorithm checks: public priority is one-based.
check(ProviderPositionSequence::move([10, 20], 10, 'down') === [20, 10], 'first provider moves down');
check(ProviderPositionSequence::move([10, 20], 20, 'up') === [20, 10], 'second provider moves up');
check(ProviderPositionSequence::move([10, 20], 10, 'up') === [10, 20], 'first provider cannot move up');
check(ProviderPositionSequence::move([10, 20], 20, 'down') === [10, 20], 'last provider cannot move down');
check(ProviderPositionSequence::reposition([10], 20, 2) === [10, 20], 'new provider appends at priority two');
check(ProviderPositionSequence::reposition([10], 20, 0) === [10, 20], 'legacy zero create priority appends safely');
check(ProviderPositionSequence::reposition([10, 20], 20, 1) === [20, 10], 'requested one-based priority is normalized');
check(ProviderPositionSequence::reposition([10, 20], 20, 99) === [10, 20], 'out-of-range priority clamps to end');

$routes = source('routes/admin.php');
$controller = source('src/Controllers/Admin/ProviderController.php');
$lifecycle = source('src/Services/ProviderLifecycleManager.php');
$context = source('src/Services/ProviderLifecycleContext.php');
$observer = source('src/Observers/ProviderInstanceObserver.php');
$model = source('src/Models/ProviderInstance.php');
$instances = source('src/Services/EloquentProviderInstances.php');
$resolver = source('src/Services/DefaultCapabilityResolver.php');
$cache = source('src/Services/SharedDataCache.php');
$view = source('resources/views/admin/providers/index.blade.php');
$form = source('resources/views/admin/providers/_form.blade.php');
$migration = source('database/migrations/2026_08_06_010000_enforce_gaminghub_provider_positions.php');
$request = source('src/Http/Requests/SaveProviderRequest.php');
$validator = source('src/Validation/ProviderConfigurationValidator.php');
$provider = source('src/Providers/GamingHubCoreServiceProvider.php');

// Exact route-model binding and HTTP method contracts.
foreach (['toggle', 'move', 'destroy'] as $action) {
    check(
        preg_match('/function\s+'.$action.'\([^)]*Game\s+\$game[^)]*Server\s+\$server[^)]*ProviderInstance\s+\$provider/s', $controller) === 1,
        $action.' matches route-model binding names',
    );
}
check(preg_match('/function\s+move\([^)]*string\s+\$direction/s', $controller) === 1, 'move matches direction route parameter');
check(str_contains($routes, '/providers/{provider}/move/{direction}'), 'move route has provider and direction parameters');
check(str_contains($routes, "Route::delete('/games/{game}/servers/{server}/providers/{provider}'"), 'delete route uses DELETE');
check(str_contains($routes, "Route::patch('/games/{game}/servers/{server}/providers/{provider}/move/{direction}'"), 'move route uses PATCH');
check(str_contains($routes, "middleware('can:gaminghub.providers.manage')"), 'provider mutations are authorization protected');
check(str_contains($request, "can('gaminghub.providers.manage')"), 'provider form request authorizes management');

// Creation and extension-controller compatibility.
check(str_contains($controller, '$request->providerData()'), 'Core controller consumes sanitized provider data');
check(str_contains($validator, 'ProviderConfigurationInput::reconcile($configuration, $raw, $allowed)'), 'configuration validator persists only registry-declared keys');
check(! str_contains($validator, 'Unknown configuration keys'), 'transient extension form keys do not break another provider type');
check(str_contains($request, 'public function providerData()'), 'Core request exposes generic provider data');
check(str_contains($request, "if (\$field->type === 'boolean')"), 'generic boolean fields are normalized');
check(str_contains($form, "\$field->type==='boolean'"), 'generic form renders boolean fields');
check(str_contains($form, "\$field->type==='integer'?'number':'text'"), 'generic form renders integer fields');
check(str_contains($observer, 'public function creating(ProviderInstance $provider)'), 'extension-owned creates inherit Core ordering');
check(str_contains($observer, "'position' => ((int) ProviderInstance::query()"), 'extension-owned creates append sequentially');
check(str_contains($observer, "foreach (['game_id', 'server_id', 'position']"), 'extension-owned edits cannot corrupt ownership or priority');
check(str_contains($provider, 'ProviderInstance::observe(ProviderInstanceObserver::class)'), 'provider observer is registered by Core');
check(str_contains($context, 'private int $managedDepth = 0'), 'observer re-entry has a managed lifecycle guard');

// Server ownership, transactions, locks, and deterministic normalization.
check(str_contains($controller, '(int) $provider->server_id === (int) $server->getKey()') && str_contains($controller, '(int) $provider->game_id === (int) $game->getKey()'), 'wrong game or server ownership is rejected');
check(str_contains($lifecycle, "->where('server_id', \$server->getKey())"), 'lifecycle queries only the selected server');
check(str_contains($lifecycle, 'lockForUpdate()'), 'provider lifecycle uses row locks');
check(substr_count($lifecycle, 'DB::transaction(') >= 6, 'create/update/toggle/move/delete/normalize use transactions');
check(str_contains($lifecycle, 'ProviderPositionSequence::move'), 'move uses production sequence');
check(str_contains($lifecycle, 'ProviderPositionSequence::reposition'), 'create and update use normalized priority');
check(str_contains($lifecycle, "'position' => \$providers->count() + 1"), 'Core creation appends sequentially');
check(str_contains($lifecycle, '$this->persistOrder($server, $remaining->modelKeys(), $remaining)'), 'delete normalizes remaining positions');
check(str_contains($lifecycle, '$position = $offset + 1'), 'runtime normalization is one-based and contiguous');
check(str_contains($lifecycle, "'position' => -(\$offset + 1)"), 'reordering uses collision-safe temporary positions');
check(str_contains($lifecycle, 'Query-builder updates avoid') || str_contains($lifecycle, 'ProviderInstance::query()'), 'reordering bypasses recursive model events');
check(str_contains($migration, "'position' => \$offset + 1"), 'upgrade migration persists one-based contiguous positions');
check(str_contains($migration, "->unique(['server_id', 'position']"), 'database enforces unique server priority');
check(str_contains($model, "orderBy('position')->orderBy('id')"), 'runtime ordering is deterministic');

// Generic deletion and connection safety.
check(str_contains($lifecycle, 'event(new ProviderDeleting($locked))'), 'generic pre-delete extension hook is available');
check(str_contains($lifecycle, 'event(new ProviderDeleted($snapshot))'), 'generic post-delete extension hook is available');
check(str_contains($lifecycle, 'if (! $locked->delete())'), 'provider instance and JSON configuration delete together');
check(! preg_match('/Pelican|Pterodactyl|GamingHubPanel|PanelConnection/', $lifecycle), 'Core lifecycle has no extension-specific dependency');
check(! preg_match('/connection.*delete|delete.*connection/i', $lifecycle), 'provider deletion does not delete global connections');
check(str_contains($controller, "withErrors([\n            'provider'"), 'lifecycle failures return validation errors');
check(str_contains($controller, "with('success'"), 'successful lifecycle actions flash success messages');

// Cache invalidation.
check(str_contains($cache, 'gaminghub:data-generation:'), 'cache has a provider generation key');
check(str_contains($cache, '$this->generation($provider->id)'), 'cache data key includes provider generation');
check(str_contains($cache, '$this->cache->forever($key, $this->generation($providerId) + 1)'), 'provider invalidation advances generation');
check(str_contains($lifecycle, '$this->cache->invalidateProvider($providerId)'), 'delete explicitly invalidates provider cache');
check(str_contains($model, "foreach (['saved', 'deleted'] as \$event)"), 'provider persistence invalidates through model events');

// Capability priority contract.
check(str_contains($instances, "->where('server_id',\$id)->ordered()"), 'provider retrieval is server-scoped and ordered');
check(str_contains($instances, 'if(!$all)$q->enabled()'), 'disabled providers are excluded');
check(str_contains($instances, '$t->supports($cap)'), 'unsupported capabilities are skipped');
check(str_contains($resolver, 'enabledForServerByCapability') && str_contains($resolver, '[0]??null'), 'resolver selects first eligible priority');

// UI/HTTP forms in Core remain correct even when extensions add views.
check(substr_count($view, '@csrf') >= 4, 'provider action forms include CSRF tokens');
check(substr_count($view, "@method('PATCH')") >= 3, 'move and toggle forms spoof PATCH');
check(str_contains($view, "@method('DELETE')"), 'delete form spoofs DELETE');
check(str_contains($view, '@disabled($loop->first)'), 'move-up boundary is disabled');
check(str_contains($view, '@disabled($loop->last)'), 'move-down boundary is disabled');
check(str_contains($view, '$errors->any()'), 'validation errors are rendered');
check(str_contains($view, "session('success')"), 'success messages are rendered');
check(substr_count($view, "@section('title'") === 1, 'provider page has one title section');

if ($failures !== []) {
    fwrite(STDERR, "FAILED ".count($failures)." / {$tests}\n - ".implode("\n - ", $failures)."\n");
    exit(1);
}

echo "PASS {$tests} provider lifecycle checks\n";
