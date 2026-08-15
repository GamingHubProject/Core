<?php

namespace GamingHub\Core\Contracts;

use GamingHub\Core\Capabilities\CapabilityValue;

/**
 * Translates a connector's raw payload into a normalized CapabilityValue.
 * This is Core's "data normalization" job, and the concrete normalizer
 * classes live in Core (GamingHub\Core\Normalizers) — Core never fetches
 * the raw payload itself (that's Panel calling a Connector), it only shapes
 * what comes back, but the shaping logic itself belongs here, not Platform.
 *
 * capability() lets a generic caller — the Provider priority stack in
 * CapabilityGateway — know which capability a given Provider's chosen
 * normalizer actually serves, without hardcoding any per-game assumption: a
 * Provider only participates in resolving a capability if its normalizer's
 * declared capability matches the one being requested.
 */
interface NormalizerContract
{
    public function normalize(array $raw): CapabilityValue;

    public function capability(): string;
}
