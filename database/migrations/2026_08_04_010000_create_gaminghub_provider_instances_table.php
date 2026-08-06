<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('gaminghub_provider_instances', function(Blueprint $table): void { $table->increments('id'); $table->unsignedInteger('game_id'); $table->string('provider_type',100)->index(); $table->string('name'); $table->boolean('enabled')->default(true)->index(); $table->integer('position')->default(0)->index(); $table->json('configuration'); $table->timestamps(); $table->foreign('game_id')->references('id')->on('gaminghub_games')->cascadeOnDelete(); $table->index(['game_id','enabled','position'],'gaminghub_providers_game_enabled_position'); }); }
 public function down(): void { Schema::dropIfExists('gaminghub_provider_instances'); }
};
