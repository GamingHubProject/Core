<?php

namespace Azuriom\Plugin\GamingHubCore\Services;

use Closure;

/**
 * Prevents model observers from re-entering Core-managed provider operations.
 */
final class ProviderLifecycleContext
{
    private int $managedDepth = 0;

    public function managed(): bool
    {
        return $this->managedDepth > 0;
    }

    public function run(Closure $callback): mixed
    {
        $this->managedDepth++;

        try {
            return $callback();
        } finally {
            $this->managedDepth--;
        }
    }
}
