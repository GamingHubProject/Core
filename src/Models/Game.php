<?php

namespace Azuriom\Plugin\GamingHubCore\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Game extends Model
{
    use HasTablePrefix;

    protected $prefix = 'gaminghub_';

    protected $fillable = [
        'slug',
        'name',
        'short_name',
        // Legacy compatibility only. Current administration uses short_description/long_description.
        'description',
        'short_description',
        'long_description',
        'icon_url',
        'banner_url',
        'icon_media_id',
        'cover_media_id',
        'accent_color',
        'enabled',
        'sort_order',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'icon_media_id' => 'integer',
        'cover_media_id' => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(fn (Game $game) => $game->uuid ??= (string) Str::uuid());
    }

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    /**
     * @deprecated Provider instances are Server-owned. Retained only so migrated
     *             installations and older integrations can read historical data.
     */
    public function providers(): HasMany
    {
        return $this->hasMany(ProviderInstance::class);
    }

    public function shortDescriptionForDisplay(): ?string
    {
        return $this->firstNonBlank($this->short_description, $this->description);
    }

    public function longDescriptionForDisplay(): ?string
    {
        return $this->firstNonBlank($this->long_description, $this->description);
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name')->orderBy('id');
    }

    private function firstNonBlank(mixed ...$values): ?string
    {
        foreach ($values as $value) {
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }
}
