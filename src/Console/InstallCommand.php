<?php

namespace Squareetlabs\LaravelSimplePermissions\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the simple-permissions package migrations and creates additional directories';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        if (!$this->confirm('Do you wish to continue?')) {
            return;
        }

        // Publish...
        $this->call('vendor:publish', ['--tag' => 'simple-permissions-config', '--force' => true]);
        $this->call('vendor:publish', ['--tag' => 'simple-permissions-migrations', '--force' => true]);
        // $this->call('vendor:publish', ['--tag' => 'simple-permissions-views', '--force' => true]); // Views not currently used

        // Directories...
        // (new Filesystem())->ensureDirectoryExists(app_path('Actions/Permissions')); // Actions not currently used
        // (new Filesystem())->ensureDirectoryExists(app_path('Events'));
        (new Filesystem())->ensureDirectoryExists(app_path('Policies'));

        // Actions...
        // (new Filesystem())->copyDirectory(__DIR__.'/../../stubs/app/Actions/Permissions', app_path('Actions/Permissions/'));

        // Policies...
        // (new Filesystem())->copyDirectory(__DIR__.'/../../stubs/app/Policies', app_path('Policies'));

        // Controllers...
        // (new Filesystem())->copyDirectory(__DIR__.'/../../stubs/app/Controllers', app_path('Http/Controllers/'));

        $this->info('All done. Have a nice journey.');
    }
}
