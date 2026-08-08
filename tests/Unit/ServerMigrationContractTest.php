<?php
namespace Azuriom\Plugin\GamingHubCore\Tests\Unit; use PHPUnit\Framework\TestCase;
final class ServerMigrationContractTest extends TestCase { public function test_existing_assignments_are_migrated():void{$m=file_get_contents(dirname(__DIR__,2).'/database/migrations/2026_08_05_000000_create_gaminghub_servers_table.php');$this->assertStringContainsString("'Default Server'",$m);$this->assertStringContainsString("whereNull('server_id')->update",$m);$this->assertStringContainsString("cascadeOnDelete",$m);} }
