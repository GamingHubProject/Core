<?php

namespace GamingHub\Core\Models;

use GamingHub\Core\Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A game may have zero servers (publisher-hosted or community-only games
 * are first-class). Game does NOT know about ServerGroup, Map,
 * ConfigurationPreset, GameExtension, Page, or Theme — those are Platform
 * concerns and query by game_id directly rather than via a relation here.
 */
class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon_url',
        'status',
        'has_servers',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'has_servers' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    protected static function newFactory(): GameFactory
    {
        return GameFactory::new();
    }
}
