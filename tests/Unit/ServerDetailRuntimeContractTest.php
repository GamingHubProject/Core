<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ServerDetailRuntimeContractTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function test_controller_uses_explicit_nested_contract(): void
    {
        $source = file_get_contents($this->root.'/src/Controllers/GameController.php');

        self::assertStringContainsString('$gameModel->servers()', $source);
        self::assertStringContainsString("'currentGame' =>", $source);
        self::assertStringContainsString("'currentServer' =>", $source);
        self::assertStringContainsString('gaming-hub-core-runtime-v044::servers.show-v044', $source);
        self::assertStringNotContainsString("'game' => $this->presenter->detail($gameModel)", $source);
        self::assertStringNotContainsString("'server' => $this->serverPresenter->make($serverModel)", $source);
    }

    public function test_view_has_no_nullable_object_name_access(): void
    {
        $view = file_get_contents($this->root.'/resources/views/runtime/servers/show-v044.blade.php');

        self::assertStringContainsString('$currentGame->name', $view);
        self::assertStringContainsString('$currentServer->name', $view);
        self::assertStringNotContainsString('$server->game', $view);
        self::assertStringNotContainsString('$provider->name', $view);
        self::assertStringNotContainsString('$status->name', $view);
        self::assertStringNotContainsString('$navigationItem->name', $view);
    }

    public function test_missing_provider_and_optional_metadata_are_safe(): void
    {
        $presenter = file_get_contents($this->root.'/src/Services/PublicServerPresenter.php');
        $view = file_get_contents($this->root.'/resources/views/runtime/servers/show-v044.blade.php');

        self::assertStringContainsString("return ['unknown', null]", $presenter);
        self::assertStringContainsString('@if($currentServer->iconUrl)', $view);
        self::assertStringContainsString('@if($currentServer->longDescription)', $view);
        self::assertStringContainsString('$currentServer->hostname', $view);
        self::assertStringContainsString('FILTER_VALIDATE_URL', $view);
    }

    public function test_obsolete_capability_placeholders_are_absent(): void
    {
        $view = file_get_contents($this->root.'/resources/views/runtime/servers/show-v044.blade.php');

        self::assertStringNotContainsString("['players','console','map','guilds','trading']", $view);
        self::assertStringNotContainsString("public.capabilities", $view);
    }
}
