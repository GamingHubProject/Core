<?php

namespace Azuriom\Plugin\GamingHubCore\Data;

final readonly class PublicGameData
{
    /** @param list<ProviderInstanceData> $providers */
    public function __construct(
        public int $id,
        public string $slug,
        public string $name,
        public ?string $shortDescription,
        public ?string $longDescription,
        public ?string $iconUrl,
        public ?string $bannerUrl,
        public int $position,
        public string $status,
        public ?string $statusMessage,
        public array $providers = [],
    ) {}
}
