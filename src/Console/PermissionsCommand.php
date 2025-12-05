<?php

namespace Squareetlabs\LaravelSimplePermissions\Console;

use Illuminate\Console\Command;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Exception;

class PermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show all permissions';

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle(): int
    {
        $permissionModel = SimplePermissions::model('permission');

        $permissions = $permissionModel::all();

        if ($permissions->isEmpty()) {
            $this->info("No permissions found.");
            return self::SUCCESS;
        }

        $this->info("All Permissions:");
        $this->table(
            ['ID', 'Code', 'Description', 'Created At'],
            $permissions->map(function ($permission) {
                return [
                    $permission->id,
                    $permission->code,
                    $permission->description,
                    $permission->created_at->format('Y-m-d H:i:s'),
                ];
            })->toArray()
        );

        return self::SUCCESS;
    }
}

