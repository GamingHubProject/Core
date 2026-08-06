<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ExtensionLifecycleV063ContractTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function test_update_is_dedicated_and_preserves_state_with_rollback(): void
    {
        $source = file_get_contents($this->root.'/src/Services/ExtensionInstaller.php');

        self::assertStringContainsString('public function update(', $source);
        self::assertStringContainsString('InstalledExtension $installed', $source);
        self::assertStringNotContainsString("operate('update'", $source);
        self::assertStringContainsString('$this->versions->assertNewer', $source);
        self::assertStringContainsString('$manifest->id !== $extensionId', $source);
        self::assertStringContainsString('$restored->forceFill($metadata)', $source);
        self::assertStringContainsString('Previous enabled state could not be restored.', $source);
    }

    public function test_update_stages_are_complete(): void
    {
        $source = file_get_contents($this->root.'/src/Services/ExtensionInstaller.php');

        foreach (['downloading', 'validating', 'staging', 'backing_up', 'disabling', 'replacing', 'migrating', 'enabling', 'cleaning', 'rolling_back'] as $stage) {
            self::assertStringContainsString("transition('{$stage}'", $source);
        }
    }

    public function test_uninstall_is_safe_file_only_and_dependency_guarded(): void
    {
        $source = file_get_contents($this->root.'/src/Services/ExtensionUninstaller.php');
        $guard = file_get_contents($this->root.'/src/Services/ExtensionDependencyGuard.php');

        self::assertStringContainsString('$this->dependencies->assertUninstallAllowed', $source);
        self::assertStringContainsString("'data_retained' => true", $source);
        self::assertStringContainsString('$this->paths->destination($extensionId)', $source);
        self::assertStringContainsString("if (\$extensionId === 'gaming-hub-core')", $guard);
        self::assertStringNotContainsString('migrate:rollback', $source);
        self::assertStringNotContainsString('drop table', strtolower($source));
    }

    public function test_admin_ui_never_offers_install_for_known_installed_state(): void
    {
        $view = file_get_contents($this->root.'/resources/views/admin/extensions/index.blade.php');

        foreach (['Update available', 'Up to date', 'Incompatible', 'Installed', 'Uninstall'] as $label) {
            self::assertStringContainsString($label, $view);
        }
        self::assertStringContainsString("@elseif ($state === 'update')", $view);
        self::assertStringContainsString("@elseif ($state === 'up_to_date')", $view);
    }

    public function test_lifecycle_uses_correct_azuriom_argument(): void
    {
        $source = file_get_contents($this->root.'/src/Services/AzuriomPluginLifecycle.php');

        self::assertStringContainsString('PluginManager', $source);
        self::assertStringContainsString("['id' => \$extensionId]", $source);
        self::assertStringNotContainsString("['plugin' => \$id]", $source);
    }
}
