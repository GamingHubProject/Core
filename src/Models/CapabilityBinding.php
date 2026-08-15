<?php

namespace GamingHub\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A single admin-entered "default" value for a capability — the bottom of
 * the priority stack (see Provider.priority / CapabilityGateway::probe()),
 * used only when no Provider on the subject answers the capability. This is
 * NOT the mechanism providers use to route data; a Provider is resolved
 * directly by CapabilityGateway walking Server->providers() in priority
 * order, never through a CapabilityBinding record. The morph map (which
 * subject types exist) is registered by the host app, since Core doesn't
 * know about Platform-side subjects like Map.
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
