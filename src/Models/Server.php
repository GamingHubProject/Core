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
        'cpu_usage_percent',
        'memory_usage_bytes',
        'cpu_current',
        'cpu_limit',
        'cpu_percent',
        'memory_current',
        'memory_limit',
        'memory_percent',
        'disk_current',
        'disk_limit',
        'disk_percent',
        'network_rx',
        'network_tx',
        'node_name',
        'supported_features',
        'game_version',
        'last_polled_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'supported_features' => 'array',
            'last_polled_at' => 'datetime',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function providers(): HasMany
    {
        return $this->hasMany(Provider::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ServerAllocation::class);
    }

    /**
     * Whether this server has at least one connector-backed provider (as
     * opposed to only manual providers, or none at all) — Platform uses
     * this to decide whether the live-stats fields on this Server are
     * driven by PollProviders and should be read-only in the admin form.
     * Deliberately stops here: knowing *which* connector is Platform's
     * concern (it needs ConnectorInstance, a Platform model), not Core's.
     */
    public function hasConnectorProvider(): bool
    {
        return $this->providers()->where('type', 'connector')->exists();
    }

    protected static function newFactory(): ServerFactory
    {
        return ServerFactory::new();
    }
}
