<?php

namespace Squareetlabs\LaravelSimplePermissions\Tests\Feature\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Squareetlabs\LaravelSimplePermissions\Middleware\Permission;
use Squareetlabs\LaravelSimplePermissions\Tests\TestCase;
use Squareetlabs\LaravelSimplePermissions\Tests\Models\User;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Exception;

class PermissionMiddlewareTest extends TestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function middleware_allows_access_with_correct_permission(): void
    {
        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.view']);

        $role->permissions()->attach($permission);
        $user->assignRole('admin');

        Auth::login($user);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $middleware = new Permission();
        // Handle method signature: ($request, Closure $next, $permission, $guard = null)
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        }, 'posts.view');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     * @throws Exception
     */
    public function middleware_denies_access_without_permission(): void
    {
        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.view']);

        $role->permissions()->attach($permission);
        $user->assignRole('admin');

        Auth::login($user);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $middleware = new Permission();

        try {
            $middleware->handle($request, function ($req) {
                return response('OK');
            }, 'posts.delete');
            $this->fail('Expected HttpException was not thrown');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }
}

