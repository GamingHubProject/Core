<?php

namespace Azuriom\Plugin\GamingHubCore\Services;

use Azuriom\Plugin\GamingHubCore\Contracts\GameRegistry;
use Azuriom\Plugin\GamingHubCore\Models\Game;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EloquentGameRegistry implements GameRegistry
{
    public function all(bool $includeDisabled = false): Collection
    {
        return $this->query($includeDisabled)->ordered()->get();
    }

    public function findById(int $id, bool $includeDisabled = false): ?Game
    {
        return $this->query($includeDisabled)->find($id);
    }

    public function findBySlug(string $slug, bool $includeDisabled = false): ?Game
    {
        return $this->query($includeDisabled)->where('slug', $slug)->first();
    }

    public function findByUuid(string $uuid, bool $includeDisabled = false): ?Game
    {
        return $this->query($includeDisabled)->where('uuid', $uuid)->first();
    }

    public function exists(int|string $identifier, bool $includeDisabled = false): bool
    {
        $query = $this->query($includeDisabled);

        if (is_int($identifier) || ctype_digit($identifier)) {
            return $query->whereKey((int) $identifier)->exists();
        }

        return $query->where(function (Builder $query) use ($identifier): void {
            $query->where('slug', $identifier)->orWhere('uuid', $identifier);
        })->exists();
    }

    private function query(bool $includeDisabled): Builder
    {
        $query = Game::query();

        return $includeDisabled ? $query : $query->enabled();
    }
}
