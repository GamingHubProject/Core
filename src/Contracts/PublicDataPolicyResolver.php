<?php
namespace Azuriom\Plugin\GamingHubCore\Contracts;
use Azuriom\Plugin\GamingHubCore\Data\PublicStatisticPolicy; use Azuriom\Plugin\GamingHubCore\Models\Server;
interface PublicDataPolicyResolver { public function effective(?Server $server,string $key):PublicStatisticPolicy; public function filter(Server $server,array $data,?string $sourceLabel=null):array; }
