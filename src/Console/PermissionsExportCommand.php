<?php

namespace Squareetlabs\LaravelSimplePermissions\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Exception;

class PermissionsExportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:export {--format=json : Export format (json, yaml)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export permissions, roles, and groups to a file';

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle(): int
    {
        $format = $this->option('format');

        $permissionModel = SimplePermissions::model('permission');
        $roleModel = SimplePermissions::model('role');
        
        $groupsEnabled = Config::get('simple-permissions.features.groups.enabled', true)
            && Schema::hasTable('groups')
            && Schema::hasTable('group_user');

        $permissions = $permissionModel::all();

        $roles = $roleModel::with('permissions')->get()->map(function ($role) {
            return [
                'code' => $role->code,
                'name' => $role->name,
                'description' => $role->description,
                'permissions' => $role->permissions->pluck('code')->toArray(),
            ];
        });

        $groups = [];
        if ($groupsEnabled) {
            $groupModel = SimplePermissions::model('group');
            $groups = $groupModel::with('permissions')->get()->map(function ($group) {
                return [
                    'code' => $group->code,
                    'name' => $group->name,
                    'permissions' => $group->permissions->pluck('code')->toArray(),
                ];
            })->toArray();
        }

        $data = [
            'permissions' => $permissions->pluck('code')->toArray(),
            'roles' => $roles->toArray(),
            'groups' => $groups,
            'exported_at' => now()->toIso8601String(),
        ];

        $filename = "permissions_export_" . now()->format('Y-m-d_His') . ".{$format}";

        if ($format === 'json') {
            $content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } elseif ($format === 'yaml') {
            if (!function_exists('yaml_emit')) {
                $this->error("YAML extension is not installed.");
                return self::FAILURE;
            }
            $content = yaml_emit($data);
        } else {
            $this->error("Unsupported format: {$format}");
            return self::FAILURE;
        }

        file_put_contents($filename, $content);

        $this->info("Permissions exported to: {$filename}");
        $groupsCount = $groupsEnabled ? count($groups) : 0;
        $this->line("Exported {$permissions->count()} permissions, {$roles->count()} roles" . ($groupsEnabled ? ", and {$groupsCount} groups" : " (groups disabled)") . ".");

        return self::SUCCESS;
    }
}

