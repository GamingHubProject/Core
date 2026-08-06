<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $serverIds = DB::table('gaminghub_provider_instances')
            ->whereNotNull('server_id')
            ->select('server_id')
            ->distinct()
            ->orderBy('server_id')
            ->pluck('server_id');

        foreach ($serverIds as $serverId) {
            $providers = DB::table('gaminghub_provider_instances')
                ->where('server_id', $serverId)
                ->orderBy('position')
                ->orderBy('id')
                ->get(['id', 'position']);

            foreach ($providers as $position => $provider) {
                if ((int) $provider->position === $position) {
                    continue;
                }

                DB::table('gaminghub_provider_instances')
                    ->where('id', $provider->id)
                    ->where('server_id', $serverId)
                    ->update([
                        'position' => $position,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Normalized positions are valid historical data and are not made ambiguous again.
    }
};
