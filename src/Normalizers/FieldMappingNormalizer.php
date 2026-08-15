<?php

namespace GamingHub\Core\Normalizers;

use GamingHub\Core\Capabilities\CapabilityValue;
use GamingHub\Core\Contracts\NormalizerContract;

/**
 * Renames raw provider fields into a capability's expected shape using an
 * admin-supplied mapping instead of a hardcoded per-game class — this is
 * what a generic REST connector uses in place of a game-specific
 * normalizer (see the removed PalworldServerStatusNormalizer). An admin
 * setting up a REST connector for whatever game they're actually running
 * maps that game's own field names once, in the Providers form; Core
 * never learns what game produced the raw payload.
 *
 * $config shape: {"capability": "server-status", "field_map": {"currentplayernum": "players", "maxplayernum": "max_players"}}
 *
 * 'online' is synthesized as true if the mapping didn't already produce an
 * 'online' key and at least one other field was found in the raw payload —
 * a generic stand-in for "the call succeeded and returned something
 * recognizable," not a per-game rule. If none of the mapped fields are
 * present at all, the raw payload doesn't match what was configured and
 * the result is UNAVAILABLE, the same way a fixed-shape normalizer treats
 * an unexpected response.
 */
class FieldMappingNormalizer implements NormalizerContract
{
    public function normalize(array $raw, array $config = []): CapabilityValue
    {
        $capability = $this->capability($config);
        $fieldMap = $config['field_map'] ?? [];

        $data = [];

        foreach ($fieldMap as $rawKey => $targetKey) {
            if (array_key_exists($rawKey, $raw)) {
                $data[$targetKey] = $raw[$rawKey];
            }
        }

        if ($data === []) {
            return CapabilityValue::unavailable($capability);
        }

        if (! array_key_exists('online', $data)) {
            $data['online'] = true;
        }

        return CapabilityValue::ok($capability, $data);
    }

    public function capability(array $config = []): string
    {
        return $config['capability'] ?? '';
    }
}
