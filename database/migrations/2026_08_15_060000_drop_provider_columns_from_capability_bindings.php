<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * priority and source_provider_id were an earlier attempt to make
 * CapabilityBinding the provider priority stack itself. That was the wrong
 * shape — the stack lives on Provider.priority now, walked directly by
 * CapabilityGateway. CapabilityBinding goes back to being a single
 * admin-entered "default" value with no provider bookkeeping of its own.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capability_bindings', function (Blueprint $table) {
            $table->dropColumn(['priority', 'source_provider_id']);
        });
    }

    public function down(): void
    {
        Schema::table('capability_bindings', function (Blueprint $table) {
            $table->unsignedInteger('priority')->default(0);
            $table->unsignedBigInteger('source_provider_id')->nullable();
        });
    }
};
