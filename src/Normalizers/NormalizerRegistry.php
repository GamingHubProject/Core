<?php

namespace GamingHub\Core\Normalizers;

use GamingHub\Core\Contracts\NormalizerContract;
use InvalidArgumentException;

/**
 * The one registry of normalizers. Both the contract and the concrete
 * normalizer classes (GamingHub\Core\Normalizers\*) live in Core — data
 * normalization is Core's job. Platform's AppServiceProvider registers
 * instances here at boot (still Platform's job to decide *when* a
 * normalizer is available, e.g. gated by an InstalledPackage's enabled
 * state), but never authors the normalization logic itself.
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
