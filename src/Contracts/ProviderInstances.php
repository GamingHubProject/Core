<?php
namespace Azuriom\Plugin\GamingHubCore\Contracts; use Azuriom\Plugin\GamingHubCore\Data\ProviderInstanceData;
interface ProviderInstances { public function forServer(int $serverId,bool $includeDisabled=true): array; public function enabledForServerByCapability(int $serverId,string $capability): array; public function findForServer(int $serverId,int $providerId): ?ProviderInstanceData; public function validatedConfiguration(int $serverId,int $providerId): array; }
