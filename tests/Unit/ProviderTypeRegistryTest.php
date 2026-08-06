<?php
namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;
use Azuriom\Plugin\GamingHubCore\Data\ProviderConfigurationField;
use Azuriom\Plugin\GamingHubCore\Data\ProviderType;
use Azuriom\Plugin\GamingHubCore\Exceptions\DuplicateProviderType;
use Azuriom\Plugin\GamingHubCore\Exceptions\InvalidProviderType;
use Azuriom\Plugin\GamingHubCore\Exceptions\UnknownProviderType;
use Azuriom\Plugin\GamingHubCore\Services\InMemoryProviderTypeRegistry;
use PHPUnit\Framework\TestCase;
final class ProviderTypeRegistryTest extends TestCase {
 private function manual(): ProviderType { return new ProviderType('manual','Manual','Local status.','gaming-hub-core','Gaming Hub Core',['server-status'],[new ProviderConfigurationField('status','Status','select',true,['online','offline','maintenance','unknown'])]); }
 public function test_registration_and_manual_metadata(): void { $r=new InMemoryProviderTypeRegistry(); $r->register($this->manual()); self::assertSame('manual',$r->get('manual')->id); self::assertSame(['server-status'],$r->all()[0]->capabilities); }
 public function test_duplicate_is_rejected(): void { $this->expectException(DuplicateProviderType::class); $r=new InMemoryProviderTypeRegistry(); $r->register($this->manual()); $r->register($this->manual()); }
 public function test_invalid_capability_is_rejected(): void { $this->expectException(InvalidProviderType::class); (new InMemoryProviderTypeRegistry())->register(new ProviderType('bad','Bad','Bad provider.','plugin','Plugin',['imaginary'],[])); }
 public function test_unknown_is_distinct(): void { $this->expectException(UnknownProviderType::class); (new InMemoryProviderTypeRegistry())->get('missing'); }
 public function test_returned_metadata_is_detached(): void { $r=new InMemoryProviderTypeRegistry(); $r->register($this->manual()); self::assertNotSame($r->get('manual'),$r->get('manual')); self::assertNotSame($r->get('manual')->fields[0],$r->get('manual')->fields[0]); }
}
