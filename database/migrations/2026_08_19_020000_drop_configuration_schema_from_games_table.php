<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Removed for the same reason as Instance/ConfigurationPreset: nothing in
 * core actually consumed a Game's configuration_schema to change how a
 * server behaves, and the only UI that read/wrote it (Instance's dynamic
 * config form) is gone. Long-term plan: a Game Extension ships its own
 * settings shape and presets — that's extension work, not a core column.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('configuration_schema');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->json('configuration_schema')->nullable();
        });
    }
};
