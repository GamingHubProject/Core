<?php

namespace Azuriom\Plugin\GamingHubCore\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\GamingHubCore\Models\ExtensionOperation;
use Azuriom\Plugin\GamingHubCore\Models\InstalledExtension;
use Azuriom\Plugin\GamingHubCore\Services\AzuriomPluginLifecycle;
use Azuriom\Plugin\GamingHubCore\Services\ExtensionDependencyGuard;
use Azuriom\Plugin\GamingHubCore\Services\ExtensionSafeMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class InstalledExtensionController extends Controller
{
    public function __construct(
        private AzuriomPluginLifecycle $lifecycle,
        private ExtensionDependencyGuard $dependencies,
        private ExtensionSafeMessage $messages,
    ) {
    }

    public function show(InstalledExtension $extension): View
    {
        return view('gaming-hub-core::admin.extensions.show', [
            'extension' => $extension,
            'enabled' => $this->lifecycle->isEnabled($extension->extension_id),
            'dependents' => $this->dependencies->dependentsOf($extension->extension_id),
        ]);
    }

    public function enable(Request $request, InstalledExtension $extension): RedirectResponse
    {
        return $this->change($request, $extension, true);
    }

    public function disable(Request $request, InstalledExtension $extension): RedirectResponse
    {
        if ($extension->extension_id === 'gaming-hub-core') {
            return back()->withErrors(['lifecycle' => 'Gaming Hub Core cannot disable itself from this interface.']);
        }

        $dependents = $this->dependencies->dependentsOf($extension->extension_id);
        if ($dependents !== [] && ! $request->boolean('confirm_dependents')) {
            return back()->withErrors([
                'lifecycle' => 'Enabled dependencies may be affected: '.implode(', ', array_column($dependents, 'id')).'.',
            ]);
        }

        return $this->change($request, $extension, false);
    }

    private function change(Request $request, InstalledExtension $extension, bool $enable): RedirectResponse
    {
        $stage = $enable ? 'enabling' : 'disabling';
        $operation = ExtensionOperation::create([
            'operation_uuid' => (string) Str::uuid(),
            'operation' => $enable ? 'enable' : 'disable',
            'extension_id' => $extension->extension_id,
            'version' => $extension->installed_version,
            'source_id' => $extension->source_id,
            'actor_id' => $request->user()->getKey(),
            'started_at' => now(),
            'result' => 'running',
            'current_stage' => $stage,
            'events' => [[
                'at' => now()->toIso8601String(),
                'stage' => $stage,
                'level' => 'info',
                'message' => $enable ? 'Enable operation started.' : 'Disable operation started.',
            ]],
        ]);

        try {
            $successful = $enable
                ? $this->lifecycle->enable($extension->extension_id)
                : $this->lifecycle->disable($extension->extension_id);

            if (! $successful || $this->lifecycle->isEnabled($extension->extension_id) !== $enable) {
                throw new \RuntimeException('Azuriom did not apply the requested plugin lifecycle state.');
            }

            $extension->update([
                'enabled_snapshot' => $enable,
                'last_operation_result' => 'completed',
            ]);
            $operation->complete('Azuriom plugin lifecycle completed.');

            return back()->with('success', $enable ? 'Extension enabled.' : 'Extension disabled.');
        } catch (\Throwable $exception) {
            $operation->fail($this->messages->fromThrowable($exception), 'lifecycle_failed');

            return back()->withErrors([
                'lifecycle' => 'Extension lifecycle action failed: '.$this->messages->fromThrowable($exception),
            ]);
        }
    }
}
