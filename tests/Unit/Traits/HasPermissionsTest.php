<?php

namespace Squareetlabs\LaravelSimplePermissions\Tests\Unit\Traits;

use Squareetlabs\LaravelSimplePermissions\Tests\TestCase;
use Squareetlabs\LaravelSimplePermissions\Tests\Models\User;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Exception;

class HasPermissionsTest extends TestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function user_can_be_assigned_a_role(): void
    {
        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);

        $user->assignRole('admin');

        $this->assertTrue($user->hasRole('admin'));
    }

    /**
     * @test
     * @throws Exception
     */
    public function user_can_have_multiple_roles(): void
    {
        $user = User::factory()->create();
        SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);
        SimplePermissions::model('role')::create(['code' => 'editor', 'name' => 'Editor']);

        $user->assignRole('admin');
        $user->assignRole('editor');

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('editor'));
    }

    /**
     * @test
     * @throws Exception
     */
    public function user_can_check_multiple_roles(): void
    {
        $user = User::factory()->create();
        SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);
        SimplePermissions::model('role')::create(['code' => 'editor', 'name' => 'Editor']);

        $user->assignRole('admin');

        $this->assertTrue($user->hasRole(['admin', 'editor'])); // has at least one
        $this->assertFalse($user->hasRole(['editor', 'moderator'])); // has none
        $this->assertFalse($user->hasRole(['admin', 'editor'], true)); // require all
    }

    /**
     * @test
     * @throws Exception
     */
    public function user_can_check_permission_via_role(): void
    {
        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.view']);

        $role->permissions()->attach($permission);
        $user->assignRole('admin');

        $this->assertTrue($user->hasPermission('posts.view'));
        $this->assertFalse($user->hasPermission('posts.delete'));
    }

    /**
     * @test
     * @throws Exception
     */
    public function user_can_check_wildcard_permission(): void
    {
        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.*']);

        $role->permissions()->attach($permission);
        $user->assignRole('admin');

        $this->assertTrue($user->hasPermission('posts.view'));
        $this->assertTrue($user->hasPermission('posts.create'));
        $this->assertFalse($user->hasPermission('users.view'));
    }

    /**
     * @test
     * @throws Exception
     */
    public function user_can_check_ability(): void
    {
        $user = User::factory()->create();
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.view']);

        // Create a proper mock entity class
        $entityClass = get_class(new class {
            public $id = 1;
            public function getKey()
            {
                return $this->id;
            }
        });

        $ability = SimplePermissions::model('ability')::create([
            'permission_id' => $permission->id,
            'title' => 'View Post #1',
            'entity_id' => 1,
            'entity_type' => $entityClass,
        ]);

        $ability->users()->attach($user, ['forbidden' => false]);

        // Create entity instance
        $entity = new $entityClass();
        $entity->id = 1;

        $this->assertTrue($user->hasAbility('posts.view', $entity));
    }
}

