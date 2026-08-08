<?php
namespace Azuriom\Plugin\GamingHubCore\Data;
use Carbon\CarbonImmutable;
final readonly class SharedDataResult {
 public const STATUSES=['available','unavailable','unsupported','stale','failed'];
 public function __construct(public string $capability,public int $serverId,public string $status,public array $data=[],public ?CarbonImmutable $observedAt=null,public ?CarbonImmutable $sourceUpdatedAt=null,public ?string $providerTypeId=null,public ?int $providerInstanceId=null,public ?string $errorCode=null,public ?string $diagnostic=null,public ?string $sourceLabel=null){if(!in_array($status,self::STATUSES,true))throw new \InvalidArgumentException('Invalid shared data status.');}
 public static function unsupported(string $cap,int $sid):self{return new self($cap,$sid,'unsupported',errorCode:'unsupported');}
 public static function unavailable(string $cap,int $sid,?string $code='unavailable'):self{return new self($cap,$sid,'unavailable',errorCode:$code);}
 public static function failed(string $cap,int $sid,string $code='unknown_error',?string $diag=null):self{return new self($cap,$sid,'failed',errorCode:$code,diagnostic:$diag);}
 public function publicArray():array{return ['capability'=>$this->capability,'server_id'=>$this->serverId,'status'=>$this->status,'data'=>$this->data,'observed_at'=>$this->observedAt?->toIso8601String(),'source_updated_at'=>$this->sourceUpdatedAt?->toIso8601String(),'source'=>$this->sourceLabel,'error_code'=>$this->errorCode];}
 public function internalArray():array{return $this->publicArray()+['provider_type_id'=>$this->providerTypeId,'provider_instance_id'=>$this->providerInstanceId,'diagnostic'=>$this->diagnostic];}
}
