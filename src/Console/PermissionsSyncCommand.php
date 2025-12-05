<?php

namespace Squareetlabs\LaravelSimplePermissions\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Exception;

class PermissionsSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync permissions from config file to database';

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle(): int
    {
        $permissions = Config::get('simple-permissions.default_permissions');

        if (empty($permissions)) {
            $this->warn('No default permissions found in config. Add "default_permissions" to config/simple-permissions.php');
            return self::SUCCESS;
        }

        $permissionModel = SimplePermissions::model('permission');

        foreach ($permissions as $code) {
            $permissionModel::firstOrCreate([
                'code' => $code,
            ]);
        }

        $this->info('Permissions synced successfully!');
        return self::SUCCESS;
    }
}

