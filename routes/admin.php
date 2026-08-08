<?php

use Azuriom\Plugin\GamingHubCore\Controllers\Admin\DirectorySettingsController;
use Azuriom\Plugin\GamingHubCore\Controllers\Admin\GameController;
use Azuriom\Plugin\GamingHubCore\Controllers\Admin\ProviderController;
use Azuriom\Plugin\GamingHubCore\Controllers\Admin\PublicDataSettingsController;
use Azuriom\Plugin\GamingHubCore\Controllers\Admin\ServerController;
use Azuriom\Plugin\GamingHubCore\Controllers\Admin\ServerPublicDataController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:gaminghub.games.view')->group(function (): void {
    Route::get('/games', [GameController::class, 'index'])->name('games.index');
});

Route::middleware('can:gaminghub.games.manage')->group(function (): void {
    Route::get('/games/create', [GameController::class, 'create'])->name('games.create');
    Route::post('/games', [GameController::class, 'store'])->name('games.store');
    Route::get('/games/{game}/edit', [GameController::class, 'edit'])->name('games.edit');
    Route::put('/games/{game}', [GameController::class, 'update'])->name('games.update');
    Route::patch('/games/{game}/toggle', [GameController::class, 'toggle'])->name('games.toggle');
    Route::patch('/games/{game}/move/{direction}', [GameController::class, 'move'])
        ->whereIn('direction', ['up', 'down'])
        ->name('games.move');
    Route::delete('/games/{game}', [GameController::class, 'destroy'])->name('games.destroy');
});

Route::middleware('can:gaminghub.settings.manage')->group(function (): void {
    Route::get('/settings/directory', [DirectorySettingsController::class, 'edit'])->name('settings.directory.edit');
    Route::put('/settings/directory', [DirectorySettingsController::class, 'update'])->name('settings.directory.update');
    Route::get('/settings/public-data', [PublicDataSettingsController::class, 'edit'])->name('settings.public-data.edit');
    Route::put('/settings/public-data', [PublicDataSettingsController::class, 'update'])->name('settings.public-data.update');
    Route::get('/games/{game}/servers/{server}/public-data', [ServerPublicDataController::class, 'edit'])
        ->name('games.servers.public-data.edit');
    Route::put('/games/{game}/servers/{server}/public-data', [ServerPublicDataController::class, 'update'])
        ->name('games.servers.public-data.update');
});

Route::middleware('can:gaminghub.servers.view')->group(function (): void {
    Route::get('/games/{game}/servers', [ServerController::class, 'index'])->name('games.servers.index');
});

Route::middleware('can:gaminghub.servers.manage')->group(function (): void {
    Route::get('/games/{game}/servers/create', [ServerController::class, 'create'])->name('games.servers.create');
    Route::post('/games/{game}/servers', [ServerController::class, 'store'])->name('games.servers.store');
    Route::get('/games/{game}/servers/{server}/edit', [ServerController::class, 'edit'])->name('games.servers.edit');
    Route::put('/games/{game}/servers/{server}', [ServerController::class, 'update'])->name('games.servers.update');
    Route::patch('/games/{game}/servers/{server}/toggle', [ServerController::class, 'toggle'])->name('games.servers.toggle');
    Route::patch('/games/{game}/servers/{server}/duplicate', [ServerController::class, 'duplicate'])->name('games.servers.duplicate');
    Route::patch('/games/{game}/servers/{server}/move/{direction}', [ServerController::class, 'move'])
        ->whereIn('direction', ['up', 'down'])
        ->name('games.servers.move');
    Route::delete('/games/{game}/servers/{server}', [ServerController::class, 'destroy'])->name('games.servers.destroy');
});

Route::middleware('can:gaminghub.providers.view')->group(function (): void {
    Route::get('/games/{game}/servers/{server}/providers', [ProviderController::class, 'index'])
        ->name('games.servers.providers.index');
});

Route::middleware('can:gaminghub.providers.manage')->group(function (): void {
    Route::get('/games/{game}/servers/{server}/providers/create', [ProviderController::class, 'create'])
        ->name('games.servers.providers.create');
    Route::post('/games/{game}/servers/{server}/providers', [ProviderController::class, 'store'])
        ->name('games.servers.providers.store');
    Route::get('/games/{game}/servers/{server}/providers/{provider}/edit', [ProviderController::class, 'edit'])
        ->name('games.servers.providers.edit');
    Route::put('/games/{game}/servers/{server}/providers/{provider}', [ProviderController::class, 'update'])
        ->name('games.servers.providers.update');
    Route::patch('/games/{game}/servers/{server}/providers/{provider}/toggle', [ProviderController::class, 'toggle'])
        ->name('games.servers.providers.toggle');
    Route::patch('/games/{game}/servers/{server}/providers/{provider}/move/{direction}', [ProviderController::class, 'move'])
        ->whereIn('direction', ['up', 'down'])
        ->name('games.servers.providers.move');
    Route::delete('/games/{game}/servers/{server}/providers/{provider}', [ProviderController::class, 'destroy'])
        ->name('games.servers.providers.destroy');
});
