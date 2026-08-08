<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require $root.'/src/Services/ProviderConfigurationInput.php';

use Azuriom\Plugin\GamingHubCore\Services\ProviderConfigurationInput;

$tests = 0;
$failures = [];
$check = static function (bool $condition, string $label) use (&$tests, &$failures): void {
    $tests++;
    if (! $condition) {
        $failures[] = $label;
    }
};
$source = static fn (string $path): string => (string) file_get_contents($root.'/'.$path);

// Reproduce the exact extension FormRequest regression: validated() retained
// only an extension transient key and discarded Manual registry fields.
$reconciled = ProviderConfigurationInput::reconcile(
    ['manual_identifier' => false],
    ['status' => 'online', 'display_message' => 'Ready', 'manual_identifier' => false],
    ['status', 'display_message'],
);
$check($reconciled === ['status' => 'online', 'display_message' => 'Ready'], 'raw declared Manual fields are recovered');
$check(! array_key_exists('manual_identifier', $reconciled), 'extension transient key is not persisted for Manual');

$validatedWins = ProviderConfigurationInput::reconcile(
    ['status' => 'maintenance'],
    ['status' => 'online'],
    ['status'],
);
$check($validatedWins === ['status' => 'maintenance'], 'validated provider value wins over raw input');

$pelican = ProviderConfigurationInput::reconcile(
    ['panel_connection_id' => 7],
    ['panel_connection_id' => 7, 'panel_server_identifier' => 'abc-123', 'client_token_override' => 'secret'],
    ['panel_connection_id', 'panel_server_identifier'],
);
$check($pelican === ['panel_connection_id' => 7, 'panel_server_identifier' => 'abc-123'], 'extension provider declared fields reconcile generically');
$check(! array_key_exists('client_token_override', $pelican), 'credential override is never copied into Core configuration');

$validator = $source('src/Validation/ProviderConfigurationValidator.php');
$middleware = $source('src/Http/Middleware/TraceProviderCreation.php');
$trace = $source('src/Services/ProviderCreationTrace.php');
$controller = $source('src/Controllers/Admin/ProviderController.php');
$lifecycle = $source('src/Services/ProviderLifecycleManager.php');
$observer = $source('src/Observers/ProviderInstanceObserver.php');
$provider = $source('src/Providers/GamingHubCoreServiceProvider.php');
$form = $source('resources/views/admin/providers/_form.blade.php');

$check(str_contains($validator, 'ProviderConfigurationInput::reconcile'), 'validator reconciles extension and raw input');
$check(str_contains($validator, "'configuration.'.\$key"), 'validator prefixes field errors for provider forms');
$check(str_contains($validator, 'ValidationException::withMessages'), 'validator returns normal Laravel validation errors');
$check(str_contains($middleware, "str_ends_with(\$routeName, '.providers.store')"), 'trace covers Core and extension provider store routes');
$check(str_contains($middleware, 'catch (ValidationException $exception)'), 'validation stop is explicitly traced');
$check(str_contains($middleware, "session()->flash('error'"), 'validation errors are visible through Azuriom admin alerts');
$check(str_contains($middleware, 'throw $exception;'), 'validation exception is not suppressed');
$check(str_contains($controller, 'withInput()'), 'controller failures preserve old input');
$check(str_contains($controller, 'report($exception)'), 'controller failures are not ignored');
$check(str_contains($lifecycle, "stage('lifecycle_entered'"), 'lifecycle entry is traced');
$check(str_contains($lifecycle, "stage('provider_dto_built'"), 'provider DTO construction is traced');
$check(str_contains($lifecycle, "model('repository_saved'"), 'repository save is traced');
$check(str_contains($lifecycle, "model('transaction_committed'"), 'transaction commit is traced');
$check(str_contains($observer, "model('model_creating'"), 'extension-owned direct create reaches model trace');
$check(str_contains($observer, "model('model_position_assigned'"), 'extension-owned position assignment is traced');
$check(str_contains($observer, 'public function created'), 'extension-owned repository save is traced');
$check(str_contains($observer, 'DB::afterCommit'), 'extension-owned transaction commit is traced only after commit');
$check(str_contains($provider, "pushMiddlewareToGroup('web', TraceProviderCreation::class)"), 'provider trace middleware is registered');
$check(str_contains($provider, "config/providers.php"), 'provider trace config is loaded');
$check(str_contains($form, '$errors->all()'), 'Core form displays all validation errors');
$check(str_contains($trace, "'configuration_keys'"), 'trace logs configuration key names');
$check(! str_contains($trace, "'configuration' => \$request->input"), 'trace does not log configuration values');
$check(str_contains($trace, "'has_client_token_override'"), 'trace records credential presence only');
$check(! str_contains($trace, "'client_token_override' => \$request->input"), 'trace never logs credential contents');
$check(str_contains($trace, "Gaming Hub provider creation: "), 'trace lines have a stable searchable prefix');
$check(str_contains($source('config/providers.php'), 'GAMING_HUB_PROVIDER_TRACE'), 'temporary trace can be disabled by environment');
$check(str_contains($source('config/providers.php'), "env('GAMING_HUB_PROVIDER_TRACE', false)"), 'provider trace is disabled by default');

// No schema change was added for this validation/controller defect.
$migrations = glob($root.'/database/migrations/*.php') ?: [];
$check(count($migrations) === 11, 'provider creation correction adds no migration');
$check(! file_exists($root.'/database/migrations/2026_08_06_020000_provider_creation.php'), 'no unnecessary provider creation migration exists');

if ($failures !== []) {
    fwrite(STDERR, "FAILED ".count($failures)." / {$tests}\n - ".implode("\n - ", $failures)."\n");
    exit(1);
}

echo "PASS {$tests} provider creation pipeline checks\n";
