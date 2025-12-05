<?php

namespace Squareetlabs\LaravelSimplePermissions\Tests\Feature;

use Squareetlabs\LaravelSimplePermissions\Tests\TestCase;
use Squareetlabs\LaravelSimplePermissions\Tests\Models\User;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Exception;

class PermissionCheckingTest extends TestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function user_can_check_permission(): void
    {
        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'member', 'name' => 'Member']);
        $permission1 = SimplePermissions::model('permission')::create(['code' => 'posts.view']);
        $permission2 = SimplePermissions::model('permission')::create(['code' => 'posts.create']);

        $role->permissions()->attach([$permission1->id, $permission2->id]);
        $user->assignRole('member');

        $this->assertTrue($user->hasPermission('posts.view'));
        $this->assertTrue($user->hasPermission('posts.create'));
        $this->assertFalse($user->hasPermission('posts.delete'));
    }

    /**
     * @test
     * @throws Exception
     */
    public function wildcard_permission_grants_all_access(): void
    {
        config()->set('simple-permissions.wildcards.enabled', true);

        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);
        $permission = SimplePermissions::model('permission')::create(['code' => '*']);

        $role->permissions()->attach($permission);
        $user->assignRole('admin');

        $this->assertTrue($user->hasPermission('any.permission'));
        $this->assertTrue($user->hasPermission('posts.create'));
        $this->assertTrue($user->hasPermission('users.delete'));
    }
}

