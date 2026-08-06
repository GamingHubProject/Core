<?php

namespace Azuriom\Plugin\GamingHubCore\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\GamingHubCore\Contracts\ProviderTypeRegistry;
use Azuriom\Plugin\GamingHubCore\Exceptions\ProviderLifecycleException;
use Azuriom\Plugin\GamingHubCore\Http\Requests\SaveProviderRequest;
use Azuriom\Plugin\GamingHubCore\Models\Game;
use Azuriom\Plugin\GamingHubCore\Models\ProviderInstance;
use Azuriom\Plugin\GamingHubCore\Models\Server;
use Azuriom\Plugin\GamingHubCore\Services\ProviderLifecycleManager;
use Azuriom\Plugin\GamingHubCore\Services\ProviderCreationTrace;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class ProviderController extends Controller
{
    public function __construct(
        private readonly ProviderTypeRegistry $types,
        private readonly ProviderLifecycleManager $lifecycle,
        private readonly ProviderCreationTrace $trace,
    ) {
    }

    public function index(Game $game, Server $server): View
    {
        $this->server($game, $server);

        try {
            $this->lifecycle->normalize($server);
        } catch (Throwable $exception) {
            report($exception);
        }

        return view('gaming-hub-core::admin.providers.index', [
            'game' => $game,
            'server' => $server,
            'providers' => $server->providers()->ordered()->get(),
            'types' => $this->types->all(),
        ]);
    }

    public function create(Game $game, Server $server): View
    {
        $this->server($game, $server);

        return view('gaming-hub-core::admin.providers.create', [
            'game' => $game,
            'server' => $server,
            'types' => $this->availableTypes(),
            'nextPosition' => $server->providers()->count() + 1,
        ]);
    }

    public function store(SaveProviderRequest $request, Game $game, Server $server): RedirectResponse
    {
        $this->server($game, $server);

        try {
            $data = $request->providerData();
            $this->trace->validated($data, self::class.'::store');
            $this->lifecycle->create($server, $data);
        } catch (Throwable $exception) {
            return $this->failure($exception, 'create_failed');
        }

        return to_route('gaming-hub-core.admin.games.servers.providers.index', [$game, $server])
            ->with('success', trans('gaming-hub-core::admin.provider_messages.created'));
    }

    public function edit(Game $game, Server $server, ProviderInstance $provider): View
    {
        $this->owned($game, $server, $provider);

        return view('gaming-hub-core::admin.providers.edit', [
            'game' => $game,
            'server' => $server,
            'provider' => $provider,
            'types' => $this->availableTypes(),
        ]);
    }

    public function update(
        SaveProviderRequest $request,
        Game $game,
        Server $server,
        ProviderInstance $provider,
    ): RedirectResponse {
        $this->owned($game, $server, $provider);

        try {
            $this->lifecycle->update($server, $provider, $request->providerData());
        } catch (Throwable $exception) {
            return $this->failure($exception, 'update_failed');
        }

        return to_route('gaming-hub-core.admin.games.servers.providers.index', [$game, $server])
            ->with('success', trans('gaming-hub-core::admin.provider_messages.updated'));
    }

    public function toggle(Game $game, Server $server, ProviderInstance $provider): RedirectResponse
    {
        $this->owned($game, $server, $provider);

        try {
            $this->lifecycle->toggle($server, $provider);
        } catch (Throwable $exception) {
            return $this->failure($exception, 'toggle_failed');
        }

        return back()->with('success', trans('gaming-hub-core::admin.provider_messages.updated'));
    }

    public function move(
        Game $game,
        Server $server,
        ProviderInstance $provider,
        string $direction,
    ): RedirectResponse {
        $this->owned($game, $server, $provider);

        try {
            $moved = $this->lifecycle->move($server, $provider, $direction);
        } catch (Throwable $exception) {
            return $this->failure($exception, 'move_failed');
        }

        if (! $moved) {
            return back()->withErrors([
                'provider' => trans('gaming-hub-core::admin.provider_messages.move_boundary'),
            ]);
        }

        return back()->with('success', trans('gaming-hub-core::admin.provider_messages.moved'));
    }

    public function destroy(Game $game, Server $server, ProviderInstance $provider): RedirectResponse
    {
        $this->owned($game, $server, $provider);

        try {
            $this->lifecycle->delete($server, $provider);
        } catch (Throwable $exception) {
            return $this->failure($exception, 'delete_failed');
        }

        return to_route('gaming-hub-core.admin.games.servers.providers.index', [$game, $server])
            ->with('success', trans('gaming-hub-core::admin.provider_messages.deleted'));
    }

    /** @return array<int, \Azuriom\Plugin\GamingHubCore\Data\ProviderType> */
    private function availableTypes(): array
    {
        return array_values(array_filter(
            $this->types->all(),
            static fn ($type): bool => $type->available,
        ));
    }

    private function server(Game $game, Server $server): void
    {
        abort_unless((int) $server->game_id === (int) $game->getKey(), 404);
    }

    private function owned(Game $game, Server $server, ProviderInstance $provider): void
    {
        $this->server($game, $server);
        abort_unless(
            (int) $provider->server_id === (int) $server->getKey()
            && (int) $provider->game_id === (int) $game->getKey(),
            404,
        );
    }

    private function failure(Throwable $exception, string $message): RedirectResponse
    {
        $this->trace->failed($exception, self::class);
        report($exception);

        $safeMessage = $exception instanceof ProviderLifecycleException && filled($exception->getMessage())
            ? $exception->getMessage()
            : trans('gaming-hub-core::admin.provider_messages.'.$message);

        return back()->withInput()->withErrors([
            'provider' => $safeMessage,
        ])->with('error', $safeMessage);
    }
}
