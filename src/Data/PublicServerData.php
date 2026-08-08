<?php
namespace Azuriom\Plugin\GamingHubCore\Data;
final readonly class PublicServerData { public function __construct(public int $id,public string $gameSlug,public string $slug,public string $name,public ?string $shortDescription,public ?string $longDescription,public ?string $iconUrl,public ?string $bannerUrl,public int $position,public ?string $status,public ?string $statusMessage,public ?string $hostname,public ?int $displayPort,public ?string $joinUrl,public array $providers=[],public ?int $currentPlayers=null,public ?int $maximumPlayers=null,public ?string $sourceLabel=null){} }
