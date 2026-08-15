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
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'last_check' => 'datetime',
        ];
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    protected static function newFactory(): ProviderFactory
    {
        return ProviderFactory::new();
    }
}
