<?php

namespace Squareetlabs\LaravelSimplePermissions\Middleware;

use Closure;
use Illuminate\Http\Request;

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
        if (!$this->authorization($request, 'ability', $ability, $models)) {
            return $this->unauthorized();
        }

        return $next($request);
    }
}
