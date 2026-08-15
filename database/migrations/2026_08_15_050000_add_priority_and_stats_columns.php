<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->unsignedInteger('priority')->default(0)->after('connector_instance_id');
        });

        Schema::table('capability_bindings', function (Blueprint $table) {
            $table->unsignedInteger('priority')->default(0)->after('provider');
            // Soft reference to a Platform-side Provider that owns this binding
            // (same pattern as Provider.connector_instance_id) — lets Platform
            // sync/remove exactly the binding a given Provider created, now that
            // a (capability, subject) pair can have more than one binding.
            $table->unsignedBigInteger('source_provider_id')->nullable()->after('priority');
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->decimal('cpu_usage_percent', 5, 2)->nullable()->after('current_players');
            $table->unsignedBigInteger('memory_usage_bytes')->nullable()->after('cpu_usage_percent');
            $table->string('game_version')->nullable()->after('memory_usage_bytes');
            $table->timestamp('last_polled_at')->nullable()->after('game_version');
        });
    }

    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn('priority');
        });

        Schema::table('capability_bindings', function (Blueprint $table) {
            $table->dropColumn(['priority', 'source_provider_id']);
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn(['cpu_usage_percent', 'memory_usage_bytes', 'game_version', 'last_polled_at']);
        });
    }
};
