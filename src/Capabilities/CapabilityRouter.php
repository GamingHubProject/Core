<?php

namespace GamingHub\Core\Capabilities;

use GamingHub\Core\Contracts\CapabilityProviderContract;
use GamingHub\Core\Models\CapabilityBinding;
use Illuminate\Database\Eloquent\Collection;
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
        return $this->findBindings($capability, $subject)->first();
    }

    /**
     * All bindings for a (capability, subject) pair, ordered by priority —
     * lowest number first. More than one can exist: e.g. a server's
     * "server-status" may be served by several providers at once, each
     * contributing different fields (see CapabilityGateway::probe()).
     *
     * @return Collection<int, CapabilityBinding>
     */
    public function findBindings(string $capability, Model $subject): Collection
    {
        return CapabilityBinding::query()
            ->where('capability', $capability)
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey())
            ->orderBy('priority')
            ->orderBy('id')
            ->get();
    }
}
