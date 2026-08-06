<?php

namespace Azuriom\Plugin\GamingHubCore\Services;

use Azuriom\Plugin\GamingHubCore\Events\ProviderDeleted;
use Azuriom\Plugin\GamingHubCore\Events\ProviderDeleting;
use Azuriom\Plugin\GamingHubCore\Exceptions\ProviderLifecycleException;
use Azuriom\Plugin\GamingHubCore\Models\ProviderInstance;
use Azuriom\Plugin\GamingHubCore\Models\Server;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ProviderLifecycleManager
{
    public function __construct(
        private readonly SharedDataCache $cache,
        private readonly ProviderLifecycleContext $context,
        private readonly ProviderCreationTrace $trace,
    ) {
    }

    public function normalize(Server $server): void
    {
        $this->context->run(function () use ($server): void {
            DB::transaction(function () use ($server): void {
                $providers = $this->lockProviders($server);
                $this->persistOrder($server, $providers->modelKeys(), $providers);
            }, 3);
        });
    }

    /** @param array<string, mixed> $attributes */
    public function create(Server $server, array $attributes): ProviderInstance
    {
        $this->trace->validated($attributes, self::class);
        $this->trace->stage('lifecycle_entered', [
            'server_id' => $server->getKey(),
            'provider_type' => $attributes['provider_type'] ?? null,
        ]);

        try {
            $provider = $this->context->run(function () use ($server, $attributes): ProviderInstance {
                return DB::transaction(function () use ($server, $attributes): ProviderInstance {
                    $providers = $this->lockProviders($server);
                    $this->persistOrder($server, $providers->modelKeys(), $providers);
                    $this->trace->stage('ordering_normalized', [
                        'server_id' => $server->getKey(),
                        'provider_count' => $providers->count(),
                    ]);

                    $requestedPosition = (int) ($attributes['position'] ?? 0);
                    $this->trace->stage('provider_dto_built', [
                        'server_id' => $server->getKey(),
                        'provider_type' => $attributes['provider_type'] ?? null,
                        'attribute_keys' => array_keys($attributes),
                        'configuration_keys' => array_keys((array) ($attributes['configuration'] ?? [])),
                    ]);
                    $provider = $server->providers()->create([
                        ...Arr::except($attributes, ['game_id', 'server_id', 'position']),
                        'game_id' => (int) $server->game_id,
                        'position' => $providers->count() + 1,
                    ]);
                    $this->trace->model('repository_saved', $provider);

                    $providers->push($provider);
                    $orderedIds = ProviderPositionSequence::reposition(
                        $providers->modelKeys(),
                        (int) $provider->getKey(),
                        $requestedPosition,
                    );
                    $this->persistOrder($server, $orderedIds, $providers);

                    return $provider->refresh();
                }, 3);
            });
        } catch (Throwable $exception) {
            $this->trace->failed($exception, 'lifecycle_transaction');

            throw $exception;
        }

        $this->trace->model('transaction_committed', $provider);

        return $provider;
    }

    /** @param array<string, mixed> $attributes */
    public function update(Server $server, ProviderInstance $provider, array $attributes): ProviderInstance
    {
        return $this->context->run(function () use ($server, $provider, $attributes): ProviderInstance {
            return DB::transaction(function () use ($server, $provider, $attributes): ProviderInstance {
                $providers = $this->lockProviders($server);
                $this->persistOrder($server, $providers->modelKeys(), $providers);
                $locked = $providers->first(fn (ProviderInstance $candidate): bool => $candidate->is($provider));

                if (! $locked instanceof ProviderInstance) {
                    throw new ProviderLifecycleException('The selected provider is no longer assigned to this server.');
                }

                $requestedPosition = (int) ($attributes['position'] ?? $locked->position);
                if ($requestedPosition < 1) {
                    $requestedPosition = (int) $locked->position;
                }

                $locked->fill(Arr::except($attributes, ['game_id', 'server_id', 'position']));
                $locked->save();

                $orderedIds = ProviderPositionSequence::reposition(
                    $providers->modelKeys(),
                    (int) $locked->getKey(),
                    $requestedPosition,
                );
                $this->persistOrder($server, $orderedIds, $providers);

                return $locked->refresh();
            }, 3);
        });
    }

    public function toggle(Server $server, ProviderInstance $provider): ProviderInstance
    {
        return $this->context->run(function () use ($server, $provider): ProviderInstance {
            return DB::transaction(function () use ($server, $provider): ProviderInstance {
                $providers = $this->lockProviders($server);
                $this->persistOrder($server, $providers->modelKeys(), $providers);
                $locked = $providers->first(fn (ProviderInstance $candidate): bool => $candidate->is($provider));

                if (! $locked instanceof ProviderInstance) {
                    throw new ProviderLifecycleException('The selected provider is no longer assigned to this server.');
                }

                $locked->update(['enabled' => ! $locked->enabled]);

                return $locked->refresh();
            }, 3);
        });
    }

    public function move(Server $server, ProviderInstance $provider, string $direction): bool
    {
        return $this->context->run(function () use ($server, $provider, $direction): bool {
            return DB::transaction(function () use ($server, $provider, $direction): bool {
                $providers = $this->lockProviders($server);
                $this->persistOrder($server, $providers->modelKeys(), $providers);
                $locked = $providers->first(fn (ProviderInstance $candidate): bool => $candidate->is($provider));

                if (! $locked instanceof ProviderInstance) {
                    throw new ProviderLifecycleException('The selected provider is no longer assigned to this server.');
                }

                $orderedIds = $providers->modelKeys();
                $movedIds = ProviderPositionSequence::move(
                    $orderedIds,
                    (int) $locked->getKey(),
                    $direction,
                );

                if ($movedIds === $orderedIds) {
                    return false;
                }

                $this->persistOrder($server, $movedIds, $providers);

                return true;
            }, 3);
        });
    }

    public function delete(Server $server, ProviderInstance $provider): void
    {
        $snapshot = $this->context->run(function () use ($server, $provider): ProviderInstance {
            return DB::transaction(function () use ($server, $provider): ProviderInstance {
                $providers = $this->lockProviders($server);
                $this->persistOrder($server, $providers->modelKeys(), $providers);
                $locked = $providers->first(fn (ProviderInstance $candidate): bool => $candidate->is($provider));

                if (! $locked instanceof ProviderInstance) {
                    throw new ProviderLifecycleException('The selected provider is no longer assigned to this server.');
                }

                $snapshot = clone $locked;

                try {
                    event(new ProviderDeleting($locked));
                } catch (Throwable $exception) {
                    throw new ProviderLifecycleException(
                        'The provider cleanup hook rejected deletion.',
                        previous: $exception,
                    );
                }

                $providerId = (int) $locked->getKey();
                if (! $locked->delete()) {
                    throw new ProviderLifecycleException('The provider could not be deleted.');
                }

                $this->cache->invalidateProvider($providerId);

                $remaining = $providers
                    ->reject(fn (ProviderInstance $candidate): bool => (int) $candidate->getKey() === $providerId)
                    ->values();
                $this->persistOrder($server, $remaining->modelKeys(), $remaining);

                return $snapshot;
            }, 3);
        });

        try {
            event(new ProviderDeleted($snapshot));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /** @return Collection<int, ProviderInstance> */
    private function lockProviders(Server $server): Collection
    {
        Server::query()
            ->whereKey($server->getKey())
            ->lockForUpdate()
            ->firstOrFail();

        return ProviderInstance::query()
            ->where('server_id', $server->getKey())
            ->orderBy('position')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    /**
     * Writes priorities in two phases so the `(server_id, position)` unique
     * index cannot be violated during a swap. Query-builder updates avoid
     * recursive model events; cache generations are advanced explicitly.
     *
     * @param list<int> $orderedIds
     * @param Collection<int, ProviderInstance> $providers
     */
    private function persistOrder(Server $server, array $orderedIds, Collection $providers): void
    {
        $byId = $providers->keyBy(fn (ProviderInstance $provider): int => (int) $provider->getKey());
        $finalPositions = [];
        $changedIds = [];

        foreach (array_values($orderedIds) as $offset => $providerId) {
            $provider = $byId->get($providerId);
            if (! $provider instanceof ProviderInstance) {
                throw new ProviderLifecycleException('Provider ordering changed during the operation.');
            }

            $position = $offset + 1;
            $finalPositions[$providerId] = $position;
            if ((int) $provider->position !== $position) {
                $changedIds[] = $providerId;
            }
        }

        if ($changedIds === []) {
            return;
        }

        $timestamp = now();
        foreach (array_values($changedIds) as $offset => $providerId) {
            $updated = ProviderInstance::query()
                ->whereKey($providerId)
                ->where('server_id', $server->getKey())
                ->update([
                    'position' => -($offset + 1),
                    'updated_at' => $timestamp,
                ]);

            if ($updated !== 1) {
                throw new ProviderLifecycleException('Provider ordering changed during the operation.');
            }
        }

        foreach ($changedIds as $providerId) {
            $updated = ProviderInstance::query()
                ->whereKey($providerId)
                ->where('server_id', $server->getKey())
                ->update([
                    'position' => $finalPositions[$providerId],
                    'updated_at' => $timestamp,
                ]);

            if ($updated !== 1) {
                throw new ProviderLifecycleException('Provider ordering changed during the operation.');
            }

            /** @var ProviderInstance $provider */
            $provider = $byId->get($providerId);
            $provider->forceFill([
                'position' => $finalPositions[$providerId],
                'updated_at' => $timestamp,
            ]);
            $provider->syncOriginalAttributes(['position', 'updated_at']);
            $this->cache->invalidateProvider($providerId);
        }
    }
}
