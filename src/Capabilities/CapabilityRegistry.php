<?php

namespace GamingHub\Core\Capabilities;

use GamingHub\Core\Models\Capability;
use GamingHub\Core\Models\Game;
use GamingHub\Core\Models\Provider;
use GamingHub\Core\Models\Server;
use GamingHub\Core\Normalizers\NormalizerRegistry;
use Illuminate\Database\Eloquent\Collection;

/**
 * The catalogue of capability ids the platform knows about, plus which of
 * them a given Game can actually answer right now. Deliberately reasons
 * only from Core's own tables (Provider/Server/Game) and Core's own
 * NormalizerRegistry — never touches a Platform model (ConnectorInstance,
 * InstalledPackage) directly, the same boundary every other Core class
 * documents ("Core must never know about connectors directly").
 * NormalizerRegistry itself is a Core class even though Platform is what
 * populates it at boot, so depending on it here doesn't cross that line.
 */
class CapabilityRegistry
{
    public function __construct(protected NormalizerRegistry $normalizers) {}

    /** @return Collection<int, Capability> */
    public function all(): Collection
    {
        return Capability::query()->orderBy('name')->get();
    }

    public function byId(string $id): ?Capability
    {
        return Capability::find($id);
    }

    /**
     * Capabilities at least one of this Game's Servers can currently
     * answer for real: a manual Provider row declaring the capability
     * directly, or a connector Provider row whose normalizer resolves to
     * it. Game Integration extensions declaring capabilities of their own
     * isn't modeled yet (InstalledPackage has no capabilities field) —
     * once it is, this is the one place that union gets added.
     *
     * @return Collection<int, Capability>
     */
    public function forGame(Game $game): Collection
    {
        $serverIds = Server::where('game_id', $game->id)->pluck('id');

        $ids = Provider::query()
            ->whereIn('server_id', $serverIds)
            ->get()
            ->map(fn (Provider $provider) => $this->capabilityIdFor($provider))
            ->filter()
            ->unique()
            ->values();

        return Capability::whereIn('id', $ids)->orderBy('name')->get();
    }

    protected function capabilityIdFor(Provider $provider): ?string
    {
        if ($provider->type === 'manual') {
            return $provider->config['capability'] ?? null;
        }

        $normalizerId = $provider->config['normalizer'] ?? null;

        if (! $normalizerId || ! $this->normalizers->has($normalizerId)) {
            return null;
        }

        return $this->normalizers->get($normalizerId)->capability();
    }
}
