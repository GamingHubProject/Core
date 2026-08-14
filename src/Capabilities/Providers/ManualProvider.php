<?php

namespace GamingHub\Core\Capabilities\Providers;

use GamingHub\Core\Capabilities\CapabilityValue;
use GamingHub\Core\Contracts\CapabilityProviderContract;
use GamingHub\Core\Models\CapabilityBinding;

/**
 * The only provider that exists before real Connector packages do — the
 * "value" is whatever an admin typed into the binding directly. No external
 * I/O, so it's safely Core-side (Core never speaks to connectors, but
 * reading and normalizing a stored value isn't speaking to one).
 */
class ManualProvider implements CapabilityProviderContract
{
    public static function id(): string
    {
        return 'manual';
    }

    public function fetch(CapabilityBinding $binding): CapabilityValue
    {
        if (! $binding->enabled) {
            return CapabilityValue::unavailable($binding->capability);
        }

        return CapabilityValue::ok($binding->capability, $binding->value ?? []);
    }
}
