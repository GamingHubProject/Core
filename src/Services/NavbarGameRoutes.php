<?php
namespace Azuriom\Plugin\GamingHubCore\Services;
use Azuriom\Plugin\GamingHubCore\Models\Game; use Azuriom\Plugin\GamingHubCore\Models\Server; use Illuminate\Support\Facades\Schema; use Illuminate\Support\Str;
final class NavbarGameRoutes {
 public const GAME_PREFIX='gaming-hub-core.games.game.'; public const SERVER_PREFIX='gaming-hub-core.games.server.';
 public function enabledGames(){if(!Schema::hasTable((new Game)->getTable()))return collect();return Game::query()->enabled()->ordered()->get(['id','uuid','slug','name']);}
 public function enabledServers(){if(!Schema::hasTable((new Server)->getTable()))return collect();return Server::query()->with('game:id,uuid,slug,name,enabled')->whereHas('game',fn($q)=>$q->enabled())->enabled()->public()->ordered()->get(['id','game_id','uuid','slug','name']);}
 public function gameRouteName(Game $g):string{return self::GAME_PREFIX.$this->key($g->uuid).'.show';} public function routeName(Game $g):string{return $this->gameRouteName($g);} public function serverRouteName(Server $s):string{return self::SERVER_PREFIX.$this->key($s->uuid).'.show';}
 public function descriptions():array{$games=$this->enabledGames()->mapWithKeys(fn(Game $g)=>[$this->gameRouteName($g)=>trans('gaming-hub-core::public.navbar.game',['game'=>$g->name])]);$servers=$this->enabledServers()->mapWithKeys(fn(Server $s)=>[$this->serverRouteName($s)=>trans('gaming-hub-core::public.navbar.server',['game'=>$s->game->name,'server'=>$s->name])]);return $games->merge($servers)->all();}
 private function key(string $uuid):string{return Str::lower(str_replace('-','',$uuid));}
}
