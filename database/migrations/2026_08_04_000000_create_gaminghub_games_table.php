<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gaminghub_games', function (Blueprint $table): void {
            $table->increments('id');
            $table->uuid('uuid')->unique();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('short_name', 64);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('icon_media_id')->nullable();
            $table->unsignedBigInteger('cover_media_id')->nullable();
            $table->string('accent_color', 7)->default('#6c5ce7');
            $table->boolean('enabled')->default(true)->index();
            $table->integer('sort_order')->default(0)->index();
            $table->timestamps();

            $table->index(['enabled', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gaminghub_games');
    }
};
