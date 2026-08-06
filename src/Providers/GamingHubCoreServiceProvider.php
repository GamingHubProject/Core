<?php

namespace Azuriom\Plugin\GamingHubCore\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Extensions\Plugin\PluginManager;
use Azuriom\Models\Permission;
use Azuriom\Plugin\GamingHubCore\Contracts\CapabilityReaderRegistry;
use Azuriom\Plugin\GamingHubCore\Contracts\CapabilityResolver;
use Azuriom\Plugin\GamingHubCore\Contracts\GameRegistry;
use Azuriom\Plugin\GamingHubCore\Contracts\ProviderInstances;
use Azuriom\Plugin\GamingHubCore\Contracts\ProviderTypeRegistry;
use Azuriom\Plugin\GamingHubCore\Contracts\PublicDataPolicyResolver;
use Azuriom\Plugin\GamingHubCore\Contracts\SharedDataGateway;
use Azuriom\Plugin\GamingHubCore\Data\ProviderConfigurationField;
use Azuriom\Plugin\GamingHubCore\Data\ProviderType;
use Azuriom\Plugin\GamingHubCore\Http\Middleware\TraceProviderCreation;
use Azuriom\Plugin\GamingHubCore\Models\ProviderInstance;
use Azuriom\Plugin\GamingHubCore\Navigation\GameNavigation;
use Azuriom\Plugin\GamingHubCore\Navigation\GameNavigationItem;
use Azuriom\Plugin\GamingHubCore\Observers\ProviderInstanceObserver;
use Azuriom\Plugin\GamingHubCore\Readers\ManualServerStatusReader;
use Azuriom\Plugin\GamingHubCore\Services\DefaultCapabilityResolver;
use Azuriom\Plugin\GamingHubCore\Services\DefaultPublicDataPolicyResolver;
use Azuriom\Plugin\GamingHubCore\Services\DefaultSharedDataGateway;
use Azuriom\Plugin\GamingHubCore\Services\EloquentGameRegistry;
use Azuriom\Plugin\GamingHubCore\Services\EloquentProviderInstances;
use Azuriom\Plugin\GamingHubCore\Services\InMemoryCapabilityReaderRegistry;
use Azuriom\Plugin\GamingHubCore\Services\InMemoryProviderTypeRegistry;
use Azuriom\Plugin\GamingHubCore\Services\NavbarGameRoutes;
use Azuriom\Plugin\GamingHubCore\Services\ProviderCreationTrace;
use Azuriom\Plugin\GamingHubCore\Services\ProviderLifecycleContext;
use Azuriom\Plugin\GamingHubCore\Services\ProviderLifecycleManager;
use Azuriom\Plugin\GamingHubCore\Services\SharedDataCache;
use Azuriom\Plugin\GamingHubCore\Settings\GameDirectorySettings;
use Azuriom\Plugin\GamingHubCore\Settings\GamePageSettings;
use Azuriom\Plugin\GamingHubCore\Settings\PublicDataSettings;
use Azuriom\Plugin\GamingHubCore\Validation\ProviderConfigurationValidator;
use Azuriom\Plugin\GamingHubCore\View\Components\GameBadge;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class GamingHubCoreServiceProvider extends BasePluginServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            plugin_path($this->plugin->id.'/config/providers.php'),
            'gaming-hub-core.providers',
        );

        $this->app->singleton(CapabilityResolver::class, DefaultCapabilityResolver::class);
        $this->app->alias(CapabilityResolver::class, 'gaminghub.capabilities');
        $this->app->singleton(CapabilityReaderRegistry::class, InMemoryCapabilityReaderRegistry::class);
        $this->app->alias(CapabilityReaderRegistry::class, 'gaminghub.capability-readers');
        $this->app->singleton(PublicDataPolicyResolver::class, DefaultPublicDataPolicyResolver::class);
        $this->app->alias(PublicDataPolicyResolver::class, 'gaminghub.public-data-policy');
        $this->app->singleton(SharedDataCache::class, fn ($app) => new SharedDataCache($app['cache.store']));
        $this->app->singleton(ProviderLifecycleContext::class);
        $this->app->singleton(ProviderCreationTrace::class);
        $this->app->singleton(ProviderLifecycleManager::class);
        $this->app->singleton(SharedDataGateway::class, DefaultSharedDataGateway::class);
        $this->app->alias(SharedDataGateway::class, 'gaminghub.shared-data');
        $this->app->singleton(GameRegistry::class, EloquentGameRegistry::class);
        $this->app->alias(GameRegistry::class, 'gaminghub.games');
        $this->app->singleton(ProviderTypeRegistry::class, InMemoryProviderTypeRegistry::class);
        $this->app->alias(ProviderTypeRegistry::class, 'gaminghub.provider-types');
        $this->app->singleton(ProviderConfigurationValidator::class);
        $this->app->singleton(ProviderInstances::class, EloquentProviderInstances::class);
        $this->app->alias(ProviderInstances::class, 'gaminghub.providers');
        $this->app->singleton(GameDirectorySettings::class);
        $this->app->singleton(GamePageSettings::class);
        $this->app->singleton(PublicDataSettings::class);
        $this->app->singleton(GameNavigation::class);
        $this->app->alias(GameNavigation::class, 'gaminghub.game-navigation');
        $this->app->singleton(NavbarGameRoutes::class);
    }

    public function boot(): void
    {
        $this->loadViews();

        // Private runtime namespace: themes must style semantic classes, not replace Core's structure.
        View::addNamespace('gaming-hub-core-runtime-v043', plugin_path($this->plugin->id.'/resources/views/runtime'));
        View::addNamespace('gaming-hub-core-runtime-v044', plugin_path($this->plugin->id.'/resources/views/runtime'));
        View::addNamespace('gaming-hub-core-runtime-v050', plugin_path($this->plugin->id.'/resources/views/runtime'));

        $this->loadTranslations();
        $this->loadMigrations();
        $this->registerRouteDescriptions();
        $this->registerAdminNavigation();
        $this->app['router']->pushMiddlewareToGroup('web', TraceProviderCreation::class);
        ProviderInstance::observe(ProviderInstanceObserver::class);
        $this->registerBuiltInProviderTypes();
        $this->registerBuiltInCapabilityReaders();
        $this->registerBuiltInGameNavigation();

        Permission::registerPermissions([
            'gaminghub.games.view' => 'gaming-hub-core::admin.permissions.games_view',
            'gaminghub.games.manage' => 'gaming-hub-core::admin.permissions.games_manage',
            'gaminghub.servers.view' => 'gaming-hub-core::admin.permissions.servers_view',
            'gaminghub.servers.manage' => 'gaming-hub-core::admin.permissions.servers_manage',
            'gaminghub.providers.view' => 'gaming-hub-core::admin.permissions.providers_view',
            'gaminghub.providers.manage' => 'gaming-hub-core::admin.permissions.providers_manage',
            'gaminghub.settings.manage' => 'gaming-hub-core::admin.permissions.settings_manage',
            'gaminghub.public-data.manage' => 'gaming-hub-core::admin.permissions.settings_manage',
        ]);

        Blade::component('game-badge', GameBadge::class);
    }

    private function registerBuiltInProviderTypes(): void
    {
        $this->app->make(ProviderTypeRegistry::class)->register(new ProviderType(
            id: 'manual',
            name: trans('gaming-hub-core::admin.manual.name'),
            description: trans('gaming-hub-core::admin.manual.description'),
            pluginId: 'gaming-hub-core',
            pluginName: 'Gaming Hub Core',
            capabilities: ['server-status'],
            fields: [
                new ProviderConfigurationField(
                    'status',
                    trans('gaming-hub-core::admin.manual.status'),
                    'select',
                    true,
                    ['online', 'offline', 'maintenance', 'unknown'],
                ),
                new ProviderConfigurationField(
                    'display_message',
                    trans('gaming-hub-core::admin.manual.display_message'),
                    'string',
                    false,
                    [],
                    500,
                ),
            ],
            publicAttributionLabel: trans('gaming-hub-core::admin.manual.name'),
        ));
    }

    private function registerBuiltInCapabilityReaders(): void
    {
        $this->app->make(CapabilityReaderRegistry::class)
            ->register('manual', 'server-status', ManualServerStatusReader::class);
    }

    private function registerBuiltInGameNavigation(): void
    {
        $navigation = $this->app->make(GameNavigation::class);
        $navigation->register(new GameNavigationItem(
            id: 'overview',
            label: trans('gaming-hub-core::public.navigation.overview'),
            url: static fn ($game): string => route('gaming-hub-core.games.show', $game->slug).'#overview',
            icon: 'bi bi-house',
            order: 10,
            active: static fn ($game, $request): bool => $request->routeIs('gaming-hub-core.games.show')
                || str_starts_with((string) $request->route()?->getName(), 'gaming-hub-core.games.game.'),
        ));
        $navigation->register(new GameNavigationItem(
            id: 'servers',
            label: trans('gaming-hub-core::public.navigation.servers'),
            url: static fn ($game): string => route('gaming-hub-core.games.show', $game->slug).'#servers',
            icon: 'bi bi-hdd-rack',
            order: 20,
            visible: static fn ($game): bool => $game->servers()->enabled()->public()->exists(),
        ));
    }

    /**
     * @return array<string, string>
     */
    protected function routeDescriptions(): array
    {
        return [
            'gaming-hub-core.games.index' => trans('gaming-hub-core::public.navbar.games'),
            ...$this->app->make(NavbarGameRoutes::class)->descriptions(),
        ];
    }

    protected function adminNavigation(): array
    {
        return [
            'gaming-hub-core' => [
                'name' => trans('gaming-hub-core::admin.nav.title'),
                'type' => 'dropdown',
                'icon' => 'bi bi-controller',
                'route' => 'gaming-hub-core.admin.*',
                'items' => [
                    'gaming-hub-core.admin.games.index' => [
                        'name' => trans('gaming-hub-core::admin.nav.games'),
                        'permission' => 'gaminghub.games.view',
                    ],
                    'gaming-hub-core.admin.settings.directory.edit' => [
                        'name' => trans('gaming-hub-core::admin.nav.directory_settings'),
                        'permission' => 'gaminghub.settings.manage',
                    ],
                    'gaming-hub-core.admin.settings.public-data.edit' => [
                        'name' => trans('gaming-hub-core::admin.nav.public_data'),
                        'permission' => 'gaminghub.settings.manage',
                    ],
                    ...$this->managerNavigationItem(),
                ],
            ],
        ];
    }

    /**
     * Core has no runtime dependency on Gaming Hub Manager. The link exists
     * only while Azuriom reports the Manager plugin as enabled, which also
     * guarantees that its administration routes can be loaded normally.
     *
     * @return array<string, array{name: string, permission: string}>
     */
    private function managerNavigationItem(): array
    {
        if (! class_exists(PluginManager::class)) {
            return [];
        }

        try {
            if (! $this->app->make(PluginManager::class)->isEnabled('gaming-hub-manager')
                || ! Route::has('gaming-hub-manager.admin.overview')) {
                return [];
            }
        } catch (\Throwable) {
            return [];
        }

        return [
            'gaming-hub-manager.admin.overview' => [
                'name' => trans('gaming-hub-core::admin.nav.package_manager'),
                'permission' => 'gaminghub.manager.view',
            ],
        ];
    }
}
