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
 *
 * $config is the owning Provider's own config JSON, passed through
 * unchanged. A fixed-shape normalizer (Pelican's) ignores it — its
 * capability is a constant. A generic, admin-configured normalizer
 * (FieldMappingNormalizer) reads its target capability and field mapping
 * from it instead of hardcoding either, which is what makes it usable for
 * any game's REST API without a per-game class.
 */
interface NormalizerContract
{
    public function normalize(array $raw, array $config = []): CapabilityValue;

    public function capability(array $config = []): string;
}
