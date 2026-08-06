<?php

namespace Azuriom\Plugin\GamingHubCore\Services;

use InvalidArgumentException;

final class ProviderPositionSequence
{
    /**
     * @param list<int> $providerIds
     * @return list<int>
     */
    public static function move(array $providerIds, int $providerId, string $direction): array
    {
        if (! in_array($direction, ['up', 'down'], true)) {
            throw new InvalidArgumentException('Provider direction must be up or down.');
        }

        $providerIds = array_values($providerIds);
        $index = array_search($providerId, $providerIds, true);

        if ($index === false) {
            return $providerIds;
        }

        $target = $direction === 'up' ? $index - 1 : $index + 1;
        if (! array_key_exists($target, $providerIds)) {
            return $providerIds;
        }

        [$providerIds[$index], $providerIds[$target]] = [$providerIds[$target], $providerIds[$index]];

        return array_values($providerIds);
    }

    /**
     * Repositions a provider using the public one-based priority value.
     * Values below one mean "append" for compatibility with extension forms
     * that historically submitted zero for new providers.
     *
     * @param list<int> $providerIds
     * @return list<int>
     */
    public static function reposition(array $providerIds, int $providerId, int $position): array
    {
        $providerIds = array_values(array_filter(
            $providerIds,
            static fn (int $candidate): bool => $candidate !== $providerId,
        ));

        $position = $position < 1
            ? count($providerIds) + 1
            : min($position, count($providerIds) + 1);

        array_splice($providerIds, $position - 1, 0, [$providerId]);

        return array_values($providerIds);
    }
}
