<?php

namespace Squareetlabs\LaravelSimplePermissions\Console;

use Illuminate\Console\Command;
use Squareetlabs\LaravelSimplePermissions\Support\Services\PermissionCache;

class PermissionsClearCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all permissions cache';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cache = new PermissionCache();
        $cache->flush();

        $this->info('Permissions cache cleared successfully!');
        return self::SUCCESS;
    }
}

