<?php

namespace GamingHub\Core\Normalizers;

use GamingHub\Core\Contracts\NormalizerContract;
use InvalidArgumentException;

/**
 * The one registry of normalizers. Platform registers game/connector-specific
 * normalizers here at boot (e.g. "palworld-server-status",
 * "pelican-server-status") — Core just holds the registry and the contract,
 * it never authors game-specific normalization logic itself.
 */
class NormalizerRegistry
{
    /** @var array<string, NormalizerContract> */
    protected array $normalizers = [];

    public function register(string $id, NormalizerContract $normalizer): void
    {
        $this->normalizers[$id] = $normalizer;
    }

    public function get(string $id): NormalizerContract
    {
        return $this->normalizers[$id]
            ?? throw new InvalidArgumentException("No normalizer registered for [{$id}].");
    }

    public function has(string $id): bool
    {
        return isset($this->normalizers[$id]);
    }
}
