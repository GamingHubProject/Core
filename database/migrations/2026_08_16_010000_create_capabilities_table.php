<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The catalogue of capability ids the platform knows about — 'id' is the
 * same string every normalizer's capability() and every Provider/
 * CapabilityBinding's 'capability' column already use (e.g.
 * "server-status"), now with a name/description an admin form can show
 * instead of a bare string. This table doesn't decide what's *available*
 * for a given Game (see CapabilityRegistry::forGame()) — it's just the
 * fixed vocabulary.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capabilities', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capabilities');
    }
};
