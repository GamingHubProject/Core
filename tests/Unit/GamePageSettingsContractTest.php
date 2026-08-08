<?php
namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;
use PHPUnit\Framework\TestCase;
final class GamePageSettingsContractTest extends TestCase {
 public function test_rendering_contract_contains_all_server_page_states():void{$s=file_get_contents(dirname(__DIR__,2).'/src/Settings/GamePageSettings.php');foreach(['compact','standard','hidden','hostname','hostname_and_port','gh-server-grid--','gh-server-card--'] as $v)$this->assertStringContainsString($v,$s);}
 public function test_public_view_renders_distinct_columns_and_density():void{$v=file_get_contents(dirname(__DIR__,2).'/resources/views/games/show.blade.php');foreach(['gh-server-grid--1','gh-server-grid--2','gh-server-grid--3','gh-server-card--compact','gh-server-card--standard'] as $c)$this->assertStringContainsString($c,$v);}
}
