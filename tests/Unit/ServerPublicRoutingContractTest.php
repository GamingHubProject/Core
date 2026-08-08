<?php
namespace Azuriom\Plugin\GamingHubCore\Tests\Unit; use PHPUnit\Framework\TestCase;
final class ServerPublicRoutingContractTest extends TestCase { public function test_public_contract():void{$root=dirname(__DIR__,2);$routes=file_get_contents($root.'/routes/web.php');$controller=file_get_contents($root.'/src/Controllers/GameController.php');$this->assertStringContainsString("/games/{game}/{server}",$routes);$this->assertStringContainsString("->enabled()->public()",$controller);$this->assertStringContainsString("firstOrFail()",$controller);} }
