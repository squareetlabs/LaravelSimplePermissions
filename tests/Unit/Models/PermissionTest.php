<?php

namespace Squareetlabs\LaravelSimplePermissions\Tests\Unit\Models;

use Squareetlabs\LaravelSimplePermissions\Models\Permission;
use Squareetlabs\LaravelSimplePermissions\Tests\TestCase;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Exception;

class PermissionTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_create_a_permission(): void
    {
        $permission = SimplePermissions::model('permission')::create([
            'code' => 'posts.view',
            'name' => 'View Posts',
        ]);

        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertEquals('posts.view', $permission->code);
        $this->assertEquals('View Posts', $permission->name);
    }

    /**
     * @test
     * @throws Exception
     */
    public function permission_can_be_attached_to_role(): void
    {
        $role = SimplePermissions::model('role')::create([
            'code' => 'admin',
            'name' => 'Administrator'
        ]);

        $permissions = ['posts.view', 'posts.create'];
        foreach ($permissions as $code) {
            $permission = SimplePermissions::model('permission')::create(['code' => $code]);
            $role->permissions()->attach($permission);
        }

        $this->assertCount(2, $role->permissions);
        $this->assertTrue($role->permissions->pluck('code')->contains('posts.view'));
    }

    /**
     * @test
     * @throws Exception
     */
    public function permission_can_be_attached_to_multiple_roles(): void
    {
        $role1 = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);
        $role2 = SimplePermissions::model('role')::create(['code' => 'editor', 'name' => 'Editor']);

        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.view']);

        $role1->permissions()->attach($permission);
        $role2->permissions()->attach($permission);

        $this->assertCount(2, $permission->roles);
    }

    /**
     * @test
     */
    public function permission_code_must_be_unique(): void
    {
        SimplePermissions::model('permission')::create(['code' => 'posts.view', 'name' => 'View Posts']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        SimplePermissions::model('permission')::create(['code' => 'posts.view', 'name' => 'View Posts Again']);
    }
}

