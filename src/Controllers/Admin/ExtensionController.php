<?php

namespace Azuriom\Plugin\GamingHubCore\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\GamingHubCore\Models\ExtensionOperation;
use Azuriom\Plugin\GamingHubCore\Models\ExtensionSource;
use Azuriom\Plugin\GamingHubCore\Models\InstalledExtension;
use Azuriom\Plugin\GamingHubCore\Services\ExtensionSourceManager;
use Azuriom\Plugin\GamingHubCore\Services\ExtensionVersionPolicy;
use Azuriom\Plugin\GamingHubCore\Services\InstalledExtensionResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ExtensionController extends Controller
{
    public function __construct(
        private ExtensionSourceManager $sources,
        private InstalledExtensionResolver $resolver,
        private ExtensionVersionPolicy $versions,
    ) {
    }

    public function index(Request $request): View
    {
        $this->closeInterruptedOperations();
        $this->sources->ensureOfficial();
        $this->resolver->reconcileFilesystem();

        $sources = ExtensionSource::orderByDesc('type')->orderBy('name')->get();
        $installed = InstalledExtension::orderBy('extension_id')->get();
        $installedById = $installed->keyBy('extension_id');
        $catalog = [];
        $states = [];
        $updates = [];
        $installedStates = [];

        foreach ($sources->where('enabled', true) as $source) {
            try {
                $catalog[$source->id] = $this->sources->refresh($source);
                $this->buildSourceStates($source, $catalog[$source->id], $installedById, $states, $updates, $installedStates);
            } catch (\Throwable) {
                $catalog[$source->id] = ['error' => $source->last_error];
            }
        }

        return view('gaming-hub-core::admin.extensions.index', [
            'sources' => $sources,
            'catalog' => $catalog,
            'catalogStates' => $states,
            'updates' => $updates,
            'installedStates' => $installedStates,
            'installed' => $installed,
            'operations' => ExtensionOperation::latest('started_at')->limit(100)->get(),
            'pluginPath' => base_path('plugins'),
            'stagingPath' => storage_path('app/gaming-hub/extensions/staging'),
            'backupPath' => storage_path('app/gaming-hub/extensions/backups'),
        ]);
    }

    private function buildSourceStates(
        ExtensionSource $source,
        array $sourceData,
        $installedById,
        array &$states,
        array &$updates,
        array &$installedStates,
    ): void {
        if (isset($sourceData['registry'])) {
            foreach ($sourceData['registry']->extensions as $extension) {
                $installed = $installedById->get($extension->id);
                $compatible = $this->previewCompatible($extension->raw['requires'] ?? null, $installedById);
                $state = 'available';

                if (! $compatible) {
                    $state = 'incompatible';
                } elseif ($installed !== null) {
                    $comparison = $this->versions->compare($extension->latestVersion, $installed->installed_version);
                    $state = $comparison > 0 ? 'update' : ($comparison === 0 ? 'up_to_date' : 'installed');
                }

                if ($installed !== null) {
                    $this->rememberInstalledState($installedStates, $extension->id, $state);
                }

                $states[$source->id][$extension->id] = [
                    'state' => $state,
                    'installed' => $installed,
                    'latest_version' => $extension->latestVersion,
                ];

                if ($state === 'update') {
                    $updates[$extension->id] = [
                        'source' => $source,
                        'latest_version' => $extension->latestVersion,
                    ];
                }
            }

            return;
        }

        if (isset($sourceData['release'])) {
            $installed = $installedById->firstWhere('source_id', $source->source_id);
            if ($installed === null) {
                $sourceRepository = $this->normalizeRepository($source->url);
                $installed = $installedById->first(fn ($candidate) =>
                    $this->normalizeRepository((string) $candidate->repository_url) === $sourceRepository
                );
            }
            $latest = $this->versions->normalize((string) ($sourceData['release']['tag_name'] ?? '0.0.0'));
            $state = 'available';
            if ($installed !== null) {
                $comparison = $this->versions->compare($latest, $installed->installed_version);
                $state = $comparison > 0 ? 'update' : ($comparison === 0 ? 'up_to_date' : 'installed');
                if ($state === 'update') {
                    $updates[$installed->extension_id] = [
                        'source' => $source,
                        'latest_version' => $latest,
                    ];
                }
            }

            if ($installed !== null) {
                $this->rememberInstalledState($installedStates, $installed->extension_id, $state);
            }

            $states[$source->id]['direct'] = [
                'state' => $state,
                'installed' => $installed,
                'latest_version' => $latest,
            ];
        }
    }

    private function rememberInstalledState(array &$states, string $extensionId, string $state): void
    {
        $priority = ['update' => 4, 'up_to_date' => 3, 'incompatible' => 2, 'installed' => 1, 'available' => 0];
        $current = $states[$extensionId] ?? null;
        if ($current === null || ($priority[$state] ?? 0) > ($priority[$current] ?? 0)) {
            $states[$extensionId] = $state;
        }
    }

    private function previewCompatible(mixed $requirements, $installedById): bool
    {
        if (! is_array($requirements)) {
            return true;
        }

        $versions = [
            'gaming-hub-core' => $this->coreVersion(),
            'azuriom' => $this->azuriomVersion(),
            'php' => PHP_VERSION,
        ];

        foreach ($versions as $key => $version) {
            $constraint = $requirements[$key] ?? null;
            if (is_string($constraint) && ! $this->versions->satisfies($version, $constraint)) {
                return false;
            }
        }

        foreach (($requirements['extensions'] ?? []) as $id => $constraint) {
            $dependency = $installedById->get($id);
            if (! is_string($constraint)
                || $dependency === null
                || ! $this->versions->satisfies($dependency->installed_version, $constraint)) {
                return false;
            }
        }

        return true;
    }

    private function azuriomVersion(): string
    {
        if (class_exists(\Azuriom\Azuriom::class) && method_exists(\Azuriom\Azuriom::class, 'version')) {
            return (string) \Azuriom\Azuriom::version();
        }

        return (string) (defined('AZURIOM_VERSION') ? AZURIOM_VERSION : config('app.version', '1.2.0'));
    }

    private function normalizeRepository(string $url): string
    {
        $url = strtolower(rtrim(trim($url), '/'));

        return str_ends_with($url, '.git') ? substr($url, 0, -4) : $url;
    }

    private function coreVersion(): string
    {
        $path = base_path('plugins/gaming-hub-core/plugin.json');
        $plugin = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;

        return is_array($plugin) && is_string($plugin['version'] ?? null)
            ? $plugin['version']
            : '0.6.6';
    }

    private function closeInterruptedOperations(): void
    {
        ExtensionOperation::query()
            ->where('result', 'running')
            ->whereNull('finished_at')
            ->where('updated_at', '<', now()->subMinutes(10))
            ->get()
            ->each(fn (ExtensionOperation $operation) => $operation->fail(
                'Operation stopped updating and was marked as interrupted.',
                'interrupted',
            ));
    }
}
