<?php
namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;
use Azuriom\Plugin\GamingHubCore\Exceptions\DuplicateGameNavigationItem; use Azuriom\Plugin\GamingHubCore\Navigation\GameNavigation; use Azuriom\Plugin\GamingHubCore\Navigation\GameNavigationItem; use PHPUnit\Framework\TestCase;
final class GameNavigationContractTest extends TestCase {
 public function test_duplicate_ids_are_rejected():void{$n=new GameNavigation();$i=new GameNavigationItem('wiki','Wiki',static fn()=>'/wiki');$n->register($i);$this->expectException(DuplicateGameNavigationItem::class);$n->register($i);}
 public function test_service_documents_visibility_active_and_deterministic_order():void{$s=file_get_contents(dirname(__DIR__,2).'/src/Navigation/GameNavigation.php');foreach(['visible','active','usort','order'] as $x)$this->assertStringContainsString($x,$s);}
 public function test_built_ins_are_registered():void{$s=file_get_contents(dirname(__DIR__,2).'/src/Providers/GamingHubCoreServiceProvider.php');$this->assertStringContainsString("id: 'overview'",$s);$this->assertStringContainsString("id: 'servers'",$s);}
}
