<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX = 'gaminghub_provider_server_position_unique';

    public function up(): void
    {
        DB::transaction(function (): void {
            $serverIds = DB::table('gaminghub_provider_instances')
                ->whereNotNull('server_id')
                ->select('server_id')
                ->distinct()
                ->orderBy('server_id')
                ->pluck('server_id');

            foreach ($serverIds as $serverId) {
                $providerIds = DB::table('gaminghub_provider_instances')
                    ->where('server_id', $serverId)
                    ->orderBy('position')
                    ->orderBy('id')
                    ->pluck('id')
                    ->map(static fn ($id): int => (int) $id)
                    ->values();

                $timestamp = now();

                // Temporary negative values make the migration compatible
                // with a unique index and with arbitrary legacy positions.
                foreach ($providerIds as $offset => $providerId) {
                    DB::table('gaminghub_provider_instances')
                        ->where('server_id', $serverId)
                        ->where('id', $providerId)
                        ->update([
                            'position' => -($offset + 1),
                            'updated_at' => $timestamp,
                        ]);
                }

                foreach ($providerIds as $offset => $providerId) {
                    DB::table('gaminghub_provider_instances')
                        ->where('server_id', $serverId)
                        ->where('id', $providerId)
                        ->update([
                            'position' => $offset + 1,
                            'updated_at' => $timestamp,
                        ]);
                }
            }
        }, 3);

        Schema::table('gaminghub_provider_instances', function (Blueprint $table): void {
            $table->unique(['server_id', 'position'], self::INDEX);
        });
    }

    public function down(): void
    {
        Schema::table('gaminghub_provider_instances', function (Blueprint $table): void {
            $table->dropUnique(self::INDEX);
        });
    }
};
