<?php
namespace Azuriom\Plugin\GamingHubCore\Contracts;

use Azuriom\Plugin\GamingHubCore\Data\ProviderType;

interface ProviderTypeRegistry
{
    public function register(ProviderType $type): void;
    public function get(string $id): ProviderType;
    public function find(string $id): ?ProviderType;
    /** @return array<int, ProviderType> */
    public function all(): array;
}
