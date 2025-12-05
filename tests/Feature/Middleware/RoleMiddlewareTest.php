<?php

namespace Squareetlabs\LaravelSimplePermissions\Tests\Feature\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Squareetlabs\LaravelSimplePermissions\Middleware\Role;
use Squareetlabs\LaravelSimplePermissions\Tests\TestCase;
use Squareetlabs\LaravelSimplePermissions\Tests\Models\User;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Exception;

class RoleMiddlewareTest extends TestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function middleware_allows_access_with_correct_role(): void
    {
        $user = User::factory()->create();
        SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);

        $user->assignRole('admin');

        Auth::login($user);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $middleware = new Role();
        // Handle method signature: ($request, Closure $next, $role, $guard = null)
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        }, 'admin');

        $this->assertEquals(200, $response->getStatusCode());
    }
}

