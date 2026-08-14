<?php

namespace GamingHub\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * The capability definition/routing data itself: binds a capability to a
 * Context Subject with a named provider. This is Core's "capability
 * definitions & routing" — deciding *which* provider serves a capability,
 * never speaking to one. The morph map (which subject types exist) is
 * registered by the host app, since Core doesn't know about Platform-side
 * subjects like Map.
 */
class CapabilityBinding extends Model
{
    protected $fillable = [
        'capability',
        'subject_type',
        'subject_id',
        'provider',
        'value',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'enabled' => 'boolean',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
