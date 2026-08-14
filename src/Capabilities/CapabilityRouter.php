<?php

namespace GamingHub\Core\Capabilities;

use GamingHub\Core\Contracts\CapabilityProviderContract;
use GamingHub\Core\Models\CapabilityBinding;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * The one registry of capability providers, and how a (capability, subject)
 * pair resolves to a binding + the provider that serves it. This is Core's
 * "capability definitions & routing" — Platform's CapabilityGateway (acting
 * as Panel) asks this the decision, then either invokes the provider
 * directly (manual) or, for a real Connector-backed provider, invokes the
 * connector itself and hands the raw payload back to Core to normalize.
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
