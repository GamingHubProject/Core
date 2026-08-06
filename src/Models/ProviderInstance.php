<?php

namespace Azuriom\Plugin\GamingHubCore\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Plugin\GamingHubCore\Services\SharedDataCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Throwable;

class ProviderInstance extends Model
{
    use HasTablePrefix;

    protected $prefix = 'gaminghub_';
    protected $table = 'gaminghub_provider_instances';

    protected $fillable = [
        'game_id',
        'server_id',
        'provider_type',
        'name',
        'enabled',
        'position',
        'configuration',
    ];

    protected $casts = [
        'game_id' => 'integer',
        'server_id' => 'integer',
        'enabled' => 'boolean',
        'position' => 'integer',
        'configuration' => 'array',
    ];

    protected $hidden = ['configuration'];

    protected static function booted(): void
    {
        foreach (['saved', 'deleted'] as $event) {
            static::{$event}(function (self $provider): void {
                try {
                    app(SharedDataCache::class)->invalidateProvider((int) $provider->getKey());
                } catch (Throwable) {
                    // Cache outages must not block provider persistence.
                }
            });
        }
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /** @deprecated Retained for migrated metadata compatibility only. */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }
}
