<?php

namespace Squareetlabs\LaravelSimplePermissions\Console;

use Illuminate\Console\Command;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Exception;

class PermissionsListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all roles';

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle(): int
    {
        $roleModel = SimplePermissions::model('role');
        $roles = $roleModel::with('permissions')->get();

        if ($roles->isEmpty()) {
            $this->info('No roles found.');
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Code', 'Name', 'Permissions', 'Created At'],
            $roles->map(function ($role) {
                return [
                    $role->id,
                    $role->code,
                    $role->name,
                    $role->permissions->count(),
                    $role->created_at->format('Y-m-d H:i:s'),
                ];
            })->toArray()
        );

        return self::SUCCESS;
    }
}

