<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
return new class extends Migration {
 public function up(): void {
  Schema::create('gaminghub_servers', function(Blueprint $table): void {
   $table->increments('id'); $table->unsignedInteger('game_id'); $table->uuid('uuid')->unique();
   $table->string('name'); $table->string('slug'); $table->string('short_description',500)->nullable();
   $table->text('long_description')->nullable(); $table->string('icon_url',2048)->nullable(); $table->string('banner_url',2048)->nullable();
   $table->boolean('enabled')->default(true)->index(); $table->boolean('public')->default(true)->index(); $table->integer('position')->default(0)->index();
   $table->string('hostname',253)->nullable(); $table->unsignedSmallInteger('display_port')->nullable(); $table->string('join_url',2048)->nullable();
   $table->timestamps(); $table->foreign('game_id')->references('id')->on('gaminghub_games')->cascadeOnDelete();
   $table->unique(['game_id','slug'],'gaminghub_servers_game_slug_unique'); $table->index(['game_id','enabled','public','position'],'gaminghub_servers_public_order');
  });
  Schema::table('gaminghub_provider_instances', function(Blueprint $table): void { $table->unsignedInteger('server_id')->nullable()->after('game_id')->index(); });
  $rows=DB::table('gaminghub_provider_instances')->select('game_id')->distinct()->orderBy('game_id')->get();
  foreach($rows as $row){
   $game=DB::table('gaminghub_games')->where('id',$row->game_id)->first(); if(!$game) continue;
   $serverId=DB::table('gaminghub_servers')->insertGetId(['game_id'=>$row->game_id,'uuid'=>(string)Str::uuid(),'name'=>'Default Server','slug'=>'default','short_description'=>'Migrated provider assignments','enabled'=>true,'public'=>true,'position'=>0,'created_at'=>now(),'updated_at'=>now()]);
   DB::table('gaminghub_provider_instances')->where('game_id',$row->game_id)->whereNull('server_id')->update(['server_id'=>$serverId]);
  }
  Schema::table('gaminghub_provider_instances', function(Blueprint $table): void { $table->foreign('server_id')->references('id')->on('gaminghub_servers')->cascadeOnDelete(); $table->index(['server_id','enabled','position'],'gaminghub_providers_server_enabled_position'); });
 }
 public function down(): void { Schema::table('gaminghub_provider_instances', function(Blueprint $table): void { $table->dropForeign(['server_id']); $table->dropIndex('gaminghub_providers_server_enabled_position'); $table->dropColumn('server_id'); }); Schema::dropIfExists('gaminghub_servers'); }
};
