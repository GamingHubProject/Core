<?php

namespace GamingHub\Core\Models;

use GamingHub\Core\Database\Factories\ProviderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'type',
        'credentials',
        'status',
        'last_check',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
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
