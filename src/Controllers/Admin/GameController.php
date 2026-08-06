<?php

namespace Azuriom\Plugin\GamingHubCore\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\GamingHubCore\Events\GameCreated;
use Azuriom\Plugin\GamingHubCore\Events\GameDeleted;
use Azuriom\Plugin\GamingHubCore\Events\GameDisabled;
use Azuriom\Plugin\GamingHubCore\Events\GameEnabled;
use Azuriom\Plugin\GamingHubCore\Events\GameUpdated;
use Azuriom\Plugin\GamingHubCore\Http\Requests\SaveGameRequest;
use Azuriom\Plugin\GamingHubCore\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(): View
    {
        return view('gaming-hub-core::admin.games.index', [
            'games' => Game::query()->ordered()->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('gaming-hub-core::admin.games.create');
    }

    public function store(SaveGameRequest $request): RedirectResponse
    {
        $game = Game::create($request->validated());
        event(new GameCreated($game));

        return to_route('gaming-hub-core.admin.games.index')->with('success', trans('gaming-hub-core::admin.messages.created'));
    }

    public function edit(Game $game): View
    {
        return view('gaming-hub-core::admin.games.edit', compact('game'));
    }

    public function update(SaveGameRequest $request, Game $game): RedirectResponse
    {
        $wasEnabled = $game->enabled;
        $game->update($request->validated());
        event(new GameUpdated($game));
        $this->dispatchEnabledStateEvent($game, $wasEnabled);

        return to_route('gaming-hub-core.admin.games.index')->with('success', trans('gaming-hub-core::admin.messages.updated'));
    }

    public function toggle(Game $game): RedirectResponse
    {
        $game->update(['enabled' => ! $game->enabled]);
        event($game->enabled ? new GameEnabled($game) : new GameDisabled($game));

        return back()->with('success', trans('gaming-hub-core::admin.messages.updated'));
    }

    public function move(Game $game, string $direction): RedirectResponse
    {
        DB::transaction(function () use ($game, $direction): void {
            $games = Game::query()->ordered()->lockForUpdate()->get();
            $index = $games->search(fn (Game $candidate): bool => $candidate->is($game));

            if ($index === false) {
                return;
            }

            $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;
            if (! $games->has($targetIndex)) {
                return;
            }

            $items = $games->all();
            [$items[$index], $items[$targetIndex]] = [$items[$targetIndex], $items[$index]];

            foreach ($items as $position => $item) {
                if ($item->sort_order === $position) {
                    continue;
                }

                $item->update(['sort_order' => $position]);
                event(new GameUpdated($item->refresh()));
            }
        });

        return back();
    }

    public function destroy(Game $game): RedirectResponse
    {
        $snapshot = clone $game;
        $game->delete();
        event(new GameDeleted($snapshot));

        return to_route('gaming-hub-core.admin.games.index')->with('success', trans('gaming-hub-core::admin.messages.deleted'));
    }

    private function dispatchEnabledStateEvent(Game $game, bool $wasEnabled): void
    {
        if ($wasEnabled === $game->enabled) return;
        event($game->enabled ? new GameEnabled($game) : new GameDisabled($game));
    }
}
