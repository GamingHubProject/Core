<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;

use Azuriom\Plugin\GamingHubCore\Services\ProviderConfigurationInput;
use PHPUnit\Framework\TestCase;

final class ProviderCreationV066ContractTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function test_extension_formrequest_fields_are_reconciled_only_through_registry_keys(): void
    {
        self::assertSame(
            ['status' => 'online', 'display_message' => 'Ready'],
            ProviderConfigurationInput::reconcile(
                ['manual_identifier' => false],
                ['status' => 'online', 'display_message' => 'Ready', 'manual_identifier' => false],
                ['status', 'display_message'],
            ),
        );
    }

    public function test_validation_errors_are_visible_and_not_suppressed(): void
    {
        $middleware = file_get_contents($this->root.'/src/Http/Middleware/TraceProviderCreation.php');
        $validator = file_get_contents($this->root.'/src/Validation/ProviderConfigurationValidator.php');
        $controller = file_get_contents($this->root.'/src/Controllers/Admin/ProviderController.php');

        self::assertStringContainsString("session()->flash('error'", $middleware);
        self::assertStringContainsString('throw $exception;', $middleware);
        self::assertStringContainsString("'configuration.'.\$key", $validator);
        self::assertStringContainsString('withInput()', $controller);
    }

    public function test_trace_covers_every_creation_boundary_without_logging_configuration_values(): void
    {
        $trace = file_get_contents($this->root.'/src/Services/ProviderCreationTrace.php');
        $lifecycle = file_get_contents($this->root.'/src/Services/ProviderLifecycleManager.php');
        $observer = file_get_contents($this->root.'/src/Observers/ProviderInstanceObserver.php');

        self::assertStringContainsString('request_received', $trace);
        self::assertStringContainsString('validated_payload', $trace);
        self::assertStringContainsString('provider_dto_built', $lifecycle);
        self::assertStringContainsString('repository_saved', $lifecycle);
        self::assertStringContainsString('DB::afterCommit', $observer);
        self::assertStringContainsString('transaction_committed', $observer);
        self::assertStringNotContainsString("'configuration' => \$request->input", $trace);
    }
}
