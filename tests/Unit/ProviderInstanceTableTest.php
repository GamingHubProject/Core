<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;

use Azuriom\Plugin\GamingHubCore\Models\Game;
use Azuriom\Plugin\GamingHubCore\Models\ProviderInstance;
use PHPUnit\Framework\TestCase;

final class ProviderInstanceTableTest extends TestCase
{
    public function test_provider_instance_uses_migrated_table(): void
    {
        self::assertSame('gaminghub_provider_instances', (new ProviderInstance())->getTable());
    }

    public function test_game_provider_relationship_uses_provider_instance_model(): void
    {
        $relationship = (new Game())->providers();

        self::assertInstanceOf(ProviderInstance::class, $relationship->getRelated());
        self::assertSame('gaminghub_provider_instances', $relationship->getRelated()->getTable());
    }
}
