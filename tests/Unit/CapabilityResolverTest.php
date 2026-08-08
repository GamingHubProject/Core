<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;

use Azuriom\Plugin\GamingHubCore\Contracts\ProviderInstances;
use Azuriom\Plugin\GamingHubCore\Data\ProviderInstanceData;
use Azuriom\Plugin\GamingHubCore\Models\Server;
use Azuriom\Plugin\GamingHubCore\Services\DefaultCapabilityResolver;
use PHPUnit\Framework\TestCase;

final class CapabilityResolverTest extends TestCase
{
    public function test_it_returns_highest_priority_enabled_provider(): void
    {
        $first = $this->provider(2, true, 1, 'pelican');
        $second = $this->provider(1, true, 2, 'manual');
        $server = new Server();
        $server->setAttribute('id', 9);

        self::assertSame(
            $first,
            (new DefaultCapabilityResolver($this->providers([$first, $second])))
                ->resolve($server, 'server-status'),
        );
    }

    public function test_it_returns_null_when_no_enabled_provider_supports_capability(): void
    {
        $server = new Server();
        $server->setAttribute('id', 9);

        self::assertNull(
            (new DefaultCapabilityResolver($this->providers([])))
                ->resolve($server, 'server-status'),
        );
    }

    private function provider(int $id, bool $enabled, int $position, string $type): ProviderInstanceData
    {
        return new ProviderInstanceData(
            id: $id,
            serverId: 9,
            gameId: 4,
            providerType: $type,
            name: ucfirst($type),
            enabled: $enabled,
            position: $position,
            configuration: [],
            createdAt: null,
            updatedAt: null,
        );
    }

    /** @param list<ProviderInstanceData> $resolved */
    private function providers(array $resolved): ProviderInstances
    {
        return new class($resolved) implements ProviderInstances {
            public function __construct(private array $resolved)
            {
            }

            public function forServer(int $serverId, bool $includeDisabled = true): array
            {
                return $this->resolved;
            }

            public function enabledForServerByCapability(int $serverId, string $capability): array
            {
                return $this->resolved;
            }

            public function findForServer(int $serverId, int $providerId): ?ProviderInstanceData
            {
                return null;
            }

            public function validatedConfiguration(int $serverId, int $providerId): array
            {
                return [];
            }
        };
    }
}
