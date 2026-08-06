<?php

namespace Azuriom\Plugin\GamingHubCore\Services;

use Azuriom\Plugin\GamingHubCore\Exceptions\ExtensionOperationFailed;
use Azuriom\Plugin\GamingHubCore\Models\InstalledExtension;

final class InstalledExtensionResolver
{
    public function __construct(
        private ExtensionPathGuard $paths,
        private ExtensionManifestValidator $manifests,
        private AzuriomPluginLifecycle $lifecycle,
    ) {
    }

    public function reconcileFilesystem(): void
    {
        $root = $this->paths->pluginsRoot();
        $entries = scandir($root) ?: [];

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || str_starts_with($entry, '.') || $entry === 'gaming-hub-core') {
                continue;
            }

            try {
                $this->resolve($entry, true);
            } catch (\Throwable) {
                // Non-Gaming-Hub plugins and malformed local directories are ignored.
            }
        }
    }

    public function resolve(string $extensionId, bool $createMetadata = true): InstalledExtension
    {
        $extensionId = $this->paths->validateId($extensionId);
        if ($extensionId === 'gaming-hub-core') {
            throw new ExtensionOperationFailed('Gaming Hub Core is protected from self-management.');
        }

        $record = InstalledExtension::where('extension_id', $extensionId)->first();
        $path = $this->paths->destination($extensionId);

        if (! is_dir($path)) {
            if ($record !== null) {
                return $record;
            }

            throw new ExtensionOperationFailed('Installed extension metadata and files were not found.');
        }

        $manifest = $this->readManifest($path, $extensionId);
        if ($record === null && ! $createMetadata) {
            throw new ExtensionOperationFailed('Installed extension metadata was not found.');
        }

        $values = [
            'installed_version' => $manifest->version,
            'enabled_snapshot' => $this->lifecycle->isEnabled($extensionId),
            'manifest_snapshot' => $manifest->toArray(),
            'last_operation_result' => $record?->last_operation_result ?? 'discovered',
        ];

        if ($record === null) {
            $values += [
                'source_type' => 'local',
                'source_id' => 'filesystem',
                'repository_url' => $manifest->repository,
                'trust_level' => 'local',
                'installed_at' => now(),
            ];
        }

        return InstalledExtension::updateOrCreate(['extension_id' => $extensionId], $values);
    }

    public function readManifest(string $path, string $expectedId): \Azuriom\Plugin\GamingHubCore\Data\ExtensionManifest
    {
        foreach (['plugin.json', 'gaming-hub-extension.json'] as $required) {
            if (! is_file($path.'/'.$required)) {
                throw new ExtensionOperationFailed('Installed extension is missing '.$required.'.');
            }
        }

        $plugin = json_decode((string) file_get_contents($path.'/plugin.json'), true);
        $extension = json_decode((string) file_get_contents($path.'/gaming-hub-extension.json'), true);
        if (! is_array($plugin) || ! is_array($extension)) {
            throw new ExtensionOperationFailed('Installed extension metadata JSON is invalid.');
        }

        $manifest = $this->manifests->validate($extension, $plugin);
        if ($manifest->id !== $expectedId || $manifest->pluginDirectory !== $expectedId) {
            throw new ExtensionOperationFailed('Installed extension manifest ID does not match its directory.');
        }

        return $manifest;
    }
}
