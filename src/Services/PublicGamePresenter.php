<?php
namespace Azuriom\Plugin\GamingHubCore\Services;
use Azuriom\Plugin\GamingHubCore\Data\PublicGameData; use Azuriom\Plugin\GamingHubCore\Models\Game; use Azuriom\Plugin\GamingHubCore\Models\Server;
final class PublicGamePresenter {
 public function __construct(private readonly PublicServerPresenter $servers){}
 public function directory(Game $game):PublicGameData{return $this->make($game,false);} public function detail(Game $game):PublicGameData{return $this->make($game,true);}
 private function make(Game $game,bool $include):PublicGameData{$server=$game->servers()->with('game')->enabled()->public()->ordered()->first();$status=$server?$this->servers->make($server):null;return new PublicGameData((int)$game->getKey(),$game->slug,$game->name,$game->shortDescriptionForDisplay(),$game->longDescriptionForDisplay(),$game->icon_url,$game->banner_url,(int)$game->sort_order,$status?->status??'unknown',$status?->statusMessage,[]);}
}
