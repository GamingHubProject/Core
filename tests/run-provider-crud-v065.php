<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require $root.'/src/Services/ProviderPositionSequence.php';

use Azuriom\Plugin\GamingHubCore\Services\ProviderPositionSequence;

final class MemoryProviderStore
{
    /** @var array<int, array{id:int,server:int,type:string,enabled:bool,position:int,capabilities:list<string>,configuration:array<string,mixed>}> */
    private array $rows = [];
    private int $nextId = 1;

    /** @param list<string> $capabilities @param array<string,mixed> $configuration */
    public function create(int $server, string $type, array $capabilities, array $configuration = []): int
    {
        $ids = $this->ids($server);
        $id = $this->nextId++;
        $this->rows[$id] = compact('id', 'server', 'type', 'capabilities', 'configuration') + [
            'enabled' => true,
            'position' => count($ids) + 1,
        ];
        $this->persist($server, ProviderPositionSequence::reposition([...$ids, $id], $id, 0));

        return $id;
    }

    public function delete(int $server, int $id): void
    {
        if (($this->rows[$id]['server'] ?? null) !== $server) {
            throw new RuntimeException('ownership');
        }
        unset($this->rows[$id]);
        $this->persist($server, $this->ids($server));
    }

    public function move(int $server, int $id, string $direction): bool
    {
        $ids = $this->ids($server);
        $moved = ProviderPositionSequence::move($ids, $id, $direction);
        if ($moved === $ids) {
            return false;
        }
        $this->persist($server, $moved);

        return true;
    }

    /** @param array<string, mixed> $configuration */
    public function update(int $server, int $id, array $configuration, int $submittedPosition): void
    {
        if (($this->rows[$id]['server'] ?? null) !== $server) {
            throw new RuntimeException('ownership');
        }

        $position = $this->rows[$id]['position'];
        $this->rows[$id]['configuration'] = $configuration;
        // Simulates Core's extension-controller observer: normal edits cannot
        // rewrite priority; movement remains a separate transactional action.
        $this->rows[$id]['position'] = $position;
    }

    /** @return array<string, mixed> */
    public function configuration(int $id): array
    {
        return $this->rows[$id]['configuration'];
    }

    public function position(int $id): int
    {
        return $this->rows[$id]['position'];
    }

    public function setEnabled(int $id, bool $enabled): void
    {
        $this->rows[$id]['enabled'] = $enabled;
    }

    /** @return list<int> */
    public function priorities(int $server): array
    {
        return array_map(fn (int $id): int => $this->rows[$id]['position'], $this->ids($server));
    }

    /** @return list<int> */
    public function ids(int $server): array
    {
        $rows = array_filter($this->rows, static fn (array $row): bool => $row['server'] === $server);
        uasort($rows, static fn (array $a, array $b): int => [$a['position'], $a['id']] <=> [$b['position'], $b['id']]);

        return array_map('intval', array_keys($rows));
    }

    public function resolve(int $server, string $capability): ?int
    {
        foreach ($this->ids($server) as $id) {
            $row = $this->rows[$id];
            if ($row['enabled'] && in_array($capability, $row['capabilities'], true)) {
                return $id;
            }
        }

        return null;
    }

    public function corruptPositions(int $server, int $position): void
    {
        foreach ($this->ids($server) as $id) {
            $this->rows[$id]['position'] = $position;
        }
    }

    public function normalize(int $server): void
    {
        $this->persist($server, $this->ids($server));
    }

    public function reload(): self
    {
        /** @var self $copy */
        $copy = unserialize(serialize($this), ['allowed_classes' => [self::class]]);

        return $copy;
    }

    /** @param list<int> $ids */
    private function persist(int $server, array $ids): void
    {
        foreach (array_values($ids) as $offset => $id) {
            if (($this->rows[$id]['server'] ?? null) !== $server) {
                throw new RuntimeException('cross-server reorder');
            }
            $this->rows[$id]['position'] = $offset + 1;
        }
    }
}

$tests = 0;
$failures = [];
$check = static function (bool $condition, string $label) use (&$tests, &$failures): void {
    $tests++;
    if (! $condition) {
        $failures[] = $label;
    }
};

$store = new MemoryProviderStore();
$manual1 = $store->create(1, 'manual', ['server-status'], ['status' => 'online']);
$pelican1 = $store->create(1, 'pelican', ['server-status', 'metrics'], ['panel_connection_id' => 1]);
$manual2 = $store->create(1, 'manual', ['server-status'], ['status' => 'maintenance']);
$pterodactyl = $store->create(1, 'pterodactyl', ['server-status', 'metrics'], ['panel_connection_id' => 2]);
$otherServer = $store->create(2, 'manual', ['server-status']);
$pelicanDuplicateA = $store->create(3, 'pelican', ['server-status', 'metrics']);
$pelicanDuplicateB = $store->create(3, 'pelican', ['server-status', 'metrics']);

$check($store->priorities(1) === [1, 2, 3, 4], 'mixed provider creation is sequential');
$check($store->priorities(2) === [1], 'ordering is isolated per server');
$check($store->priorities(3) === [1, 2], 'duplicate extension provider creation is sequential');
$check($pelicanDuplicateA !== $pelicanDuplicateB, 'duplicate Pelican mappings are distinct instances');
$check($store->move(1, $manual1, 'down'), 'first provider moves down');
$check($store->ids(1) === [$pelican1, $manual1, $manual2, $pterodactyl], 'move down swaps immediate neighbor');
$check($store->move(1, $manual1, 'up'), 'second provider moves up');
$check($store->ids(1)[0] === $manual1, 'move up swaps immediate previous');
$check(! $store->move(1, $manual1, 'up'), 'first cannot move up');
$check(! $store->move(1, $pterodactyl, 'down'), 'last cannot move down');
$check($store->ids(2) === [$otherServer], 'moves do not affect another server');
$check($store->ids(3) === [$pelicanDuplicateA, $pelicanDuplicateB], 'moves do not affect extension providers on another server');

$positionBeforeEdit = $store->position($pelican1);
$store->update(1, $pelican1, ['panel_connection_id' => 9], 99);
$check($store->configuration($pelican1) === ['panel_connection_id' => 9], 'extension provider configuration edits persist');
$check($store->position($pelican1) === $positionBeforeEdit, 'ordinary extension edits cannot corrupt priority');

$store->move(1, $pelican1, 'up');
$check($store->resolve(1, 'server-status') === $pelican1, 'higher-priority Pelican wins server-status');
$check($store->resolve(1, 'metrics') === $pelican1, 'Manual is skipped for unsupported metrics');
$store->setEnabled($pelican1, false);
$check($store->resolve(1, 'metrics') === $pterodactyl, 'disabled Pelican is ignored');
$check($store->resolve(1, 'server-status') === $manual1, 'next enabled status provider is selected');
$store->setEnabled($pelican1, true);

$store->delete(1, $manual1);
$check($store->priorities(1) === [1, 2, 3], 'delete normalizes remaining priorities');
$manual3 = $store->create(1, 'manual', ['server-status']);
$check($store->priorities(1) === [1, 2, 3, 4], 'create after delete appends correctly');
$check($manual3 !== $manual2, 'duplicate provider type creates a distinct instance');

try {
    $store->delete(1, $otherServer);
    $check(false, 'wrong server ownership rejected');
} catch (RuntimeException) {
    $check(true, 'wrong server ownership rejected');
}

$store->corruptPositions(1, 0);
$store->normalize(1);
$check($store->priorities(1) === [1, 2, 3, 4], 'legacy duplicate zero positions normalize');

$reloaded = $store->reload();
$check($reloaded->ids(1) === $store->ids(1), 'priority persists after model reload');
$check($reloaded->priorities(1) === [1, 2, 3, 4], 'priority persists after restart-style serialization');
$check($reloaded->resolve(1, 'metrics') === $store->resolve(1, 'metrics'), 'capability priority persists after restart');

if ($failures !== []) {
    fwrite(STDERR, "FAILED ".count($failures)." / {$tests}\n - ".implode("\n - ", $failures)."\n");
    exit(1);
}

echo "PASS {$tests} provider CRUD behavior checks\n";
