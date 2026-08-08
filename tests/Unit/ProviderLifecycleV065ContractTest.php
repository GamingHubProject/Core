<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;

use Azuriom\Plugin\GamingHubCore\Services\ProviderPositionSequence;
use PHPUnit\Framework\TestCase;

final class ProviderLifecycleV065ContractTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function test_sequence_is_one_based_and_legacy_zero_appends(): void
    {
        self::assertSame([10, 20], ProviderPositionSequence::reposition([10, 20], 20, 0));
        self::assertSame([20, 10], ProviderPositionSequence::reposition([10, 20], 20, 1));
        self::assertSame([20, 10], ProviderPositionSequence::move([10, 20], 10, 'down'));
    }

    public function test_lifecycle_is_server_scoped_transactional_locked_and_collision_safe(): void
    {
        $source = file_get_contents($this->root.'/src/Services/ProviderLifecycleManager.php');

        self::assertStringContainsString("->where('server_id', \$server->getKey())", $source);
        self::assertStringContainsString('lockForUpdate()', $source);
        self::assertStringContainsString('ProviderPositionSequence::move', $source);
        self::assertStringContainsString("'position' => -(\$offset + 1)", $source);
        self::assertStringContainsString('$position = $offset + 1', $source);
    }

    public function test_extension_owned_crud_inherits_core_position_invariants(): void
    {
        $observer = file_get_contents($this->root.'/src/Observers/ProviderInstanceObserver.php');
        $provider = file_get_contents($this->root.'/src/Providers/GamingHubCoreServiceProvider.php');

        self::assertStringContainsString('public function creating(ProviderInstance $provider)', $observer);
        self::assertStringContainsString("'position' => ((int) ProviderInstance::query()", $observer);
        self::assertStringContainsString("foreach (['game_id', 'server_id', 'position']", $observer);
        self::assertStringContainsString('ProviderInstance::observe(ProviderInstanceObserver::class)', $provider);
    }

    public function test_configuration_is_registry_whitelisted(): void
    {
        $validator = file_get_contents($this->root.'/src/Validation/ProviderConfigurationValidator.php');

        self::assertStringContainsString('ProviderConfigurationInput::reconcile($configuration, $raw, $allowed)', $validator);
        self::assertStringNotContainsString('Unknown configuration keys', $validator);
    }

    public function test_database_enforces_unique_server_priority(): void
    {
        $migration = file_get_contents($this->root.'/database/migrations/2026_08_06_010000_enforce_gaminghub_provider_positions.php');

        self::assertStringContainsString("->unique(['server_id', 'position']", $migration);
        self::assertStringContainsString("'position' => \$offset + 1", $migration);
    }
}
