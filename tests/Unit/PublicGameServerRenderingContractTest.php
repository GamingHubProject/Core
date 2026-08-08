<?php
namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;
use PHPUnit\Framework\TestCase;
final class PublicGameServerRenderingContractTest extends TestCase {
 public function test_game_page_is_server_registry_driven_and_hides_technical_panels():void{$c=file_get_contents(dirname(__DIR__,2).'/src/Controllers/GameController.php');$v=file_get_contents(dirname(__DIR__,2).'/resources/views/games/show.blade.php');$this->assertStringContainsString("->servers()->with('game')->enabled()->public()->ordered()",$c);$this->assertStringNotContainsString('Enabled providers',$v);$this->assertStringNotContainsString('Future capabilities',$v);$this->assertStringNotContainsString('providers',$v);}
 public function test_card_contract_covers_status_message_join_address_and_named_detail_route():void{$v=file_get_contents(dirname(__DIR__,2).'/resources/views/games/show.blade.php');foreach(['$server->status','$server->statusMessage','FILTER_VALIDATE_URL','$server->hostname','$server->displayPort',"route('gaming-hub-core.servers.show'"] as $x)$this->assertStringContainsString($x,$v);}
 public function test_public_server_query_has_stable_order_and_visibility_filters():void{$c=file_get_contents(dirname(__DIR__,2).'/src/Controllers/GameController.php');foreach(['enabled()','public()','ordered()'] as $x)$this->assertStringContainsString($x,$c);}
}
