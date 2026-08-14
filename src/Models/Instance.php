<?php

namespace GamingHub\Core\Models;

use GamingHub\Core\Database\Factories\InstanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Instance extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'name',
        'configuration',
    ];

    protected function casts(): array
    {
        return [
            'configuration' => 'array',
        ];
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    protected static function newFactory(): InstanceFactory
    {
        return InstanceFactory::new();
    }
}
