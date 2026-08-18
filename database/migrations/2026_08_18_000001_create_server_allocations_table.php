<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per Pelican allocation (ip:port pair) a server has, synced by
 * full replace on every poll tick — these are entirely Pelican-owned with
 * no locally-editable state of their own, so there's nothing worth
 * diffing/preserving across syncs. external_id is Pelican's own
 * allocation id, kept only so a future diffing sync (if ever needed) has
 * something stable to match on; nothing reads it today.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('external_id')->nullable();
            $table->string('ip');
            $table->string('ip_alias')->nullable();
            $table->unsignedInteger('port');
            $table->boolean('is_default')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_allocations');
    }
};
