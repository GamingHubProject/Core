<?php

namespace GamingHub\Core\Contracts;

use GamingHub\Core\Capabilities\CapabilityValue;

/**
 * Translates a connector's raw payload into a normalized CapabilityValue.
 * This is the "data normalization" step — Core never fetches the raw
 * payload itself (that's Panel calling a Connector), it only shapes what
 * comes back. Core's own generic normalizers (GamingHub\Core\Normalizers,
 * e.g. FieldMapping) implement this contract directly; a normalizer built
 * around one specific external system's fixed response shape can just as
 * well live outside Core entirely and travel with the Connector package
 * that produces the raw data it shapes — Core only defines the contract
 * and never needs to know which is which.
 *
 * capability() lets a generic caller — the Provider priority stack in
 * CapabilityGateway — know which capability a given Provider's chosen
 * normalizer actually serves, without hardcoding any per-game assumption: a
 * Provider only participates in resolving a capability if its normalizer's
 * declared capability matches the one being requested.
 *
 * $config is the owning Provider's own config JSON, passed through
 * unchanged. A fixed-shape normalizer (built around one specific external
 * system's API) ignores it — its capability is a constant. A generic,
 * admin-configured normalizer (FieldMappingNormalizer) reads its target
 * capability and field mapping from it instead of hardcoding either, which
 * is what makes it usable for any game's REST API without a per-game class.
 */
interface NormalizerContract
{
    public function normalize(array $raw, array $config = []): CapabilityValue;

    public function capability(array $config = []): string;
}
