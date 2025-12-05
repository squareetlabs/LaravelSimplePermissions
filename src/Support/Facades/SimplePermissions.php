<?php

namespace Squareetlabs\LaravelSimplePermissions\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Squareetlabs\LaravelSimplePermissions\Support\Services\SimplePermissionsService;

/**
 * @method static string model(string $model)
 * @method static object instance(string $model)
 *
 * @see SimplePermissionsService
 */
class SimplePermissions extends Facade
{
    /**
     * Gets the facade name.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return SimplePermissionsService::class;
    }
}
