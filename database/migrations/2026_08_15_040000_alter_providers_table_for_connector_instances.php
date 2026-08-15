<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Provider was scaffolded in Milestone 1 with its own type+credentials,
 * duplicating what Platform's ConnectorInstance now owns. Never had an
 * admin UI, never had real records (confirmed before writing this).
 * Redesigned to reference a ConnectorInstance instead — a plain soft
 * reference (no FK constraint, no Eloquent relation defined here), the
 * same pattern CapabilityBinding already uses, since Core must never know
 * about Platform's models.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn(['type', 'credentials']);
            $table->unsignedBigInteger('connector_instance_id')->nullable()->after('server_id');
            $table->json('config')->nullable()->after('connector_instance_id');
        });
    }

    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn(['connector_instance_id', 'config']);
            $table->string('type')->after('server_id');
            $table->text('credentials')->nullable()->after('type');
        });
    }
};
