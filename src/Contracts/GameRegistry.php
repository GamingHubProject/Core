<?php

namespace Azuriom\Plugin\GamingHubCore\Contracts;

use Azuriom\Plugin\GamingHubCore\Models\Game;
use Illuminate\Support\Collection;

/** Public game-registry contract for Gaming Hub plugins. */
interface GameRegistry
{
    /** @return Collection<int, Game> */
    public function all(bool $includeDisabled = false): Collection;
    public function findById(int $id, bool $includeDisabled = false): ?Game;
    public function findBySlug(string $slug, bool $includeDisabled = false): ?Game;
    public function findByUuid(string $uuid, bool $includeDisabled = false): ?Game;
    public function exists(int|string $identifier, bool $includeDisabled = false): bool;
}
