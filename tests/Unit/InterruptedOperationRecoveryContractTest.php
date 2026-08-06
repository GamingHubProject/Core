<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class InterruptedOperationRecoveryContractTest extends TestCase
{
    public function test_stale_running_operations_are_closed_when_admin_opens_extension_manager(): void
    {
        $source = file_get_contents(__DIR__.'/../../src/Controllers/Admin/ExtensionController.php');

        self::assertStringContainsString("where('result', 'running')", $source);
        self::assertStringContainsString('subMinutes(10)', $source);
        self::assertStringContainsString("'interrupted'", $source);
    }
}
