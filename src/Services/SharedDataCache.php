<?php

namespace Azuriom\Plugin\GamingHubCore\Services;

use Azuriom\Plugin\GamingHubCore\Data\ProviderInstanceData;
use Illuminate\Contracts\Cache\Repository;
use Throwable;

final class SharedDataCache
{
    public function __construct(private readonly Repository $cache)
    {
    }

    public function key(int $serverId, ProviderInstanceData $provider, string $capability): string
    {
        return implode(':', [
            'gaminghub',
            'data',
            $serverId,
            $provider->id,
            $capability,
            $provider->updatedAt?->getTimestamp() ?? 0,
            $this->generation($provider->id),
        ]);
    }

    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        if ($ttl <= 0) {
            return $callback();
        }

        try {
            return $this->cache->remember($key, $ttl, $callback);
        } catch (Throwable) {
            return $callback();
        }
    }

    public function invalidateProvider(int $providerId): void
    {
        if ($providerId < 1) {
            return;
        }

        try {
            $key = $this->generationKey($providerId);
            $this->cache->forever($key, $this->generation($providerId) + 1);
        } catch (Throwable) {
            // Cache availability must never make provider lifecycle operations fail.
        }
    }

    private function generation(int $providerId): int
    {
        try {
            return max(0, (int) $this->cache->get($this->generationKey($providerId), 0));
        } catch (Throwable) {
            return 0;
        }
    }

    private function generationKey(int $providerId): string
    {
        return 'gaminghub:data-generation:'.$providerId;
    }
}
