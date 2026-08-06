<?php
namespace Azuriom\Plugin\GamingHubCore\Contracts; use Azuriom\Plugin\GamingHubCore\Data\ProviderInstanceData; use Azuriom\Plugin\GamingHubCore\Models\Server;
interface CapabilityResolver { public function resolve(Server $server,string $capability): ?ProviderInstanceData; }
