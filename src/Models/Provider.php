<?php

namespace GamingHub\Core\Models;

use GamingHub\Core\Database\Factories\ProviderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A Server's binding to a Platform-side ConnectorInstance — the domain fact
 * "this server is monitored via connector X, with config Y" (e.g. which
 * Pelican server identifier it maps to). connector_instance_id is a plain
 * soft reference, not an Eloquent relation: ConnectorInstance is a Platform
 * model, and Core must never know about it directly. Platform resolves the
 * reference itself (see App\Filament\Resources\ServerResource\
 * RelationManagers\ProvidersRelationManager).
 */
class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'connector_instance_id',
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
