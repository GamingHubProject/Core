<?php

namespace Azuriom\Plugin\GamingHubCore\Services;

/**
 * Reconciles extension-validated configuration with the raw HTTP payload, but
 * only for fields declared by the selected Provider Registry type.
 *
 * Some extension-owned FormRequests validate their own mapping fields and call
 * Core's generic validator afterward. Laravel's validated() removes registry
 * fields that the extension request did not explicitly declare. Recovering only
 * declared provider fields here keeps validation strict without trusting
 * arbitrary configuration keys.
 */
final class ProviderConfigurationInput
{
    /**
     * @param array<string, mixed> $validated
     * @param array<string, mixed> $raw
     * @param list<string> $declaredKeys
     * @return array<string, mixed>
     */
    public static function reconcile(array $validated, array $raw, array $declaredKeys): array
    {
        $result = [];

        foreach ($declaredKeys as $key) {
            if (array_key_exists($key, $validated)) {
                $result[$key] = $validated[$key];
            } elseif (array_key_exists($key, $raw)) {
                $result[$key] = $raw[$key];
            }
        }

        return $result;
    }
}
