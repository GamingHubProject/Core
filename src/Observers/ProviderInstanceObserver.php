<?php

namespace Azuriom\Plugin\GamingHubCore\Observers;

use Azuriom\Plugin\GamingHubCore\Exceptions\ProviderLifecycleException;
use Azuriom\Plugin\GamingHubCore\Models\ProviderInstance;
use Azuriom\Plugin\GamingHubCore\Models\Server;
use Azuriom\Plugin\GamingHubCore\Services\ProviderLifecycleContext;
use Azuriom\Plugin\GamingHubCore\Services\ProviderLifecycleManager;
use Azuriom\Plugin\GamingHubCore\Services\ProviderCreationTrace;
use Illuminate\Support\Facades\DB;

/**
 * Maintains Core ordering invariants when an extension persists the generic
 * ProviderInstance model through its own controller.
 *
 * Core's controller still uses ProviderLifecycleManager directly. This
 * observer is the compatibility boundary for extension-owned forms such as
 * Gaming Hub Panel and future provider extensions.
 */
final class ProviderInstanceObserver
{
    public function __construct(
        private readonly ProviderLifecycleContext $context,
        private readonly ProviderLifecycleManager $lifecycle,
        private readonly ProviderCreationTrace $trace,
    ) {
    }

    public function creating(ProviderInstance $provider): void
    {
        if ($this->context->managed()) {
            return;
        }

        $this->trace->model('model_creating', $provider);

        $serverId = (int) $provider->server_id;
        if ($serverId < 1) {
            throw new ProviderLifecycleException('A provider must belong to a Gaming Hub Server.');
        }

        $server = Server::query()->find($serverId);
        if (! $server instanceof Server) {
            throw new ProviderLifecycleException('The selected Gaming Hub Server no longer exists.');
        }

        // The extension controller may already own an outer transaction. A
        // nested Laravel transaction keeps its row locks until that outer
        // transaction commits. Without an outer transaction the unique index
        // still prevents duplicate priority values.
        $this->lifecycle->normalize($server);

        $provider->forceFill([
            'game_id' => (int) $server->game_id,
            'position' => ((int) ProviderInstance::query()
                ->where('server_id', $serverId)
                ->max('position')) + 1,
        ]);

        $this->trace->model('model_position_assigned', $provider);
    }


    public function created(ProviderInstance $provider): void
    {
        if ($this->context->managed()) {
            return;
        }

        $this->trace->model('repository_saved', $provider);

        // Extension-owned controllers commonly wrap the model save in their
        // own transaction. Log commit only after that outer transaction really
        // commits; a rollback never emits transaction_committed.
        if (DB::transactionLevel() > 0) {
            DB::afterCommit(function () use ($provider): void {
                $this->trace->model('transaction_committed', $provider);
            });

            return;
        }

        $this->trace->model('transaction_committed', $provider);
    }

    public function updating(ProviderInstance $provider): void
    {
        if ($this->context->managed()) {
            return;
        }

        // Generic extension edit controllers may submit a stale/default order
        // field. Priority changes must go through the transactional move API.
        foreach (['game_id', 'server_id', 'position'] as $attribute) {
            if ($provider->isDirty($attribute)) {
                $provider->setAttribute($attribute, $provider->getOriginal($attribute));
            }
        }
    }

    public function deleted(ProviderInstance $provider): void
    {
        if ($this->context->managed()) {
            return;
        }

        $server = Server::query()->find((int) $provider->server_id);
        if ($server instanceof Server) {
            $this->lifecycle->normalize($server);
        }
    }
}
