<?php

namespace Azuriom\Plugin\GamingHubCore\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\GamingHubCore\Models\ExtensionOperation;
use Azuriom\Plugin\GamingHubCore\Models\InstalledExtension;
use Azuriom\Plugin\GamingHubCore\Services\AzuriomPluginLifecycle;
use Azuriom\Plugin\GamingHubCore\Services\ExtensionDependencyGuard;
use Azuriom\Plugin\GamingHubCore\Services\ExtensionSafeMessage;
use Azuriom\Plugin\GamingHubCore\Services\ExtensionUninstaller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class ExtensionUninstallController extends Controller
{
    public function __construct(
        private ExtensionUninstaller $uninstaller,
        private ExtensionDependencyGuard $dependencies,
        private AzuriomPluginLifecycle $lifecycle,
        private ExtensionSafeMessage $messages,
    ) {
    }

    public function confirm(InstalledExtension $extension): View|RedirectResponse
    {
        if ($extension->extension_id === 'gaming-hub-core') {
            return redirect()->route('gaming-hub-core.admin.extensions.index')
                ->withErrors(['uninstall' => 'Gaming Hub Core cannot uninstall itself.']);
        }

        return view('gaming-hub-core::admin.extensions.uninstall', [
            'extension' => $extension,
            'enabled' => $this->lifecycle->isEnabled($extension->extension_id),
            'dependents' => $this->dependencies->dependentsOf($extension->extension_id),
        ]);
    }

    public function destroy(Request $request, InstalledExtension $extension): RedirectResponse
    {
        $request->validate([
            'confirmation' => ['required', 'string', 'in:'.$extension->extension_id],
            'retain_data' => ['required', 'accepted'],
        ], [
            'confirmation.in' => 'Type the exact extension ID to confirm uninstall.',
            'retain_data.accepted' => 'This milestone only supports file removal with extension data retained.',
        ]);

        $operation = ExtensionOperation::create([
            'operation_uuid' => (string) Str::uuid(),
            'operation' => 'uninstall',
            'extension_id' => $extension->extension_id,
            'version' => $extension->installed_version,
            'source_id' => $extension->source_id,
            'actor_id' => $request->user()->getKey(),
            'started_at' => now(),
            'result' => 'running',
            'current_stage' => 'resolving',
            'events' => [[
                'at' => now()->toIso8601String(),
                'stage' => 'resolving',
                'level' => 'info',
                'message' => 'Uninstall operation queued with data retention enabled.',
            ]],
        ]);

        try {
            $this->uninstaller->uninstall($extension, $operation);

            return redirect()
                ->route('gaming-hub-core.admin.extensions.index', ['tab' => 'installed'])
                ->with('success', 'Extension files removed. Extension database data was retained.');
        } catch (\Throwable $exception) {
            if ($operation->result === 'running' && $operation->finished_at === null) {
                $operation->fail($this->messages->fromThrowable($exception), 'uninstall_failed');
            }
            $operation->refresh();
            $stage = $operation->context['failed_stage'] ?? $operation->current_stage ?? 'unknown';
            $rollback = $operation->rollback_attempted
                ? ($operation->rollback_succeeded ? 'Previous files and enabled state were restored.' : 'Automatic restoration failed.')
                : 'No files were removed.';

            return redirect()
                ->route('gaming-hub-core.admin.extensions.index', ['tab' => 'logs'])
                ->withErrors([
                    'uninstall' => 'Uninstall failed during '.$stage.'. '
                        .$this->messages->fromThrowable($exception).' '.$rollback,
                ]);
        }
    }
}
