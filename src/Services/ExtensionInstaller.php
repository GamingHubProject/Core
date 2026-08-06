<?php

namespace Azuriom\Plugin\GamingHubCore\Services;

use Azuriom\Plugin\GamingHubCore\Data\ExtensionManifest;
use Azuriom\Plugin\GamingHubCore\Exceptions\ExtensionAlreadyCurrent;
use Azuriom\Plugin\GamingHubCore\Exceptions\ExtensionOperationFailed;
use Azuriom\Plugin\GamingHubCore\Models\ExtensionOperation;
use Azuriom\Plugin\GamingHubCore\Models\ExtensionSource;
use Azuriom\Plugin\GamingHubCore\Models\InstalledExtension;
use Illuminate\Support\Facades\Cache;

final class ExtensionInstaller
{
    public function __construct(
        private SafeExtensionHttpClient $http,
        private ExtensionArchiveInspector $archives,
        private ExtensionChecksumVerifier $checksums,
        private ExtensionCompatibility $compatibility,
        private ExtensionDependencyGuard $dependencies,
        private ExtensionVersionPolicy $versions,
        private ExtensionPathGuard $paths,
        private InstalledExtensionResolver $installed,
        private AzuriomPluginLifecycle $lifecycle,
        private ExtensionSafeMessage $messages,
    ) {
    }

    public function install(
        ExtensionSource $source,
        array $release,
        array $asset,
        ?string $expectedChecksum,
        int $actor,
        bool $enable = false,
        ?string $expectedExtensionId = null,
        ?ExtensionOperation $operation = null,
    ): InstalledExtension {
        $operation ??= $this->newOperation('install', $source, $actor, $expectedExtensionId);
        $lockId = $expectedExtensionId ?? (string) ($asset['name'] ?? 'package');
        $lock = Cache::lock('gaminghub:extension-operation:'.$lockId, 300);
        $locked = false;
        $work = $this->workDirectory($operation);
        $incomingSwap = null;
        $live = null;
        $moved = false;
        $record = null;

        try {
            if (! $lock->get()) {
                throw new ExtensionOperationFailed('Another lifecycle operation for this extension is already running.');
            }
            $locked = true;

            if ($expectedExtensionId !== null && $expectedExtensionId !== 'direct') {
                $expectedExtensionId = $this->paths->validateId($expectedExtensionId);
                if (InstalledExtension::where('extension_id', $expectedExtensionId)->exists()
                    || file_exists($this->paths->destination($expectedExtensionId))) {
                    throw new ExtensionOperationFailed('This extension is already installed; use Update instead.');
                }
            }

            [$manifest, $actualChecksum, $staged] = $this->preparePackage(
                $operation,
                $source,
                $asset,
                $expectedChecksum,
                $work,
            );

            if ($expectedExtensionId !== null && $expectedExtensionId !== 'direct' && $manifest->id !== $expectedExtensionId) {
                throw new ExtensionOperationFailed('Downloaded manifest ID does not match the selected extension.');
            }

            if (InstalledExtension::where('extension_id', $manifest->id)->exists()) {
                throw new ExtensionOperationFailed('This extension is already installed; use Update instead.');
            }

            $live = $this->paths->destination($manifest->id);
            if (file_exists($live)) {
                throw new ExtensionOperationFailed('This extension directory already exists; use Update after metadata reconciliation.');
            }

            $operation->transition('staging', 'Copying the validated package to same-filesystem staging.');
            $incomingSwap = $this->incomingSwapPath($operation);
            $this->paths->copyDirectory($staged, $incomingSwap);
            $this->verifyPreparedDirectory($incomingSwap, $manifest);

            $operation->transition('replacing', 'Moving the validated extension into the plugins directory.');
            if (! rename($incomingSwap, $live)) {
                throw new ExtensionOperationFailed('Atomic extension directory move failed.');
            }
            $moved = true;

            $operation->transition('migrating', 'Running extension migrations.');
            $this->lifecycle->refresh();
            $this->lifecycle->migrate($manifest->id);

            $enabled = false;
            $operation->transition('enabling', $enable
                ? 'Enabling the extension through the Azuriom lifecycle.'
                : 'Leaving the extension disabled as requested.');
            if ($enable) {
                if (! $this->lifecycle->enable($manifest->id) || ! $this->lifecycle->isEnabled($manifest->id)) {
                    throw new ExtensionOperationFailed('Azuriom could not enable the newly installed extension.');
                }
                $enabled = true;
            }

            $record = InstalledExtension::create($this->metadataValues(
                $source,
                $release,
                $asset,
                $manifest,
                $actualChecksum,
                $expectedChecksum !== null,
                $actor,
                $enabled,
                null,
            ));

            $operation->transition('cleaning', 'Clearing plugin and application caches.');
            $this->lifecycle->refresh();
            $this->cleanupNonFatal($work, $operation);
            $operation->complete('Extension installed successfully.');

            return $record;
        } catch (\Throwable $exception) {
            $operation->mergeContext(['failed_stage' => $operation->current_stage ?: 'unknown']);
            $rollbackAttempted = $moved || $record !== null;
            $rollbackSucceeded = true;

            if ($rollbackAttempted) {
                $operation->transition('rolling_back', 'Removing the partial installation.');
                try {
                    if ($live !== null && is_dir($live) && $this->lifecycle->isEnabled($operation->extension_id ?? '')) {
                        $this->lifecycle->disable((string) $operation->extension_id);
                    }
                    if ($live !== null && is_dir($live) && $operation->extension_id !== null) {
                        $this->paths->deleteExtension($operation->extension_id);
                    }
                    $record?->delete();
                    $this->lifecycle->refresh();
                } catch (\Throwable) {
                    $rollbackSucceeded = false;
                }
            }

            $operation->forceFill([
                'rollback_attempted' => $rollbackAttempted,
                'rollback_succeeded' => $rollbackAttempted ? $rollbackSucceeded : null,
            ])->save();
            $operation->fail(
                $this->messages->fromThrowable($exception),
                'installation_failed',
                $rollbackAttempted ? ($rollbackSucceeded ? 'rolled_back' : 'rollback_failed') : 'failed',
            );

            throw $exception;
        } finally {
            if ($locked) {
                $lock->release();
            }
            $this->cleanupQuietly($work);
            if ($incomingSwap !== null) {
                $this->cleanupQuietly($incomingSwap);
            }
        }
    }

    public function update(
        InstalledExtension $installed,
        ExtensionSource $source,
        array $release,
        array $asset,
        ?string $expectedChecksum,
        int $actor,
        ?ExtensionOperation $operation = null,
    ): InstalledExtension {
        $extensionId = $this->paths->validateId($installed->extension_id);
        $operation ??= $this->newOperation('update', $source, $actor, $extensionId);
        $lock = Cache::lock('gaminghub:extension-operation:'.$extensionId, 300);
        $locked = false;
        $work = $this->workDirectory($operation);
        $incomingSwap = null;
        $previousSwap = null;
        $backupPath = null;
        $backupComplete = false;
        $live = null;
        $wasEnabled = false;
        $disabled = false;
        $oldMoved = false;
        $newMoved = false;
        $metadataWritten = false;
        $metadata = $installed->getAttributes();
        $currentManifest = null;
        $currentVersion = $installed->installed_version;

        try {
            if (! $lock->get()) {
                throw new ExtensionOperationFailed('Another lifecycle operation for this extension is already running.');
            }
            $locked = true;

            $operation->transition('resolving', 'Resolving the installed extension and its current manifest.');
            $live = $this->paths->destination($extensionId, true);
            $currentManifest = $this->installed->readManifest($live, $extensionId);
            if ($currentManifest->id !== $installed->extension_id) {
                throw new ExtensionOperationFailed('Installed extension metadata does not match the current manifest.');
            }
            $currentVersion = $currentManifest->version;

            $wasEnabled = $this->lifecycle->isEnabled($extensionId);
            $operation->mergeContext([
                'installed_version' => $currentVersion,
                'metadata_version_before' => $installed->installed_version,
                'enabled_before' => $wasEnabled,
            ]);

            [$manifest, $actualChecksum, $staged] = $this->preparePackage(
                $operation,
                $source,
                $asset,
                $expectedChecksum,
                $work,
            );

            if ($manifest->id !== $extensionId || $manifest->pluginDirectory !== $extensionId) {
                throw new ExtensionOperationFailed('Update manifest ID does not match the installed extension.');
            }
            $this->versions->assertNewer($manifest->version, $currentVersion);
            $this->dependencies->assertUpdateAllowed($manifest);

            $operation->transition('staging', 'Copying the validated update to same-filesystem staging.');
            $incomingSwap = $this->incomingSwapPath($operation);
            $this->paths->copyDirectory($staged, $incomingSwap);
            $this->verifyPreparedDirectory($incomingSwap, $manifest);

            $operation->transition('backing_up', 'Creating a timestamped backup of the installed extension.');
            $backupPath = $this->backupPath($operation, $extensionId);
            $this->paths->copyDirectory($live, $backupPath);
            $this->verifyPreparedDirectory($backupPath, $currentManifest);
            $backupComplete = true;
            $operation->mergeContext(['backup' => basename(dirname($backupPath)).'/'.$extensionId]);

            $operation->transition('disabling', $wasEnabled
                ? 'Disabling the extension before replacement.'
                : 'Extension was already disabled; preserving that state.');
            if ($wasEnabled) {
                $disableResult = $this->lifecycle->disable($extensionId);
                $disabled = ! $this->lifecycle->isEnabled($extensionId);
                if (! $disableResult || ! $disabled) {
                    throw new ExtensionOperationFailed('Azuriom could not disable the extension before update.');
                }
            }

            $operation->transition('replacing', 'Atomically replacing the installed extension directory.');
            $previousSwap = $this->previousSwapPath($operation);
            if (! rename($live, $previousSwap)) {
                throw new ExtensionOperationFailed('Unable to move the current extension into replacement staging.');
            }
            $oldMoved = true;

            if (! rename($incomingSwap, $live)) {
                if (rename($previousSwap, $live)) {
                    $oldMoved = false;
                }
                throw new ExtensionOperationFailed('Atomic update directory move failed.');
            }
            $newMoved = true;

            $operation->transition('migrating', 'Running migrations for the updated extension.');
            $this->lifecycle->refresh();
            $this->lifecycle->migrate($extensionId);

            $operation->transition('enabling', $wasEnabled
                ? 'Restoring the previously enabled state.'
                : 'Keeping the extension disabled.');
            if ($wasEnabled) {
                if (! $this->lifecycle->enable($extensionId) || ! $this->lifecycle->isEnabled($extensionId)) {
                    throw new ExtensionOperationFailed('Azuriom could not re-enable the updated extension.');
                }
            } elseif ($this->lifecycle->isEnabled($extensionId)) {
                if (! $this->lifecycle->disable($extensionId)) {
                    throw new ExtensionOperationFailed('Updated extension did not remain disabled.');
                }
            }

            $installed->forceFill($this->metadataValues(
                $source,
                $release,
                $asset,
                $manifest,
                $actualChecksum,
                $expectedChecksum !== null,
                $actor,
                $wasEnabled,
                $installed->installed_at,
            ))->save();
            $metadataWritten = true;

            $operation->transition('cleaning', 'Clearing caches and finalizing replacement data.');
            $this->lifecycle->refresh();
            if ($previousSwap !== null && is_dir($previousSwap)) {
                $this->paths->deleteDirectory($previousSwap);
                $oldMoved = false;
            }
            $this->cleanupNonFatal($work, $operation);
            if (! (bool) config('gaming-hub-core.extensions.retain_successful_update_backups', true) && $backupPath !== null) {
                $this->cleanupNonFatal(dirname($backupPath), $operation);
            }

            $operation->complete('Extension updated from '.$currentVersion.' to '.$manifest->version.'.');

            return $installed->refresh();
        } catch (ExtensionAlreadyCurrent $exception) {
            $operation->transition('cleaning', 'No replacement was required.');
            if ($currentManifest !== null && $installed->installed_version !== $currentVersion) {
                $installed->forceFill([
                    'installed_version' => $currentVersion,
                    'manifest_snapshot' => $currentManifest->toArray(),
                    'enabled_snapshot' => $wasEnabled,
                ])->save();
            }
            $this->cleanupNonFatal($work, $operation);
            $operation->complete('Extension is already up to date.');

            return $installed->refresh();
        } catch (\Throwable $exception) {
            $failedStage = $operation->current_stage ?: 'unknown';
            $operation->mergeContext(['failed_stage' => $failedStage]);
            $rollbackNeeded = $disabled || $oldMoved || $newMoved || $metadataWritten;
            $rollbackSucceeded = null;

            if ($rollbackNeeded) {
                $rollbackSucceeded = $this->rollbackUpdate(
                    $operation,
                    $installed,
                    $metadata,
                    $extensionId,
                    $wasEnabled,
                    $live,
                    $previousSwap,
                    $backupPath,
                );
            }

            $operation->forceFill([
                'rollback_attempted' => $rollbackNeeded,
                'rollback_succeeded' => $rollbackNeeded ? $rollbackSucceeded : null,
            ])->save();
            $operation->fail(
                $this->messages->fromThrowable($exception),
                'update_failed',
                $rollbackNeeded ? ($rollbackSucceeded ? 'rolled_back' : 'rollback_failed') : 'failed',
            );

            throw $exception;
        } finally {
            if ($locked) {
                $lock->release();
            }
            $this->cleanupQuietly($work);
            if ($incomingSwap !== null) {
                $this->cleanupQuietly($incomingSwap);
            }
            if ($backupPath !== null && ! $backupComplete) {
                $this->cleanupQuietly(dirname($backupPath));
            }
        }
    }

    /**
     * @return array{0: ExtensionManifest, 1: string, 2: string}
     */
    private function preparePackage(
        ExtensionOperation $operation,
        ExtensionSource $source,
        array $asset,
        ?string $expectedChecksum,
        string $work,
    ): array {
        if (! is_dir($work) && ! mkdir($work, 0755, true) && ! is_dir($work)) {
            throw new ExtensionOperationFailed('Unable to create the extension operation staging directory.');
        }

        $operation->transition('downloading', 'Downloading the selected HTTPS release package.');
        $zip = $work.'/package.zip';
        $url = (string) ($asset['browser_download_url'] ?? $asset['url'] ?? '');
        $this->http->download($url, $zip, $source->allow_private_host);

        $operation->transition('validating', 'Validating checksum, archive paths, and manifests.');
        $actualChecksum = $this->checksums->verify($zip, $expectedChecksum);
        $extract = $work.'/extract';
        $manifest = $this->archives->inspect($zip, $extract);
        $operation->forceFill([
            'extension_id' => $manifest->id,
            'version' => $manifest->version,
        ])->save();

        $this->compatibility->assertCompatible(
            $manifest,
            $this->coreVersion(),
            $this->azuriomVersion(),
            PHP_VERSION,
        );
        $this->dependencies->assertCandidateDependencies($manifest);
        $staged = $extract.'/'.$manifest->pluginDirectory;
        $this->paths->assertStagedDirectory($staged, $extract, $manifest->id);
        $operation->appendEvent('validating', $expectedChecksum !== null
            ? 'SHA-256 checksum and package manifests verified.'
            : 'Package manifests verified; no registry checksum was supplied.');
        $operation->save();

        return [$manifest, $actualChecksum, $staged];
    }

    private function rollbackUpdate(
        ExtensionOperation $operation,
        InstalledExtension $installed,
        array $metadata,
        string $extensionId,
        bool $wasEnabled,
        ?string $live,
        ?string $previousSwap,
        ?string $backupPath,
    ): bool {
        $operation->transition('rolling_back', 'Restoring previous extension files, metadata, and enabled state.');

        try {
            if ($this->lifecycle->isEnabled($extensionId)) {
                $this->lifecycle->disable($extensionId);
            }

            if ($live !== null && is_dir($live)) {
                $this->paths->deleteExtension($extensionId);
            }

            if ($previousSwap !== null && is_dir($previousSwap)) {
                if (! rename($previousSwap, (string) $live)) {
                    throw new ExtensionOperationFailed('Unable to restore the previous extension directory.');
                }
            } elseif ($backupPath !== null && is_dir($backupPath)) {
                $this->paths->copyDirectory($backupPath, (string) $live);
            } else {
                throw new ExtensionOperationFailed('No valid previous extension backup was available.');
            }

            $restored = InstalledExtension::find($metadata['id'] ?? null) ?? $installed;
            $restored->forceFill($metadata);
            $restored->exists = true;
            $restored->save();

            $this->lifecycle->refresh();
            if ($wasEnabled) {
                if (! $this->lifecycle->enable($extensionId) || ! $this->lifecycle->isEnabled($extensionId)) {
                    throw new ExtensionOperationFailed('Previous enabled state could not be restored.');
                }
            } elseif ($this->lifecycle->isEnabled($extensionId)) {
                if (! $this->lifecycle->disable($extensionId)) {
                    throw new ExtensionOperationFailed('Previous disabled state could not be restored.');
                }
            }

            return true;
        } catch (\Throwable $rollbackException) {
            $operation->appendEvent('rollback_failed', $this->messages->fromThrowable($rollbackException), 'error');
            $operation->save();

            return false;
        }
    }

    private function metadataValues(
        ExtensionSource $source,
        array $release,
        array $asset,
        ExtensionManifest $manifest,
        string $actualChecksum,
        bool $checksumVerified,
        int $actor,
        bool $enabled,
        mixed $installedAt,
    ): array {
        return [
            'extension_id' => $manifest->id,
            'installed_version' => $manifest->version,
            'source_type' => $source->type,
            'source_id' => $source->source_id,
            'source_url' => $source->url,
            'repository_url' => $manifest->repository,
            'release_url' => $release['html_url'] ?? null,
            'release_id' => (string) ($release['id'] ?? ''),
            'asset_name' => (string) ($asset['name'] ?? 'unknown'),
            'checksum' => $actualChecksum,
            'checksum_verified' => $checksumVerified,
            'trust_level' => $source->trust_level,
            'installed_by' => $actor,
            'installed_at' => $installedAt ?? now(),
            'enabled_snapshot' => $enabled,
            'manifest_snapshot' => $manifest->toArray(),
            'last_operation_result' => 'completed',
        ];
    }

    private function verifyPreparedDirectory(string $path, ExtensionManifest $manifest): void
    {
        foreach (['plugin.json', 'gaming-hub-extension.json'] as $required) {
            if (! is_file($path.'/'.$required)) {
                throw new ExtensionOperationFailed('Prepared extension directory is incomplete.');
            }
        }

        $plugin = json_decode((string) file_get_contents($path.'/plugin.json'), true);
        $extension = json_decode((string) file_get_contents($path.'/gaming-hub-extension.json'), true);
        if (($plugin['id'] ?? null) !== $manifest->id
            || ($plugin['version'] ?? null) !== $manifest->version
            || ($extension['id'] ?? null) !== $manifest->id
            || ($extension['version'] ?? null) !== $manifest->version) {
            throw new ExtensionOperationFailed('Prepared extension manifests changed after validation.');
        }
    }

    private function newOperation(
        string $type,
        ExtensionSource $source,
        int $actor,
        ?string $extensionId,
    ): ExtensionOperation {
        return ExtensionOperation::create([
            'operation_uuid' => (string) \Illuminate\Support\Str::uuid(),
            'operation' => $type,
            'extension_id' => $extensionId === 'direct' ? null : $extensionId,
            'source_id' => $source->source_id,
            'actor_id' => $actor,
            'started_at' => now(),
            'result' => 'running',
            'current_stage' => 'resolving',
            'events' => [[
                'at' => now()->toIso8601String(),
                'stage' => 'resolving',
                'level' => 'info',
                'message' => ucfirst($type).' operation started.',
            ]],
        ]);
    }


    private function azuriomVersion(): string
    {
        if (class_exists(\Azuriom\Azuriom::class) && method_exists(\Azuriom\Azuriom::class, 'version')) {
            return (string) \Azuriom\Azuriom::version();
        }

        return (string) (defined('AZURIOM_VERSION') ? AZURIOM_VERSION : config('app.version', '1.2.0'));
    }

    private function coreVersion(): string
    {
        $path = base_path('plugins/gaming-hub-core/plugin.json');
        $plugin = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;

        return is_array($plugin) && is_string($plugin['version'] ?? null)
            ? $plugin['version']
            : '0.6.6';
    }

    private function workDirectory(ExtensionOperation $operation): string
    {
        return storage_path('app/gaming-hub/extensions/staging/'.$operation->operation_uuid);
    }

    private function incomingSwapPath(ExtensionOperation $operation): string
    {
        return $this->paths->pluginsRoot(true).'/.gaming-hub-incoming-'.$operation->operation_uuid;
    }

    private function previousSwapPath(ExtensionOperation $operation): string
    {
        return $this->paths->pluginsRoot(true).'/.gaming-hub-previous-'.$operation->operation_uuid;
    }

    private function backupPath(ExtensionOperation $operation, string $extensionId): string
    {
        $directory = storage_path('app/gaming-hub/extensions/backups/'.now()->format('Ymd_His').'-'.$operation->operation_uuid);
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new ExtensionOperationFailed('Unable to create the update backup directory.');
        }

        return $directory.'/'.$extensionId;
    }

    private function cleanupNonFatal(string $path, ExtensionOperation $operation): void
    {
        try {
            $this->cleanupQuietly($path, false);
        } catch (\Throwable $exception) {
            $operation->appendEvent('cleaning', 'Cleanup warning: '.$this->messages->fromThrowable($exception), 'warning');
            $operation->save();
        }
    }

    private function cleanupQuietly(?string $path, bool $suppress = true): void
    {
        if ($path === null || ! file_exists($path)) {
            return;
        }

        try {
            $this->paths->deleteDirectory($path);
        } catch (\Throwable $exception) {
            if (! $suppress) {
                throw $exception;
            }
        }
    }
}
