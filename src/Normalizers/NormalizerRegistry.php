<?php

namespace GamingHub\Core\Normalizers;

use GamingHub\Core\Contracts\NormalizerContract;
use InvalidArgumentException;

/**
 * The one registry of normalizers. The contract and Core's own generic
 * normalizer classes (GamingHub\Core\Normalizers\*, e.g. FieldMapping) live
 * here — but a normalizer shaped around one specific external system (e.g.
 * a particular game-panel's API) can just as well travel with the
 * Connector package that produces the raw data it normalizes, registered
 * from outside Core entirely. Either way, Core only ever defines the
 * *contract* and holds whatever's currently registered against it — it
 * never knows or cares where a given normalizer instance came from.
 *
 * onMiss() is how Core stays ignorant of *how* a not-yet-registered
 * normalizer might become available without needing to know what a
 * "package" or "extension" even is (both are Platform concepts). Platform
 * wires a hook here — typically "try loading installed packages, then
 * check again" — and Core just calls it on a cache miss, the same
 * blind-callback pattern used anywhere a lower layer needs to reach
 * upward without naming what it's reaching into.
 */
class NormalizerRegistry
{
    /** @var array<string, NormalizerContract> */
    protected array $normalizers = [];

    /** @var (callable(string): void)|null */
    protected $missHandler = null;

    public function register(string $id, NormalizerContract $normalizer): void
    {
        $this->normalizers[$id] = $normalizer;
    }

    /**
     * @param  callable(string $id): void  $handler
     */
    public function onMiss(callable $handler): void
    {
        $this->missHandler = $handler;
    }

    public function get(string $id): NormalizerContract
    {
        $this->resolveOnMiss($id);

        return $this->normalizers[$id]
            ?? throw new InvalidArgumentException("No normalizer registered for [{$id}].");
    }

    public function has(string $id): bool
    {
        $this->resolveOnMiss($id);

        return isset($this->normalizers[$id]);
    }

    protected function resolveOnMiss(string $id): void
    {
        if (! isset($this->normalizers[$id]) && $this->missHandler) {
            ($this->missHandler)($id);
        }
    }
}
