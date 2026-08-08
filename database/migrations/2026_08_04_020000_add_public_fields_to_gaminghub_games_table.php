<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gaminghub_games', function (Blueprint $table): void {
            $table->string('short_description', 500)->nullable();
            $table->text('long_description')->nullable();
            $table->string('icon_url', 2048)->nullable();
            $table->string('banner_url', 2048)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('gaminghub_games', function (Blueprint $table): void {
            $table->dropColumn(['short_description', 'long_description', 'icon_url', 'banner_url']);
        });
    }
};
