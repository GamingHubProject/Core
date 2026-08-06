<?php
namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;
use Azuriom\Plugin\GamingHubCore\Data\ProviderConfigurationField;
use Azuriom\Plugin\GamingHubCore\Data\ProviderType;
use Azuriom\Plugin\GamingHubCore\Validation\ProviderConfigurationValidator;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;
final class ProviderConfigurationValidatorTest extends TestCase {
 private function type(): ProviderType { return new ProviderType('manual','Manual','Local.','core','Core',['server-status'],[new ProviderConfigurationField('status','Status','select',true,['online','offline','maintenance','unknown']),new ProviderConfigurationField('display_message','Message','string',false,[],500)]); }
 public function test_valid_manual_configuration(): void { self::assertSame(['status'=>'online','display_message'=>'Ready'],(new ProviderConfigurationValidator())->validate($this->type(),['status'=>'online','display_message'=>'Ready'])); }
 public function test_invalid_status_is_rejected(): void { $this->expectException(ValidationException::class); (new ProviderConfigurationValidator())->validate($this->type(),['status'=>'broken']); }
 public function test_unknown_transient_key_is_discarded(): void { self::assertSame(['status'=>'online'],(new ProviderConfigurationValidator())->validate($this->type(),['status'=>'online','secret'=>'x'])); }
}
