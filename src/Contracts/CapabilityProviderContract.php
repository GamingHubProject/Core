<?php

namespace GamingHub\Core\Contracts;

use GamingHub\Core\Capabilities\CapabilityValue;
use GamingHub\Core\Models\CapabilityBinding;

/**
 * How a capability's value gets normalized for a binding. ManualProvider
 * (no external I/O) lives entirely in Core. A real Connector-backed
 * provider will differ: Panel actually calls the connector and hands Core
 * the raw payload to normalize — Core itself never speaks to a connector.
 */
interface CapabilityProviderContract
{
    public static function id(): string;

    public function fetch(CapabilityBinding $binding): CapabilityValue;
}
