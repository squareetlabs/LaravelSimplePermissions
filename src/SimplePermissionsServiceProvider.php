<?php

namespace Squareetlabs\LaravelSimplePermissions;

use Exception;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Squareetlabs\LaravelSimplePermissions\Exceptions\AuditTableMissingException;
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
            __DIR__ . '/../database/migrations/create_abilities_table.php' => database_path('migrations/2019_12_14_000006_create_abilities_table.php'),
            __DIR__ . '/../database/migrations/create_entity_ability_table.php' => database_path('migrations/2019_12_14_000006_create_entity_ability_table.php'),
            __DIR__ . '/../database/migrations/create_groups_table.php' => database_path('migrations/2019_12_14_000008_create_groups_table.php'),
            __DIR__ . '/../database/migrations/create_group_user_table.php' => database_path('migrations/2019_12_14_000009_create_group_user_table.php'),
            __DIR__ . '/../database/migrations/create_entity_permission_table.php' => database_path('migrations/2019_12_14_000010_create_entity_permission_table.php'),
        ];

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
            Console\SyncPermissionsCommand::class,
            Console\ClearCacheCommand::class,
            Console\ExportPermissionsCommand::class,
            Console\ImportPermissionsCommand::class,
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
     * @return void
     * @throws AuditTableMissingException
     */
    protected function validateAuditConfiguration(): void
    {
        if (!Config::get('simple-permissions.audit.enabled')) {
            return;
        }

        // No validar durante migraciones o instalación
        // La validación real se hace en AuditService cuando se intenta usar
        if ($this->app->runningInConsole()) {
            $command = $this->app->runningUnitTests() ? null : ($_SERVER['argv'][1] ?? null);

            // Saltar validación durante migraciones
            if (in_array($command, ['migrate', 'migrate:fresh', 'migrate:refresh', 'migrate:reset', 'migrate:rollback', 'migrate:status'])) {
                return;
            }
        }

        // Validar que la tabla existe si la auditoría está habilitada
        try {
            if (!Schema::hasTable('audit_logs')) {
                throw new AuditTableMissingException();
            }
        } catch (\Exception $e) {
            // Si hay un error de conexión a la BD, no validar aún
            // (puede ser que la BD aún no esté configurada)
            if (str_contains($e->getMessage(), 'Connection') || str_contains($e->getMessage(), 'SQLSTATE')) {
                return;
            }
            throw $e;
        }
    }
}
