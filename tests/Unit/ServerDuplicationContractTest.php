<?php
namespace Azuriom\Plugin\GamingHubCore\Tests\Unit; use PHPUnit\Framework\TestCase;
final class ServerDuplicationContractTest extends TestCase { public function test_duplication_copies_metadata_not_providers():void{$c=file_get_contents(dirname(__DIR__,2).'/src/Controllers/Admin/ServerController.php');$this->assertStringContainsString("replicate(['uuid'])",$c);$this->assertStringNotContainsString('providers()->create',$c);} }
