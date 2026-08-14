<?php

namespace GamingHub\Core\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Core owns domain models, capability decisions, and data normalization
 * only. It never composes UI, applies themes, manages assets, or speaks to
 * connectors — those stay in Platform (Panel/Experience/Theme/Asset systems).
 */
class CoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
