<?php
namespace Azuriom\Plugin\GamingHubCore\Data;
use Carbon\CarbonImmutable;
final readonly class ServerStatusData {
 public const STATES=['online','offline','maintenance','unknown'];
 public function __construct(public string $state='unknown',public ?string $displayMessage=null,public ?string $version=null,public ?string $serverName=null,public ?int $currentPlayers=null,public ?int $maximumPlayers=null,public ?int $uptimeSeconds=null,public ?CarbonImmutable $observedAt=null,public ?CarbonImmutable $sourceUpdatedAt=null){if(!in_array($state,self::STATES,true))throw new \InvalidArgumentException('Invalid server state.');}
 public function toArray():array{return array_filter(['server.state'=>$this->state,'server.message'=>$this->displayMessage,'server.version'=>$this->version,'server.name'=>$this->serverName,'players.current'=>$this->currentPlayers,'players.maximum'=>$this->maximumPlayers,'uptime.seconds'=>$this->uptimeSeconds,'observed_at'=>$this->observedAt?->toIso8601String(),'source_updated_at'=>$this->sourceUpdatedAt?->toIso8601String()],fn($v)=>$v!==null);}
}
