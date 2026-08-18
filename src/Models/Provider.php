<?php

namespace GamingHub\Core\Models;

use GamingHub\Core\Database\Factories\ProviderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One entry in a Server's priority-ordered capability provider stack (see
 * CapabilityGateway::probe()). 'type' discriminates the shape of 'config':
 *
 * - 'connector' (default): a binding to a Platform-side ConnectorInstance —
 *   "this server is monitored via connector X, with config Y" (e.g. which
 *   Pelican server identifier it maps to). connector_instance_id is a plain
 *   soft reference, not an Eloquent relation: ConnectorInstance is a
 *   Platform model, and Core must never know about it directly. Platform
 *   resolves the reference itself (see App\Filament\Resources\
 *   ServerResource\RelationManagers\ProvidersRelationManager).
 * - 'manual': an admin-entered {capability, value} pair, no external I/O
 *   and no connector_instance_id — the exact same data ManualProvider has
 *   always served, just reachable from the priority stack instead of only
 *   from a separate CapabilityBinding record.
 */
class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'type',
        'connector_instance_id',
        'priority',
        'config',
        'status',
        'last_check',
        'error_message',
        'last_raw_response',
        'polling_cadence_seconds',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'last_check' => 'datetime',
            'last_raw_response' => 'array',
        ];
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Independent of (and never faster than) ConnectorInstance::isDueForPoll()
     * — that gates the shared credentialed connection this Provider's
     * connector_instance_id points at; this gates this one Provider's own
     * refresh rate within a tick where the instance is already due. Same
     * null-means-never-checked-yet shape as ConnectorInstance's version.
     */
    public function isDueForPoll(): bool
    {
        return ! $this->last_check
            || $this->last_check->diffInSeconds(now()) >= $this->polling_cadence_seconds;
    }

    protected static function newFactory(): ProviderFactory
    {
        return ProviderFactory::new();
    }
}
