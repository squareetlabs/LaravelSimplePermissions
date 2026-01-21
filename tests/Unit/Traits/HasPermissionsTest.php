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

    /**
     * @test
     * @throws Exception
     */
    public function all_permissions_excludes_forbidden_permissions(): void
    {
        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'editor', 'name' => 'Editor']);

        $permission1 = SimplePermissions::model('permission')::create(['code' => 'posts.create', 'name' => 'Create Posts']);
        $permission2 = SimplePermissions::model('permission')::create(['code' => 'posts.delete', 'name' => 'Delete Posts']);
        $permission3 = SimplePermissions::model('permission')::create(['code' => 'posts.edit', 'name' => 'Edit Posts']);

        $role->permissions()->attach([$permission1->id, $permission2->id, $permission3->id]);
        $user->assignRole($role);

        // Usuario tiene 3 permisos del rol
        $this->assertCount(3, $user->allPermissions());
        $this->assertTrue($user->hasPermission('posts.create'));
        $this->assertTrue($user->hasPermission('posts.delete'));
        $this->assertTrue($user->hasPermission('posts.edit'));

        // Prohibir un permiso
        $user->revokePermission('posts.delete');

        // allPermissions() debería devolver solo 2 (sin el prohibido)
        $allPermissions = $user->allPermissions();
        $this->assertCount(2, $allPermissions);
        $this->assertContains('posts.create', $allPermissions);
        $this->assertContains('posts.edit', $allPermissions);
        $this->assertNotContains('posts.delete', $allPermissions);

        // hasPermission() debe devolver false para el prohibido
        $this->assertFalse($user->hasPermission('posts.delete'));
    }

    /**
     * @test
     * @throws Exception
     */
    public function all_permissions_is_consistent_with_has_permission(): void
    {
        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);

        $permissions = collect([
            SimplePermissions::model('permission')::create(['code' => 'users.view', 'name' => 'View Users']),
            SimplePermissions::model('permission')::create(['code' => 'users.create', 'name' => 'Create Users']),
            SimplePermissions::model('permission')::create(['code' => 'users.delete', 'name' => 'Delete Users']),
            SimplePermissions::model('permission')::create(['code' => 'posts.view', 'name' => 'View Posts']),
        ]);

        $role->permissions()->attach($permissions->pluck('id'));
        $user->assignRole($role);

        // Prohibir algunos permisos
        $user->revokePermission('users.create');
        $user->revokePermission('users.delete');

        $allPermissions = $user->allPermissions();

        // Verificar que allPermissions() y hasPermission() sean consistentes
        foreach ($permissions as $permission) {
            $inArray = in_array($permission->code, $allPermissions);
            $hasPermission = $user->hasPermission($permission->code);

            $this->assertEquals(
                $hasPermission,
                $inArray,
                "Inconsistencia para {$permission->code}: hasPermission={$hasPermission}, inArray={$inArray}"
            );
        }
    }

    /**
     * @test
     * @throws Exception
     */
    public function forbidden_permissions_have_highest_priority(): void
    {
        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);

        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.delete', 'name' => 'Delete Posts']);

        $role->permissions()->attach($permission);
        $user->assignRole($role);

        // Usuario tiene el permiso del rol
        $this->assertTrue($user->hasPermission('posts.delete'));
        $this->assertContains('posts.delete', $user->allPermissions());

        // Prohibir explícitamente el permiso
        $user->revokePermission('posts.delete');

        // El permiso prohibido debe tener prioridad sobre el del rol
        $this->assertFalse($user->hasPermission('posts.delete'));
        $this->assertNotContains('posts.delete', $user->allPermissions());
    }

    /**
     * @test
     * @throws Exception
     */
    public function remove_permission_returns_to_role_based_permissions(): void
    {
        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);

        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.publish', 'name' => 'Publish Posts']);

        $role->permissions()->attach($permission);
        $user->assignRole($role);

        // Usuario tiene el permiso del rol
        $this->assertTrue($user->hasPermission('posts.publish'));

        // Prohibir el permiso
        $user->revokePermission('posts.publish');
        $this->assertFalse($user->hasPermission('posts.publish'));
        $this->assertNotContains('posts.publish', $user->allPermissions());

        // Quitar la prohibición (vuelve a permisos del rol)
        $user->removePermission('posts.publish');
        $this->assertTrue($user->hasPermission('posts.publish'));
        $this->assertContains('posts.publish', $user->allPermissions());
    }

    /**
     * @test
     * @throws Exception
     */
    public function multiple_forbidden_permissions_are_all_excluded(): void
    {
        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);

        $permissions = [];
        for ($i = 1; $i <= 10; $i++) {
            $permissions[] = SimplePermissions::model('permission')::create([
                'code' => "posts.action{$i}",
                'name' => "Action {$i}"
            ]);
        }

        $role->permissions()->attach(collect($permissions)->pluck('id'));
        $user->assignRole($role);

        // Usuario tiene 10 permisos
        $this->assertCount(10, $user->allPermissions());

        // Prohibir 7 permisos
        for ($i = 1; $i <= 7; $i++) {
            $user->revokePermission("posts.action{$i}");
        }

        // Debe quedar con 3 permisos
        $allPermissions = $user->allPermissions();
        $this->assertCount(3, $allPermissions);

        // Verificar que solo quedan los no prohibidos
        $this->assertContains('posts.action8', $allPermissions);
        $this->assertContains('posts.action9', $allPermissions);
        $this->assertContains('posts.action10', $allPermissions);

        // Verificar que los prohibidos no están
        for ($i = 1; $i <= 7; $i++) {
            $this->assertNotContains("posts.action{$i}", $allPermissions);
            $this->assertFalse($user->hasPermission("posts.action{$i}"));
        }
    }
}

