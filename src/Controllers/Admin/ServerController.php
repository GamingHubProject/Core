<?php
namespace Azuriom\Plugin\GamingHubCore\Controllers\Admin;
use Azuriom\Http\Controllers\Controller; use Azuriom\Plugin\GamingHubCore\Http\Requests\SaveServerRequest; use Azuriom\Plugin\GamingHubCore\Models\Game; use Azuriom\Plugin\GamingHubCore\Models\Server; use Illuminate\Http\RedirectResponse; use Illuminate\Support\Facades\DB; use Illuminate\Support\Str; use Illuminate\View\View;
class ServerController extends Controller {
 public function index(Game $game): View { return view('gaming-hub-core::admin.servers.index',['game'=>$game,'servers'=>$game->servers()->ordered()->get()]); }
 public function create(Game $game): View { return view('gaming-hub-core::admin.servers.create',compact('game')); }
 public function store(SaveServerRequest $r,Game $game): RedirectResponse { $game->servers()->create($r->validated()); return to_route('gaming-hub-core.admin.games.servers.index',$game)->with('success',trans('gaming-hub-core::admin.server_messages.created')); }
 public function edit(Game $game,Server $server): View { $this->owned($game,$server); return view('gaming-hub-core::admin.servers.edit',compact('game','server')); }
 public function update(SaveServerRequest $r,Game $game,Server $server): RedirectResponse { $this->owned($game,$server); $server->update($r->validated()); return to_route('gaming-hub-core.admin.games.servers.index',$game)->with('success',trans('gaming-hub-core::admin.server_messages.updated')); }
 public function toggle(Game $game,Server $server): RedirectResponse { $this->owned($game,$server); $server->update(['enabled'=>!$server->enabled]); return back(); }
 public function duplicate(Game $game,Server $server): RedirectResponse { $this->owned($game,$server); $copy=$server->replicate(['uuid']); $copy->name=$server->name.' Copy'; $base=Str::slug($copy->name)?:'server-copy'; $slug=$base; $i=2; while($game->servers()->where('slug',$slug)->exists())$slug=$base.'-'.$i++; $copy->slug=$slug; $copy->position=(int)$game->servers()->max('position')+1; $copy->save(); return back()->with('success',trans('gaming-hub-core::admin.server_messages.duplicated')); }
 public function move(Game $game,Server $server,string $direction): RedirectResponse { $this->owned($game,$server); DB::transaction(function()use($game,$server,$direction){$items=$game->servers()->ordered()->lockForUpdate()->get();$i=$items->search(fn($x)=>$x->is($server));if($i===false)return;$t=$direction==='up'?$i-1:$i+1;if(!$items->has($t))return;$a=$items->all();[$a[$i],$a[$t]]=[$a[$t],$a[$i]];foreach($a as $pos=>$item)if($item->position!==$pos)$item->update(['position'=>$pos]);}); return back(); }
 public function destroy(Game $game,Server $server): RedirectResponse { $this->owned($game,$server); $server->delete(); return back()->with('success',trans('gaming-hub-core::admin.server_messages.deleted')); }
 private function owned(Game $g,Server $s): void { abort_unless($s->game_id===$g->id,404); }
}
