<?php
namespace Azuriom\Plugin\GamingHubCore\Controllers\Admin;
use Azuriom\Http\Controllers\Controller; use Azuriom\Plugin\GamingHubCore\Http\Requests\SaveDirectorySettingsRequest; use Azuriom\Plugin\GamingHubCore\Settings\GameDirectorySettings; use Azuriom\Plugin\GamingHubCore\Settings\GamePageSettings; use Illuminate\Http\RedirectResponse; use Illuminate\View\View;
class DirectorySettingsController extends Controller {
 public function edit(GameDirectorySettings $directory,GamePageSettings $gamePage):View{return view('gaming-hub-core::admin.settings.directory',['settings'=>$directory->all(),'gamePage'=>$gamePage->all()]);}
 public function update(SaveDirectorySettingsRequest $request,GameDirectorySettings $directory,GamePageSettings $gamePage):RedirectResponse{$values=$request->validated();$directory->save($values);$gamePage->save($values);return back()->with('success',trans('gaming-hub-core::admin.settings.saved'));}
}
