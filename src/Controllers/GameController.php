<?php

namespace Azuriom\Plugin\GamingHubCore\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\GamingHubCore\Models\Game;
use Azuriom\Plugin\GamingHubCore\Models\Server;
use Azuriom\Plugin\GamingHubCore\Navigation\GameNavigation;
use Azuriom\Plugin\GamingHubCore\Services\PublicGamePresenter;
use Azuriom\Plugin\GamingHubCore\Services\PublicServerPresenter;
use Azuriom\Plugin\GamingHubCore\Settings\GameDirectorySettings;
use Azuriom\Plugin\GamingHubCore\Settings\GamePageSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class GameController extends Controller
{
    public function __construct(
        private readonly PublicGamePresenter $presenter,
        private readonly PublicServerPresenter $serverPresenter,
        private readonly GameDirectorySettings $directorySettings,
        private readonly GamePageSettings $gamePageSettings,
        private readonly GameNavigation $navigation,
    ) {
    }

    public function index(): View
    {
        $games = Game::query()->enabled()->ordered()->get()
            ->map(fn (Game $game) => $this->presenter->directory($game));

        return view()->file(dirname(__DIR__, 2).'/resources/views/games/index.blade.php', [
            'games' => $games,
            'settings' => $this->directorySettings->all(),
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $game = Game::query()->enabled()->where('slug', $slug)->firstOrFail();
        $settings = $this->gamePageSettings->all();
        $servers = $settings['show_servers'] ? $this->publicServersFor($game) : collect();
        $navigation = $settings['show_navigation'] ? $this->navigation->visibleFor($game, $request) : [];

        return view('gaming-hub-core-runtime-v043::games.show-v043', [
            'game' => $this->presenter->detail($game),
            'publicGameServers' => $servers,
            'settings' => $settings,
            'navigation' => $navigation,
            'gamingHubCoreViewVersion' => '0.4.3',
        ]);
    }

    /** @return Collection<int, \Azuriom\Plugin\GamingHubCore\Data\PublicServerData> */
    private function publicServersFor(Game $game): Collection
    {
        return $game->servers()->with('game')->enabled()->public()->ordered()->get()
            ->map(fn (Server $server) => $this->serverPresenter->make($server))
            ->values();
    }

    public function server(Request $request, string $game, string $server): View
    {
        $gameModel = Game::query()
            ->enabled()
            ->where('slug', $game)
            ->firstOrFail();

        // Nested lookup is deliberate: an otherwise valid Server slug belonging
        // to another Game must not be accessible through this Game URL.
        $serverModel = $gameModel->servers()
            ->with('game')
            ->enabled()
            ->public()
            ->where('slug', $server)
            ->firstOrFail();

        $settings = $this->gamePageSettings->all();
        $navigation = $settings['show_navigation']
            ? $this->navigation->visibleFor($gameModel, $request)
            : [];

        return view('gaming-hub-core-runtime-v044::servers.show-v044', [
            'currentGame' => $this->presenter->detail($gameModel),
            'currentServer' => $this->serverPresenter->make($serverModel),
            'gamePageSettings' => $settings,
            'gameNavigation' => $navigation,
            'gamingHubCoreServerViewVersion' => '0.4.4',
        ]);
    }
}
