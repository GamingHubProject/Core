<?php

namespace Azuriom\Plugin\GamingHubCore\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\GamingHubCore\Models\ExtensionOperation;
use Azuriom\Plugin\GamingHubCore\Models\ExtensionSource;
use Azuriom\Plugin\GamingHubCore\Services\ExtensionInstaller;
use Azuriom\Plugin\GamingHubCore\Services\ExtensionSafeMessage;
use Azuriom\Plugin\GamingHubCore\Services\ExtensionSourceManager;
use Azuriom\Plugin\GamingHubCore\Services\GitHubReleaseClient;
use Azuriom\Plugin\GamingHubCore\Services\InstalledExtensionResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class ExtensionInstallController extends Controller
{
    public function __construct(
        private ExtensionInstaller $installer,
        private ExtensionSourceManager $sources,
        private GitHubReleaseClient $github,
        private InstalledExtensionResolver $installed,
        private ExtensionSafeMessage $messages,
    ) {
    }

    public function install(Request $request, ExtensionSource $source): RedirectResponse
    {
        $data = $request->validate([
            'extension_id' => ['required', 'string', 'max:100'],
            'enable' => ['sometimes', 'boolean'],
            'confirm_unverified' => ['sometimes', 'accepted'],
        ]);

        if (! $source->enabled) {
            return back()->withErrors(['installation' => 'The selected extension source is disabled.']);
        }
        if (! $source->trusted && $source->type !== 'official' && ! $request->boolean('confirm_unverified')) {
            return back()->withErrors(['installation' => 'Explicit untrusted package confirmation is required.']);
        }

        $operation = $this->createOperation('install', $source, (int) $request->user()->getKey(), $data['extension_id']);

        try {
            $this->installed->reconcileFilesystem();
            [$release, $asset, $checksum] = $this->resolve($source, $data['extension_id'], $operation);
            $this->installer->install(
                $source,
                $release,
                $asset,
                $checksum,
                (int) $request->user()->getKey(),
                $request->boolean('enable'),
                $data['extension_id'],
                $operation,
            );

            return redirect()
                ->route('gaming-hub-core.admin.extensions.index', ['tab' => 'installed'])
                ->with('success', 'Extension installed successfully.');
        } catch (\Throwable $exception) {
            $this->finishControllerFailure($operation, $exception, 'installation_failed');

            return $this->failureRedirect($operation, 'Install', $exception);
        }
    }

    public function update(Request $request, ExtensionSource $source): RedirectResponse
    {
        $data = $request->validate([
            'extension_id' => ['required', 'string', 'max:100'],
            'confirm_unverified' => ['sometimes', 'accepted'],
        ]);

        if (! $source->enabled) {
            return back()->withErrors(['update' => 'The selected extension source is disabled.']);
        }
        if (! $source->trusted && $source->type !== 'official' && ! $request->boolean('confirm_unverified')) {
            return back()->withErrors(['update' => 'Explicit untrusted package confirmation is required.']);
        }

        $operation = $this->createOperation('update', $source, (int) $request->user()->getKey(), $data['extension_id']);

        try {
            $this->installed->reconcileFilesystem();
            $installed = $this->installed->resolve($data['extension_id']);
            $before = $installed->installed_version;
            [$release, $asset, $checksum] = $this->resolve($source, $data['extension_id'], $operation);
            $updated = $this->installer->update(
                $installed,
                $source,
                $release,
                $asset,
                $checksum,
                (int) $request->user()->getKey(),
                $operation,
            );

            return redirect()
                ->route('gaming-hub-core.admin.extensions.index', ['tab' => 'installed'])
                ->with($updated->installed_version === $before ? 'warning' : 'success',
                    $updated->installed_version === $before
                        ? 'Extension is already up to date.'
                        : 'Extension updated successfully.');
        } catch (\Throwable $exception) {
            $this->finishControllerFailure($operation, $exception, 'update_failed');

            return $this->failureRedirect($operation, 'Update', $exception);
        }
    }

    private function resolve(ExtensionSource $source, string $id, ExtensionOperation $operation): array
    {
        $operation->transition('resolving', 'Resolving the selected source release.');
        $data = $this->sources->refresh($source, true);

        if ($source->type === 'github') {
            $release = $data['release'];
            $asset = $this->github->selectAsset($release, '*.zip');
            $operation->appendEvent('resolving', 'Packaged GitHub release asset selected.');
            $operation->save();

            return [$release, $asset, null];
        }

        $registry = $data['registry'];
        $entry = collect($registry->extensions)->first(fn ($extension) => $extension->id === $id);
        if ($entry === null) {
            throw new \RuntimeException('Requested extension is not present in the selected registry.');
        }

        $release = $this->github->latest($entry->repository, $source->allow_prereleases);
        $asset = $this->github->selectAsset($release, $entry->releaseAsset);
        $operation->appendEvent('resolving', 'Registry extension and packaged release asset selected.');
        $operation->save();

        return [$release, $asset, $entry->raw['sha256'] ?? null];
    }

    private function createOperation(string $type, ExtensionSource $source, int $actor, string $extensionId): ExtensionOperation
    {
        return ExtensionOperation::create([
            'operation_uuid' => (string) Str::uuid(),
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
                'message' => ucfirst($type).' operation queued.',
            ]],
        ]);
    }

    private function finishControllerFailure(ExtensionOperation $operation, \Throwable $exception, string $category): void
    {
        if ($operation->result === 'running' && $operation->finished_at === null) {
            $operation->fail($this->messages->fromThrowable($exception), $category);
        }
    }

    private function failureRedirect(
        ExtensionOperation $operation,
        string $type,
        \Throwable $exception,
    ): RedirectResponse {
        $operation->refresh();
        $failedStage = $operation->context['failed_stage'] ?? $operation->current_stage ?? 'unknown';
        $rollback = $operation->rollback_attempted
            ? ($operation->rollback_succeeded ? 'Rollback succeeded.' : 'Rollback failed; inspect the operation log.')
            : 'No replacement had begun.';

        return redirect()
            ->route('gaming-hub-core.admin.extensions.index', ['tab' => 'logs'])
            ->withErrors([
                strtolower($type) => $type.' failed during '.$failedStage.'. '
                    .$this->messages->fromThrowable($exception).' '.$rollback,
            ]);
    }
}
