<?php

namespace GamingHub\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One ip:port allocation on a Server, as reported by a connector (e.g.
 * Pelican's /network/allocations). Fully replaced on every poll tick by
 * whoever writes it — see Server::allocations() usage in Platform's
 * ServerAllocationSyncer — so nothing here is meant to be admin-editable.
 */
class ServerAllocation extends Model
{
    protected $fillable = [
        'server_id',
        'external_id',
        'ip',
        'ip_alias',
        'port',
        'is_default',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
