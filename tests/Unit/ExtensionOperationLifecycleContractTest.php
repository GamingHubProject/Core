<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ExtensionOperationLifecycleContractTest extends TestCase
{
    public function test_installer_uses_terminal_update_stages_and_finishes_failures(): void
    {
        $installer = file_get_contents(__DIR__.'/../../src/Services/ExtensionInstaller.php');
        $controller = file_get_contents(__DIR__.'/../../src/Controllers/Admin/ExtensionInstallController.php');
        $operation = file_get_contents(__DIR__.'/../../src/Models/ExtensionOperation.php');

        foreach (['downloading', 'validating', 'staging', 'backing_up', 'disabling', 'replacing', 'migrating', 'enabling', 'cleaning', 'rolling_back'] as $stage) {
            self::assertStringContainsString("transition('{$stage}'", $installer);
        }

        self::assertStringContainsString("transition('resolving'", $controller);
        self::assertStringContainsString('->complete(', $installer);
        self::assertStringContainsString('->fail(', $installer);
        self::assertStringContainsString("'rolled_back'", $installer);
        self::assertStringContainsString("'rollback_failed'", $installer);
        self::assertStringContainsString("'current_stage' => $terminalStage", $operation);
        self::assertStringContainsString('withErrors([', $controller);
    }
}
