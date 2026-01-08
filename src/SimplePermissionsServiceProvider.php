<?php

namespace Squareetlabs\LaravelSimplePermissions;

use Exception;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Squareetlabs\LaravelSimplePermissions\Support\Services\SimplePermissionsService;
use Squareetlabs\LaravelSimplePermissions\Middleware\Ability as AbilityMiddleware;
use Squareetlabs\LaravelSimplePermissions\Middleware\Permission as PermissionMiddleware;
use Squareetlabs\LaravelSimplePermissions\Middleware\Role as RoleMiddleware;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SimplePermissionsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/simple-permissions.php', 'simple-permissions');
    }

    /**
     * Bootstrap any application services.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'simple-permissions');

        $this->configureCommands();
        $this->configurePublishing();
        $this->registerFacades();
        $this->registerMiddlewares();
        $this->registerBladeDirectives();
        $this->validateAuditConfiguration();
    }

    /**
     * Configure publishing for the package.
     */
    protected function configurePublishing(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $migrations = [
            __DIR__ . '/../database/migrations/create_permissions_table.php' => database_path('migrations/2019_12_14_000002_create_permissions_table.php'),
            __DIR__ . '/../database/migrations/create_roles_table.php' => database_path('migrations/2019_12_14_000003_create_roles_table.php'),
            __DIR__ . '/../database/migrations/create_role_user_table.php' => database_path('migrations/2019_12_14_000004_create_role_user_table.php'),
            __DIR__ . '/../database/migrations/create_entity_permission_table.php' => database_path('migrations/2019_12_14_000005_create_entity_permission_table.php'),
        ];

        // Add abilities migrations if enabled
        if (Config::get('simple-permissions.features.abilities.enabled', true)) {
            $migrations[__DIR__ . '/../database/migrations/create_abilities_table.php'] = database_path('migrations/2019_12_14_000006_create_abilities_table.php');
            $migrations[__DIR__ . '/../database/migrations/create_entity_ability_table.php'] = database_path('migrations/2019_12_14_000007_create_entity_ability_table.php');
        }

        // Add groups migrations if enabled
        if (Config::get('simple-permissions.features.groups.enabled', true)) {
            $migrations[__DIR__ . '/../database/migrations/create_groups_table.php'] = database_path('migrations/2019_12_14_000008_create_groups_table.php');
            $migrations[__DIR__ . '/../database/migrations/create_group_user_table.php'] = database_path('migrations/2019_12_14_000009_create_group_user_table.php');
        }

        // Add audit logs migration (optional, but always published - table creation is handled by AuditService)
        $migrations[__DIR__ . '/../database/migrations/create_audit_logs_table.php'] = database_path('migrations/2019_12_14_000011_create_audit_logs_table.php');

        // Add performance indexes migration
        $migrations[__DIR__ . '/../database/migrations/add_performance_indexes.php'] = database_path('migrations/2019_12_14_000014_add_performance_indexes.php');

        $this->publishes([
            __DIR__ . '/../config/simple-permissions.php' => config_path('simple-permissions.php')
        ], 'simple-permissions-config');

        $this->publishes($migrations, 'simple-permissions-migrations');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/simple-permissions')
        ], 'simple-permissions-views');
    }

    /**
     * Configure the commands offered by the application.
     */
    protected function configureCommands(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Console\InstallCommand::class,
            Console\MakePolicyCommand::class,
            Console\PermissionsListCommand::class,
            Console\PermissionsShowCommand::class,
            Console\PermissionsCommand::class,
            Console\PermissionsSyncCommand::class,
            Console\PermissionsClearCacheCommand::class,
            Console\PermissionsExportCommand::class,
            Console\PermissionsImportCommand::class,
        ]);
    }

    /**
     * Register the models offered by the application.
     *
     * @throws Exception
     */
    protected function registerFacades(): void
    {
        $this->app->singleton('simple-permissions', static function () {
            return new SimplePermissionsService();
        });
    }

    /**
     * Register the middlewares automatically.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function registerMiddlewares(): void
    {
        if (!$this->app['config']->get('simple-permissions.middleware.register')) {
            return;
        }

        $middlewares = [
            'ability' => AbilityMiddleware::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
        ];

        foreach ($middlewares as $key => $class) {
            $this->app['router']->aliasMiddleware($key, $class);
        }
    }

    /**
     * Register Blade directives for permissions.
     *
     * @return void
     */
    protected function registerBladeDirectives(): void
    {
        Blade::if('role', function ($role) {
            return auth()->user()?->hasRole($role) ?? false;
        });

        Blade::if('permission', function ($permission) {
            return auth()->user()?->hasPermission($permission) ?? false;
        });

        Blade::if('ability', function ($ability, $model) {
            return auth()->user()?->hasAbility($ability, $model) ?? false;
        });
    }

    /**
     * Validate audit configuration.
     *
     * Note: We don't validate the table existence here to avoid blocking migrations.
     * The AuditService will check if the table exists before attempting to log,
     * and will silently skip logging if the table doesn't exist (e.g., during migrations).
     *
     * @return void
     */
    protected function validateAuditConfiguration(): void
    {
        // Validation is done at runtime in AuditService when attempting to log
        // This prevents blocking migrations or installation when audit is enabled
        // but the table hasn't been created yet
    }
}
