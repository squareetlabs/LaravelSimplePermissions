<?php

namespace Squareetlabs\LaravelSimplePermissions\Tests;

use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Squareetlabs\LaravelSimplePermissions\SimplePermissionsServiceProvider;
use Squareetlabs\LaravelSimplePermissions\Tests\Models\User;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [
            SimplePermissionsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        // Configure User model for tests
        config()->set('simple-permissions.models.user', User::class);
    }

    /**
     * Set up the database for testing.
     *
     * @return void
     */
    protected function setUpDatabase(): void
    {
        // Create users table if it doesn't exist
        if (!Schema::hasTable('users')) {
            Schema::create('users', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // Run package migrations
        $migrationPath = __DIR__ . '/../database/migrations';

        // Load migrations manually
        $migrations = [
            'create_permissions_table.php',
            'create_roles_table.php',
            'create_role_user_table.php',
            'create_abilities_table.php',
            'create_entity_ability_table.php',
            'create_groups_table.php',
            'create_group_user_table.php',
            'create_entity_permission_table.php',
            'create_audit_logs_table.php', // Always run in tests
        ];

        // $migrations[] = 'add_performance_indexes.php'; // Check if this still exists or is needed

        foreach ($migrations as $migration) {
            $migrationFile = $migrationPath . '/' . $migration;
            if (file_exists($migrationFile)) {
                $migrationClass = include $migrationFile;
                if ($migrationClass instanceof \Illuminate\Database\Migrations\Migration) {
                    $migrationClass->up();
                }
            }
        }
    }
}
