<?php

namespace Squareetlabs\LaravelSimplePermissions\Console;

use Illuminate\Console\Command;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Exception;

class PermissionsShowCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:show-role {role : The ID or code of the role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show details of a specific role';

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle(): int
    {
        $roleIdentifier = $this->argument('role');
        $roleModel = SimplePermissions::model('role');

        $role = is_numeric($roleIdentifier)
            ? $roleModel::find($roleIdentifier)
            : $roleModel::where('code', $roleIdentifier)->first();

        if (!$role) {
            $this->error("Role not found: {$roleIdentifier}");
            return self::FAILURE;
        }

        $this->info("Role Details:");
        $this->line("ID: {$role->id}");
        $this->line("Code: {$role->code}");
        $this->line("Name: {$role->name}");
        $this->line("Description: {$role->description}");
        $this->line("Created: {$role->created_at->format('Y-m-d H:i:s')}");
        $this->newLine();

        $this->info("Permissions ({$role->permissions()->count()}):");
        $permissions = $role->permissions->pluck('code')->join(', ');
        $this->line("  - {$permissions}");

        return self::SUCCESS;
    }
}

