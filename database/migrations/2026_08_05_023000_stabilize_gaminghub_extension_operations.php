<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gaminghub_extension_operations', function (Blueprint $table) {
            $table->string('current_stage', 32)->default('queued')->index()->after('result');
            $table->json('events')->nullable()->after('context');
        });

        DB::table('gaminghub_extension_operations')
            ->where('result', 'running')
            ->whereNull('finished_at')
            ->update([
                'result' => 'failed',
                'current_stage' => 'failed',
                'finished_at' => now(),
                'error_category' => 'interrupted',
                'summary' => 'Operation was interrupted before lifecycle tracking was available.',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('gaminghub_extension_operations', function (Blueprint $table) {
            $table->dropIndex(['current_stage']);
            $table->dropColumn(['current_stage', 'events']);
        });
    }
};
