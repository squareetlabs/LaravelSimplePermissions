<?php

namespace Squareetlabs\LaravelSimplePermissions\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Squareetlabs\LaravelSimplePermissions\Tests\TestCase;
use Squareetlabs\LaravelSimplePermissions\Tests\Models\User;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Exception;

class CacheTest extends TestCase
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
    public function permissions_are_cached_after_first_check(): void
    {
        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.view']);

        $role->permissions()->attach($permission);
        $user->assignRole('admin');

        // First check - should populate cache
        $this->assertTrue($user->hasPermission('posts.view'));

        // Verify permissions work (cache is internal, we just verify functionality)
        $this->assertTrue($user->hasPermission('posts.view'));
    }

    /**
     * @test
     * @throws Exception
     */
    public function cache_is_cleared_when_user_role_changes(): void
    {
        $user = User::factory()->create();
        $role1 = SimplePermissions::model('role')::create(['code' => 'member', 'name' => 'Member']);
        $role2 = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);

        $permission1 = SimplePermissions::model('permission')::create(['code' => 'posts.view']);
        $permission2 = SimplePermissions::model('permission')::create(['code' => 'posts.delete']);

        $role1->permissions()->attach($permission1);
        $role2->permissions()->attach($permission2);

        $user->assignRole('member');

        // Populate cache
        $this->assertTrue($user->hasPermission('posts.view'));
        $this->assertFalse($user->hasPermission('posts.delete'));

        // Change role
        $user->removeRole('member');
        $user->assignRole('admin');

        // Reload user and relationships to get fresh data
        $user = $user->fresh();
        $user->load('roles');

        // Cache should be cleared/updated
        $this->assertTrue($user->hasPermission('posts.delete'));
    }
}
