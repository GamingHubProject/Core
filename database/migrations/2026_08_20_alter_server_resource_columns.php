<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * cpu_current holds Pelican's cpu_absolute value (e.g. 4.469, 78.469 — a
 * fractional percent of one core), but the original migration declared it
 * unsignedBigInteger. Postgres rejects a fractional literal against an
 * integer column outright (unlike MySQL, it won't silently truncate), so
 * every write through ServerFieldMapper threw and the whole row update
 * failed — not just this one column. cpu_percent/memory_percent/
 * disk_percent were already decimal(5,2) and network_rx/network_tx were
 * already bigint (fine for a whole-byte counter), so only cpu_current
 * actually needed correcting.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->decimal('cpu_current', 8, 3)->unsigned()->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->unsignedBigInteger('cpu_current')->nullable()->change();
        });
    }
};
