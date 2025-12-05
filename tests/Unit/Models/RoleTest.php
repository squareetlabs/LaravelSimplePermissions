<?php

namespace Squareetlabs\LaravelSimplePermissions\Tests\Unit\Models;

use Squareetlabs\LaravelSimplePermissions\Models\Role;
use Squareetlabs\LaravelSimplePermissions\Tests\TestCase;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Exception;

class RoleTest extends TestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function it_can_create_a_role(): void
    {
        $role = SimplePermissions::model('role')::create([
            'code' => 'admin',
            'name' => 'Administrator'
        ]);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals('admin', $role->code);
        $this->assertEquals('Administrator', $role->name);
    }

    /**
     * @test
     * @throws Exception
     */
    public function role_can_have_multiple_permissions(): void
    {
        $role = SimplePermissions::model('role')::create([
            'code' => 'editor',
            'name' => 'Editor'
        ]);

        $permissions = ['posts.view', 'posts.create', 'posts.edit'];
        foreach ($permissions as $code) {
            $permission = SimplePermissions::model('permission')::create(['code' => $code]);
            $role->permissions()->attach($permission);
        }

        $this->assertCount(3, $role->permissions);
        $this->assertTrue($role->permissions->pluck('code')->contains('posts.view'));
        $this->assertTrue($role->permissions->pluck('code')->contains('posts.create'));
        $this->assertTrue($role->permissions->pluck('code')->contains('posts.edit'));
    }

    /**
     * @test
     * @throws Exception
     */
    public function role_can_be_retrieved_by_code(): void
    {
        SimplePermissions::model('role')::create([
            'code' => 'admin',
            'name' => 'Administrator'
        ]);

        $role = SimplePermissions::model('role')::where('code', 'admin')->first();

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals('admin', $role->code);
    }

    /**
     * @test
     * @throws Exception
     */
    public function role_returns_null_for_invalid_code(): void
    {
        $role = SimplePermissions::model('role')::where('code', 'nonexistent')->first();

        $this->assertNull($role);
    }
}

