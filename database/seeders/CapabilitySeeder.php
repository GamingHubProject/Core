<?php

namespace GamingHub\Core\Database\Seeders;

use GamingHub\Core\Models\Capability;
use Illuminate\Database\Seeder;

/**
 * The core capability vocabulary. Core defines the class (it owns the
 * capabilities table); Platform's own DatabaseSeeder decides *when* it
 * runs, same split as NormalizerRegistry's registration.
 */
class CapabilitySeeder extends Seeder
{
    public function run(): void
    {
        $capabilities = [
            ['id' => 'server-status', 'name' => 'Server Status', 'description' => 'Online state, player count, and basic resource usage for a server.'],
            ['id' => 'player-list', 'name' => 'Player List', 'description' => 'Identities of players currently connected to a server.'],
            ['id' => 'player-identities', 'name' => 'Player Identities', 'description' => 'Stable identity info (name, id) for a specific player.'],
            ['id' => 'player-positions', 'name' => 'Player Positions', 'description' => 'In-world position data for connected players (e.g. for a live map).'],
            ['id' => 'game-config', 'name' => 'Game Configuration', 'description' => 'A server\'s current configuration/settings values.'],
        ];

        foreach ($capabilities as $capability) {
            Capability::updateOrCreate(['id' => $capability['id']], $capability);
        }
    }
}
