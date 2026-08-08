<?php
namespace Azuriom\Plugin\GamingHubCore\Data; use Carbon\CarbonImmutable;
final readonly class ProviderInstanceData { public function __construct(public int $id,public int $serverId,public int $gameId,public string $providerType,public string $name,public bool $enabled,public int $position,public array $configuration,public ?CarbonImmutable $createdAt,public ?CarbonImmutable $updatedAt){} public function configuration():array{return $this->configuration;} }
