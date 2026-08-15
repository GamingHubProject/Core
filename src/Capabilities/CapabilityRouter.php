<?php

namespace GamingHub\Core\Capabilities;

use GamingHub\Core\Contracts\CapabilityProviderContract;
use GamingHub\Core\Models\CapabilityBinding;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * The one registry of capability providers, and how a (capability, subject)
 * pair resolves to a binding + the provider that serves it.
 *
 * This is deliberately the *simple*, single-value fallback path — a plain
 * admin-entered "default" with no external I/O (see ManualProvider). The
 * real priority stack for Server capabilities (REST above Pelican above
 * this default) lives on Provider.priority and is walked directly by
 * Platform's CapabilityGateway; a CapabilityBinding is not that mechanism
 * and is never auto-generated on a Provider's behalf.
 */
class CapabilityRouter
{
    /** @var array<string, CapabilityProviderContract> */
    protected array $providers = [];

    public function registerProvider(CapabilityProviderContract $provider): void
    {
        $this->providers[$provider::id()] = $provider;
    }

    public function providerFor(string $id): CapabilityProviderContract
    {
        return $this->providers[$id]
            ?? throw new InvalidArgumentException("No capability provider registered for [{$id}].");
    }

    public function findBinding(string $capability, Model $subject): ?CapabilityBinding
    {
        return CapabilityBinding::query()
            ->where('capability', $capability)
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey())
            ->first();
    }
}
