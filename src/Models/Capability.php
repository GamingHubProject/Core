<?php

namespace GamingHub\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One entry in the capability catalogue (see CapabilityRegistry). Primary
 * key is the capability id string itself ("server-status") — the same
 * value already used everywhere else (Provider.config, CapabilityBinding.
 * capability, NormalizerContract::capability()), not a surrogate int id.
 */
class Capability extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'description',
    ];
}
