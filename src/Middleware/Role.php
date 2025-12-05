<?php

namespace Squareetlabs\LaravelSimplePermissions\Middleware;

use Closure;
use Illuminate\Http\Request;

class Role extends SimplePermissionsMiddleware
{
    /**
     * Handle incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|array $roles
     * @param bool $options
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string|array $roles, bool $options = false): mixed
    {
        if (!$this->authorization($request, 'roles', $roles, [], $options)) {
            return $this->unauthorized();
        }

        return $next($request);
    }
}
