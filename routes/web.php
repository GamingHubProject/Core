<?php
use Azuriom\Plugin\GamingHubCore\Controllers\GameController; use Illuminate\Support\Facades\Route;
Route::get('/games',[GameController::class,'index'])->name('games.index'); Route::get('/games/{game}/{server}',[GameController::class,'server'])->name('servers.show'); Route::get('/games/{slug}',[GameController::class,'show'])->name('games.show');
