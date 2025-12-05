<?php

namespace Squareetlabs\LaravelSimplePermissions\Middleware;

use Closure;
use Illuminate\Http\Request;

class Permission extends SimplePermissionsMiddleware
{
    /**
     * Handle incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|array $permissions
     * @param bool $options
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string|array $permissions, bool $options = false): mixed
    {
        if (!$this->authorization($request, 'permission', $permissions, [], $options)) {
            return $this->unauthorized();
        }

        return $next($request);
    }
}
