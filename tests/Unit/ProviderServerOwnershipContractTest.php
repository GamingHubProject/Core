<?php
namespace Azuriom\Plugin\GamingHubCore\Tests\Unit; use PHPUnit\Framework\TestCase;
final class ProviderServerOwnershipContractTest extends TestCase { public function test_provider_services_are_server_scoped():void{$root=dirname(__DIR__,2);$contract=file_get_contents($root.'/src/Contracts/ProviderInstances.php');$resolver=file_get_contents($root.'/src/Contracts/CapabilityResolver.php');$this->assertStringContainsString('forServer',$contract);$this->assertStringNotContainsString('forGame',$contract);$this->assertStringContainsString('Server $server',$resolver);} }
