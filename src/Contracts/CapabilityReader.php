<?php
namespace Azuriom\Plugin\GamingHubCore\Contracts;
use Azuriom\Plugin\GamingHubCore\Data\ProviderInstanceData; use Azuriom\Plugin\GamingHubCore\Data\SharedDataResult; use Azuriom\Plugin\GamingHubCore\Models\Server;
interface CapabilityReader { public function read(Server $server, ProviderInstanceData $provider): SharedDataResult; public function cacheTtl(): int; }
