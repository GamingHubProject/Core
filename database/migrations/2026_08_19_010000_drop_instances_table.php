<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Instance had no real purpose: a named JSON blob attached to a Server
 * that nothing read or wrote back to an actual running server — no
 * Connector ever pushes configuration, only reads status. Long-term plan
 * per CLAUDE.md: Game Extensions own their own settings/presets later, as
 * extension work, not a core concept. Removed rather than left unused.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('instances');
    }

    public function down(): void
    {
        Schema::create('instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('configuration')->nullable();
            $table->timestamps();
        });
    }
};
