<?php

namespace Squareetlabs\LaravelSimplePermissions\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Squareetlabs\LaravelSimplePermissions\Support\Helpers\FeatureChecker;
use Exception;

class PermissionsImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:import {--file= : Path to the import file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import permissions, roles, and groups from a file';

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle(): int
    {
        $file = $this->option('file');

        if (!$file || !File::exists($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $content = File::get($file);

        if ($extension === 'json') {
            $data = json_decode($content, true);
        } elseif (in_array($extension, ['yaml', 'yml'])) {
            if (!function_exists('yaml_parse')) {
                $this->error("YAML extension is not installed.");
                return self::FAILURE;
            }
            $data = yaml_parse($content);
        } else {
            $this->error("Unsupported file format: {$extension}");
            return self::FAILURE;
        }

        if (!$data || !isset($data['permissions']) || !isset($data['roles'])) {
            $this->error("Invalid file format. Expected 'permissions' and 'roles' keys.");
            return self::FAILURE;
        }

        $this->info("Importing permissions...");

        $permissionModel = SimplePermissions::model('permission');
        $roleModel = SimplePermissions::model('role');
        
        $groupsEnabled = FeatureChecker::isGroupsEnabled();
        $groupModel = $groupsEnabled ? SimplePermissions::model('group') : null;

        // Import permissions
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            foreach ($data['permissions'] as $permissionCode) {
                $permissionModel::firstOrCreate(['code' => $permissionCode]);
            }
            $this->line("Imported " . count($data['permissions']) . " permissions.");
        }

        // Import roles
        if (isset($data['roles']) && is_array($data['roles'])) {
            foreach ($data['roles'] as $roleData) {
                if (!isset($roleData['code']) || !isset($roleData['permissions'])) {
                    continue;
                }

                try {
                    $role = $roleModel::updateOrCreate(
                        ['code' => $roleData['code']],
                        [
                            'name' => $roleData['name'] ?? null,
                            'description' => $roleData['description'] ?? null
                        ]
                    );

                    // Sync permissions
                    $permissions = $permissionModel::whereIn('code', $roleData['permissions'])->get();
                    $role->permissions()->sync($permissions);

                    $this->line("Processed role: {$roleData['code']}");
                } catch (\Exception $e) {
                    $this->warn("Failed to import role {$roleData['code']}: {$e->getMessage()}");
                }
            }
        }

        // Import groups (only if enabled)
        if ($groupsEnabled && isset($data['groups']) && is_array($data['groups'])) {
            foreach ($data['groups'] as $groupData) {
                if (!isset($groupData['code'])) {
                    continue;
                }

                $group = $groupModel::updateOrCreate(
                    ['code' => $groupData['code']],
                    [
                        'name' => $groupData['name'] ?? null
                    ]
                );

                // Sync permissions
                if (isset($groupData['permissions'])) {
                    $permissions = $permissionModel::whereIn('code', $groupData['permissions'])->get();
                    $group->permissions()->sync($permissions);
                }

                $this->line("Processed group: {$groupData['code']}");
            }
        } elseif (isset($data['groups']) && is_array($data['groups']) && !$groupsEnabled) {
            $this->warn("Groups feature is disabled. Skipping groups import.");
        }

        $this->info("Import completed successfully!");

        return self::SUCCESS;
    }
}

