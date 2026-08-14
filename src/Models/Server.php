<?php

namespace GamingHub\Core\Models;

use GamingHub\Core\Database\Factories\ServerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Server does NOT know about ServerGroup or Theme — those are Platform
 * concerns (context grouping and visual styling respectively) and query by
 * server_id/server_group_id directly rather than via a relation here.
 */
class Server extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'server_group_id',
        'name',
        'slug',
        'description',
        'status',
        'max_players',
        'current_players',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function instances(): HasMany
    {
        return $this->hasMany(Instance::class);
    }

    public function providers(): HasMany
    {
        return $this->hasMany(Provider::class);
    }

    protected static function newFactory(): ServerFactory
    {
        return ServerFactory::new();
    }
}
