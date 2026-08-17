<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive alongside the existing status/cpu_usage_percent/memory_usage_bytes
 * columns rather than replacing them — ServerFieldMapper keeps populating
 * both so nothing already reading the old columns breaks. limit columns are
 * nullable because Pelican reports 0 for "unlimited", which we translate to
 * null (there's no meaningful percent-of-limit to compute against that).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->unsignedBigInteger('cpu_current')->nullable();
            $table->unsignedBigInteger('cpu_limit')->nullable();
            $table->decimal('cpu_percent', 5, 2)->unsigned()->nullable();
            $table->unsignedBigInteger('memory_current')->nullable();
            $table->unsignedBigInteger('memory_limit')->nullable();
            $table->decimal('memory_percent', 5, 2)->unsigned()->nullable();
            $table->unsignedBigInteger('disk_current')->nullable();
            $table->unsignedBigInteger('disk_limit')->nullable();
            $table->decimal('disk_percent', 5, 2)->unsigned()->nullable();
            $table->unsignedBigInteger('network_rx')->nullable();
            $table->unsignedBigInteger('network_tx')->nullable();
            $table->string('node_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn([
                'cpu_current', 'cpu_limit', 'cpu_percent',
                'memory_current', 'memory_limit', 'memory_percent',
                'disk_current', 'disk_limit', 'disk_percent',
                'network_rx', 'network_tx', 'node_name',
            ]);
        });
    }
};
