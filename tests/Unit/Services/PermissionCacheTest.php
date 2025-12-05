<?php

namespace Squareetlabs\LaravelSimplePermissions\Tests\Unit\Services;

use Illuminate\Support\Facades\Cache;
use Squareetlabs\LaravelSimplePermissions\Support\Services\PermissionCache;
use Squareetlabs\LaravelSimplePermissions\Tests\TestCase;
use Squareetlabs\LaravelSimplePermissions\Tests\Models\User;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Exception;

class PermissionCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_can_cache_permissions(): void
    {
        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);
        $permission1 = SimplePermissions::model('permission')::create(['code' => 'posts.view']);
        $permission2 = SimplePermissions::model('permission')::create(['code' => 'posts.create']);

        $role->permissions()->attach([$permission1->id, $permission2->id]);
        $user->assignRole('admin');

        $cache = new PermissionCache();
        $permissions = $cache->remember("user_{$user->id}_permissions", function () use ($user) {
            return $user->allPermissions();
        });

        $this->assertIsArray($permissions);
        $this->assertContains('posts.view', $permissions);
        $this->assertContains('posts.create', $permissions);
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_returns_cached_permissions(): void
    {
        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.view']);

        $role->permissions()->attach($permission);
        $user->assignRole('admin');

        $cache = new PermissionCache();
        $key = "user_{$user->id}_permissions";

        $permissions1 = $cache->remember($key, function () use ($user) {
            return $user->allPermissions();
        });

        $permissions2 = $cache->remember($key, function () use ($user) {
            return $user->allPermissions();
        });

        $this->assertEquals($permissions1, $permissions2);
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_can_clear_user_cache(): void
    {
        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.view']);

        $role->permissions()->attach($permission);
        $user->assignRole('admin');

        $cache = new PermissionCache();
        $key = "user_{$user->id}_permissions";

        $cache->remember($key, function () use ($user) {
            return $user->allPermissions();
        });

        $cache->forget($key);

        // Cache should be cleared
        $this->assertNull($cache->get($key));
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_can_clear_all_cache(): void
    {
        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.view']);

        $role->permissions()->attach($permission);
        $user->assignRole('admin');

        $cache = new PermissionCache();
        $key = "user_{$user->id}_permissions";

        $cache->remember($key, function () use ($user) {
            return $user->allPermissions();
        });

        $cache->flush();

        // Cache should be cleared
        $this->assertNull($cache->get($key));
    }
}

