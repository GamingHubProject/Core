<?php

namespace GamingHub\Core\Contracts;

use GamingHub\Core\Capabilities\CapabilityValue;

/**
 * Translates a connector's raw payload into a normalized CapabilityValue.
 * This is Core's "data normalization" job — Core never fetches the raw
 * payload itself (that's Panel calling a Connector), it only shapes what
 * comes back. Which normalizer applies to a given binding is an explicit
 * choice (see CapabilityBinding's connector config), not auto-inferred,
 * since the same capability can come from differently-shaped raw payloads
 * (a game's own API vs. a hosting panel's generic API).
 */
interface NormalizerContract
{
    public function normalize(array $raw): CapabilityValue;
}
