<?php
namespace Azuriom\Plugin\GamingHubCore\Contracts;
use Azuriom\Plugin\GamingHubCore\Data\SharedDataResult; use Azuriom\Plugin\GamingHubCore\Models\Server;
interface SharedDataGateway { public function read(Server $server,string $capability):SharedDataResult; public function readMany(Server $server,array $capabilities):array; public function supports(Server $server,string $capability):bool; public function publicRead(Server $server,string $capability):SharedDataResult; }
