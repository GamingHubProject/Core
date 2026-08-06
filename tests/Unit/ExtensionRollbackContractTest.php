<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ExtensionRollbackContractTest extends TestCase
{
    public function test_update_rollback_restores_files_metadata_and_enabled_state(): void
    {
        $source = file_get_contents(__DIR__.'/../../src/Services/ExtensionInstaller.php');

        self::assertStringContainsString("transition('rolling_back'", $source);
        self::assertStringContainsString('$this->paths->deleteExtension($extensionId)', $source);
        self::assertStringContainsString('rename($previousSwap, (string) $live)', $source);
        self::assertStringContainsString('$this->paths->copyDirectory($backupPath, (string) $live)', $source);
        self::assertStringContainsString('$restored->forceFill($metadata)', $source);
        self::assertStringContainsString('$this->lifecycle->enable($extensionId)', $source);
        self::assertStringContainsString("'rollback_attempted'", $source);
        self::assertStringContainsString('$this->cleanupQuietly($work)', $source);
    }
}
