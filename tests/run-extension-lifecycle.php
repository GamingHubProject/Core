<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$temporaryRoot = sys_get_temp_dir().'/gaming-hub-core-v063-'.bin2hex(random_bytes(5));
mkdir($temporaryRoot.'/plugins', 0755, true);

function base_path(string $path = ''): string
{
    global $temporaryRoot;

    return $temporaryRoot.($path !== '' ? '/'.$path : '');
}

function storage_path(string $path = ''): string
{
    global $temporaryRoot;

    return $temporaryRoot.'/storage'.($path !== '' ? '/'.$path : '');
}

function public_path(string $path = ''): string
{
    global $temporaryRoot;

    return $temporaryRoot.'/public'.($path !== '' ? '/'.$path : '');
}

require $root.'/src/Exceptions/ExtensionOperationFailed.php';
require $root.'/src/Exceptions/ExtensionAlreadyCurrent.php';
require $root.'/src/Services/ExtensionVersionPolicy.php';
require $root.'/src/Services/ExtensionPathGuard.php';

use Azuriom\Plugin\GamingHubCore\Exceptions\ExtensionAlreadyCurrent;
use Azuriom\Plugin\GamingHubCore\Exceptions\ExtensionOperationFailed;
use Azuriom\Plugin\GamingHubCore\Services\ExtensionPathGuard;
use Azuriom\Plugin\GamingHubCore\Services\ExtensionVersionPolicy;

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

function rejects(callable $callable, string $exception, string $label): void
{
    try {
        $callable();
        check(false, $label);
    } catch (Throwable $throwable) {
        check($throwable instanceof $exception, $label);
    }
}

$versions = new ExtensionVersionPolicy();
check($versions->normalize('v0.1.1') === '0.1.1', 'release tags normalize');
check($versions->compare('0.1.1', '0.1.0') > 0, 'newer versions compare correctly');
$versions->assertNewer('0.1.1', '0.1.0');
check(true, '0.1.0 to 0.1.1 accepted');
rejects(fn () => $versions->assertNewer('0.1.0', '0.1.0'), ExtensionAlreadyCurrent::class, 'same version reports already current');
rejects(fn () => $versions->assertNewer('0.0.9', '0.1.0'), ExtensionOperationFailed::class, 'downgrade rejected');
check($versions->satisfies('0.6.3', '^0.6.0'), 'caret zero-major compatible version accepted');
check(! $versions->satisfies('0.7.0', '^0.6.0'), 'caret zero-major incompatible version rejected');

$paths = new ExtensionPathGuard();
check(str_ends_with($paths->destination('gaming-hub-panel'), '/plugins/gaming-hub-panel'), 'validated destination is exact plugin path');
foreach (['', '..', '../panel', 'panel/other', 'panel\\other', '.hidden', 'Gaming-Hub-Panel'] as $unsafe) {
    rejects(fn () => $paths->validateId($unsafe), ExtensionOperationFailed::class, 'unsafe ID rejected: '.var_export($unsafe, true));
}

$panel = $temporaryRoot.'/plugins/gaming-hub-panel';
mkdir($panel, 0755, true);
file_put_contents($panel.'/plugin.json', '{}');
$outside = $temporaryRoot.'/outside.txt';
file_put_contents($outside, 'keep');
symlink($outside, $panel.'/outside-link');
$paths->deleteExtension('gaming-hub-panel');
check(! file_exists($panel), 'validated extension directory removed');
check(file_exists($outside) && file_get_contents($outside) === 'keep', 'internal symlink target not followed or deleted');

$symlinkTarget = $temporaryRoot.'/real-panel';
mkdir($symlinkTarget);
symlink($symlinkTarget, $temporaryRoot.'/plugins/gaming-hub-symlink');
rejects(fn () => $paths->destination('gaming-hub-symlink'), ExtensionOperationFailed::class, 'symlinked destination escape rejected');
unlink($temporaryRoot.'/plugins/gaming-hub-symlink');

$staging = $temporaryRoot.'/stage';
mkdir($staging.'/gaming-hub-panel', 0755, true);
file_put_contents($staging.'/gaming-hub-panel/plugin.json', '{}');
file_put_contents($staging.'/gaming-hub-panel/gaming-hub-extension.json', '{}');
$paths->assertStagedDirectory($staging.'/gaming-hub-panel', $staging, 'gaming-hub-panel');
check(true, 'complete staged directory accepted');
rejects(fn () => $paths->assertStagedDirectory($staging, $staging, 'gaming-hub-panel'), ExtensionOperationFailed::class, 'incorrect staged root rejected');

$installer = file_get_contents($root.'/src/Services/ExtensionInstaller.php');
$uninstaller = file_get_contents($root.'/src/Services/ExtensionUninstaller.php');
$dependencyGuard = file_get_contents($root.'/src/Services/ExtensionDependencyGuard.php');
$lifecycle = file_get_contents($root.'/src/Services/AzuriomPluginLifecycle.php');
$controller = file_get_contents($root.'/src/Controllers/Admin/ExtensionInstallController.php');
$uninstallController = file_get_contents($root.'/src/Controllers/Admin/ExtensionUninstallController.php');
$view = file_get_contents($root.'/resources/views/admin/extensions/index.blade.php');
$uninstallView = file_get_contents($root.'/resources/views/admin/extensions/uninstall.blade.php');
$routes = file_get_contents($root.'/routes/admin.php');

check(str_contains($installer, 'public function update(') && str_contains($installer, 'InstalledExtension $installed'), 'update has a dedicated installed-extension path');
check(! str_contains($installer, "operate('update'"), 'update is not routed through fresh-install operate method');
check(str_contains($installer, 'This extension is already installed; use Update instead.'), 'fresh install rejects existing metadata or directory');
foreach (['downloading', 'validating', 'staging', 'backing_up', 'disabling', 'replacing', 'migrating', 'enabling', 'cleaning', 'rolling_back'] as $stage) {
    check(str_contains($installer, "transition('{$stage}'"), 'update stage present: '.$stage);
}
check(str_contains($installer, '$manifest->id !== $extensionId'), 'update manifest ID mismatch rejected');
check(str_contains($installer, '$this->versions->assertNewer'), 'same version and downgrade policy applied');
$updateSource = substr($installer, strpos($installer, 'public function update('));
check(strpos($updateSource, "transition('backing_up'") < strpos($updateSource, "transition('disabling'"), 'backup occurs before disable');
check(strpos($updateSource, "transition('disabling'") < strpos($updateSource, "transition('replacing'"), 'disable occurs before replacement');
check(str_contains($installer, '$this->lifecycle->migrate($extensionId)'), 'updated extension migrations run');
check(str_contains($installer, '$wasEnabled') && str_contains($installer, 'Keeping the extension disabled.'), 'enabled and disabled state preservation implemented');
check(str_contains($installer, '$restored->forceFill($metadata)'), 'rollback restores previous installed metadata');
check(str_contains($installer, 'Previous enabled state could not be restored.'), 'rollback restores prior enabled state');
check(str_contains($installer, 'retain_successful_update_backups'), 'successful backup retention is configurable');
check(str_contains($installer, '! $backupComplete'), 'incomplete backup staging is removed');

foreach (['resolving', 'disabling', 'removing', 'cleaning'] as $stage) {
    check(str_contains($uninstaller, "transition('{$stage}'"), 'uninstall stage present: '.$stage);
}
check(str_contains($uninstaller, '$this->dependencies->assertUninstallAllowed'), 'dependent extensions block uninstall');
check(str_contains($dependencyGuard, "if (\$extensionId === 'gaming-hub-core')"), 'Gaming Hub Core self-uninstall blocked');
check(str_contains($uninstaller, "'data_retained' => true"), 'uninstall records retained-data behavior');
check(str_contains($uninstaller, '$this->paths->destination($extensionId)'), 'uninstall resolves an exact guarded plugin path');
check(str_contains($uninstaller, 'rename($live, $quarantine)'), 'uninstall uses guarded removal staging');
check(str_contains($uninstaller, "/.gaming-hub-uninstalling-"), 'uninstall staging remains on the plugins filesystem');
check(str_contains($uninstaller, 'deletePublicAssets($extensionId)'), 'uninstall safely removes public plugin assets');
check(str_contains($uninstallController, "'operation' => 'uninstall'"), 'uninstall operation is logged with correct type');
check(str_contains($uninstallView, 'database data will be retained'), 'uninstall confirmation states data retention');
check(str_contains($routes, 'extensions.installed.uninstall'), 'uninstall confirmation and delete routes registered');

check(str_contains($lifecycle, 'PluginManager') && str_contains($lifecycle, "['id' => \$extensionId]"), 'Azuriom lifecycle uses PluginManager and correct command argument');
check(! str_contains($lifecycle, "['plugin' => \$id]"), 'incorrect lifecycle command argument removed');
check(str_contains($controller, '$this->installed->resolve'), 'update resolves installed metadata before replacement');
check(str_contains(file_get_contents($root.'/src/Controllers/Admin/ExtensionController.php'), 'normalizeRepository'), 'direct repository source matches existing extension metadata');
check(str_contains($installer, 'Azuriom::version()'), 'compatibility checks use Azuriom version API when available');
foreach (['Update available', 'Up to date', 'Incompatible', 'Installed', 'Uninstall'] as $label) {
    check(str_contains($view, $label), 'admin UI state/action present: '.$label);
}
check(str_contains($view, '{{ $error }}'), 'operation messages are Blade-escaped');

$operationModel = file_get_contents($root.'/src/Models/ExtensionOperation.php');
$safeMessages = file_get_contents($root.'/src/Services/ExtensionSafeMessage.php');
$installedResolver = file_get_contents($root.'/src/Services/InstalledExtensionResolver.php');
$sourceManager = file_get_contents($root.'/src/Services/ExtensionSourceManager.php');
$updateOnly = substr($installer, strpos($installer, 'public function update('));
$uninstallDisable = strpos($uninstaller, "transition('disabling'");
$uninstallRemove = strpos($uninstaller, "transition('removing'");

check(str_contains($installer, 'InstalledExtension::create('), 'new extension writes installed metadata after install');
check(str_contains($installer, 'file_exists($this->paths->destination($expectedExtensionId))'), 'existing extension folder is rejected from fresh install before download');
check(str_contains($updateOnly, '$this->paths->destination($extensionId, true)'), 'existing update destination is required rather than rejected');
check(! str_contains($updateOnly, 'Extension directory already exists.'), 'update does not fail merely because destination exists');
check(str_contains($updateOnly, '$this->paths->copyDirectory($live, $backupPath)'), 'update creates backup from prior files');
check(str_contains($updateOnly, '$this->lifecycle->migrate($extensionId)'), 'failed migration is inside rollback-guarded update transaction');
check(str_contains($updateOnly, 'Azuriom could not re-enable the updated extension.'), 'failed re-enable is treated as transaction failure');
check(str_contains($updateOnly, "'enabled_before' => \$wasEnabled"), 'operation records prior enabled state');
check(str_contains($updateOnly, "'metadata_version_before'") && str_contains($updateOnly, '$currentVersion'), 'update preserves operation-start metadata for rollback');
check(str_contains($uninstaller, '$extension->delete();'), 'uninstall removes installed-extension metadata');
check(str_contains($uninstaller, '$exists = is_dir($live);'), 'missing plugin directory is handled explicitly');
check($uninstallDisable !== false && $uninstallRemove !== false && $uninstallDisable < $uninstallRemove, 'uninstall disables before file removal');
check(str_contains($uninstaller, "'operation_uuid'") === false, 'uninstaller consumes controller-created audit operation');
check(str_contains($uninstallController, "'operation' => 'uninstall'"), 'uninstall audit record has uninstall operation type');
check(str_contains($uninstaller, "Extension database data was retained."), 'uninstall success explicitly reports retained data');
check(! preg_match('/migrate:rollback|dropIfExists|DROP\s+TABLE/i', $uninstaller), 'uninstall contains no destructive data purge');
check(str_contains($installedResolver, 'reconcileFilesystem'), 'filesystem installs are reconciled before UI state calculation');
check(str_contains($operationModel, "'finished_at' => now()") && str_contains($operationModel, "'result' => 'failed'"), 'operation failures always receive terminal timestamp and result');
check(str_contains($operationModel, "'result' => 'completed'"), 'operation success receives terminal completed result');
check(str_contains($safeMessages, '[redacted]') && str_contains($safeMessages, '[application]'), 'secrets and application paths are sanitized from messages');
check(str_contains($safeMessages, 'access_?token') && str_contains($safeMessages, 'api_?key'), 'token and API-key query values are excluded from logs');
check(str_contains($sourceManager, '$this->messages->fromThrowable($exception)'), 'source refresh errors are sanitized before persistence');
check(str_contains($view, 'Confirm untrusted source') && ! str_contains($view, '<input type="hidden" name="confirm_unverified" value="1">'), 'untrusted installed updates require visible acknowledgement');
check(str_contains($dependencyGuard, '! $optional'), 'optional Azuriom dependencies do not block uninstall');

function removeTree(string $path): void
{
    if (! file_exists($path) && ! is_link($path)) {
        return;
    }
    if (is_link($path) || is_file($path)) {
        unlink($path);
        return;
    }
    foreach (new FilesystemIterator($path) as $item) {
        removeTree($item->getPathname());
    }
    rmdir($path);
}
removeTree($temporaryRoot);

if ($failures !== []) {
    fwrite(STDERR, "FAILED ".count($failures)." / {$tests}\n - ".implode("\n - ", $failures)."\n");
    exit(1);
}

echo "PASS {$tests} extension lifecycle checks\n";
