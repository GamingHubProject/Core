<?php
namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;
use PHPUnit\Framework\TestCase;
final class ExtensionRegistrySecurityContractTest extends TestCase
{
    public function test_security_contracts_are_present(): void
    {
        $root = dirname(__DIR__, 2);
        $guard = file_get_contents($root.'/src/Services/ExtensionUrlGuard.php');
        $archive = file_get_contents($root.'/src/Services/ExtensionArchiveInspector.php');
        $installer = file_get_contents($root.'/src/Services/ExtensionInstaller.php');
        $manifest = str_replace(' ', '', file_get_contents($root.'/src/Services/ExtensionManifestValidator.php'));
        $this->assertStringContainsString('FILTER_FLAG_NO_PRIV_RANGE', $guard);
        $this->assertStringContainsString("str_starts_with(\$name,'/')", $archive);
        $this->assertStringContainsString('Symlinks are not allowed', $archive);
        $this->assertStringContainsString('gaminghub:extension-operation:', $installer);
        $this->assertStringContainsString("\$id==='gaming-hub-core'", $manifest);
    }
}
