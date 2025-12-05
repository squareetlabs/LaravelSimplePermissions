<?php

namespace Squareetlabs\LaravelSimplePermissions\Support\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

/**
 * PermissionCache Service
 * 
 * Handles caching of permissions and related data.
 * Uses Laravel's cache system with tags for efficient cache management.
 * 
 * @package Squareetlabs\LaravelSimplePermissions\Support\Services
 */
class PermissionCache
{
    /**
     * Remember a value in cache with permissions tags.
     * 
     * If caching is disabled, the callback is executed directly without caching.
     * Uses cache tags for efficient cache invalidation.
     *
     * @param string $key Cache key (will be prefixed automatically)
     * @param callable $callback Callback to execute if value is not cached
     * @param int|null $ttl Time to live in seconds (uses config default if null)
     * @return mixed The cached value or result of callback
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        if (!Config::get('simple-permissions.cache.enabled')) {
            return $callback();
        }

        $prefix = Config::get('simple-permissions.cache.prefix');
        $fullKey = $prefix . ':' . $key;
        $ttl = $ttl ?? Config::get('simple-permissions.cache.ttl');

        if (Config::get('simple-permissions.cache.tags')) {
            return Cache::tags(['simple-permissions', 'permissions'])
                ->remember($fullKey, $ttl, $callback);
        }

        return Cache::remember($fullKey, $ttl, $callback);
    }

    /**
     * Flush all permissions cache.
     * 
     * Removes all cached values tagged with 'simple-permissions' and 'permissions'.
     * Only works if cache tags are enabled.
     *
     * @return void
     */
    public function flush(): void
    {
        if (!Config::get('simple-permissions.cache.enabled')) {
            return;
        }

        Cache::tags(['simple-permissions', 'permissions'])->flush();
    }

    /**
     * Forget a specific cache key.
     * 
     * Removes a single cached value by key.
     *
     * @param string $key Cache key to forget (will be prefixed automatically)
     * @return void
     */
    public function forget(string $key): void
    {
        if (!Config::get('simple-permissions.cache.enabled')) {
            return;
        }

        $prefix = Config::get('simple-permissions.cache.prefix');
        $fullKey = $prefix . ':' . $key;

        Cache::tags(['simple-permissions', 'permissions'])->forget($fullKey);
    }

    /**
     * Get a value from cache.
     * 
     * Retrieves a cached value by key. Returns default if not found or caching is disabled.
     *
     * @param string $key Cache key to retrieve (will be prefixed automatically)
     * @param mixed $default Default value to return if key not found
     * @return mixed The cached value or default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!Config::get('simple-permissions.cache.enabled')) {
            return $default;
        }

        $prefix = Config::get('simple-permissions.cache.prefix');
        $fullKey = $prefix . ':' . $key;

        return Cache::tags(['simple-permissions', 'permissions'])->get($fullKey, $default);
    }
}

