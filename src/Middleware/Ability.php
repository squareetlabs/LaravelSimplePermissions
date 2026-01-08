<?php

namespace Squareetlabs\LaravelSimplePermissions\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class Ability extends SimplePermissionsMiddleware
{
    /**
     * Handle incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $ability
     * @param ...$models
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $ability, ...$models): mixed
    {
        // Check if abilities feature is enabled
        if (!Config::get('simple-permissions.features.abilities.enabled', true)
            || !Schema::hasTable('abilities')
            || !Schema::hasTable('entity_ability')) {
            // If abilities are disabled, deny access
            return $this->unauthorized();
        }

        if (!$this->authorization($request, 'ability', $ability, $models)) {
            return $this->unauthorized();
        }

        return $next($request);
    }
}
