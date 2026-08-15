<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Manual moves into the same priority stack as connector-backed providers
 * instead of living solely in CapabilityBinding - one mental model, one
 * table. 'type' discriminates how CapabilityGateway::probeProvider()
 * interprets a row's config: 'connector' (existing shape - normalizer +
 * call, resolved via connector_instance_id) or 'manual' (a flat
 * admin-entered {capability, value} pair, no external I/O). Defaults to
 * 'connector' so every existing row keeps its current meaning without a
 * data migration. connector_instance_id is already nullable (set in the
 * connector-instance migration) - a manual row simply leaves it null.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->string('type')->default('connector')->after('server_id');
        });
    }

    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
