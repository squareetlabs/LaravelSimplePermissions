<?php

namespace Squareetlabs\LaravelSimplePermissions\Tests\Unit\Traits;

use Illuminate\Support\Facades\Event;
use Squareetlabs\LaravelSimplePermissions\Events\PermissionGranted;
use Squareetlabs\LaravelSimplePermissions\Events\PermissionRevoked;
use Squareetlabs\LaravelSimplePermissions\Tests\TestCase;
use Squareetlabs\LaravelSimplePermissions\Tests\Models\User;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Exception;

class DirectPermissionsTest extends TestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function user_can_be_given_permission_directly(): void
    {
        $user = User::factory()->create();
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.create', 'name' => 'Create Posts']);

        $user->givePermission('posts.create');

        $this->assertTrue($user->hasPermission('posts.create'));
        $this->assertTrue($user->permissions()->where('permission_id', $permission->id)->exists());
    }

    /**
     * @test
     * @throws Exception
     */
    public function user_can_have_permission_even_if_role_does_not_have_it(): void
    {
        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'editor', 'name' => 'Editor']);
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.delete', 'name' => 'Delete Posts']);

        // Assign role without the permission
        $user->assignRole('editor');

        // User should not have the permission via role
        $this->assertFalse($user->hasPermission('posts.delete'));

        // Give permission directly
        $user->givePermission('posts.delete');

        // Now user should have the permission
        $this->assertTrue($user->hasPermission('posts.delete'));
    }

    /**
     * @test
     * @throws Exception
     */
    public function user_can_have_permission_revoked_even_if_role_has_it(): void
    {
        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.edit', 'name' => 'Edit Posts']);

        // Assign role with the permission
        $role->permissions()->attach($permission);
        $user->assignRole('admin');

        // User should have the permission via role
        $this->assertTrue($user->hasPermission('posts.edit'));

        // Revoke permission directly
        $user->revokePermission('posts.edit');

        // Now user should not have the permission, even though role has it
        $this->assertFalse($user->hasPermission('posts.edit'));
    }

    /**
     * @test
     * @throws Exception
     */
    public function permission_granted_event_is_dispatched(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.create', 'name' => 'Create Posts']);

        $user->givePermission('posts.create');

        Event::assertDispatched(PermissionGranted::class, function ($event) use ($user, $permission) {
            return $event->user->id === $user->id && $event->permission->id === $permission->id;
        });
    }

    /**
     * @test
     * @throws Exception
     */
    public function permission_revoked_event_is_dispatched(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.edit', 'name' => 'Edit Posts']);

        $user->givePermission('posts.edit');
        $user->revokePermission('posts.edit');

        Event::assertDispatched(PermissionRevoked::class, function ($event) use ($user, $permission) {
            return $event->user->id === $user->id && $event->permission->id === $permission->id;
        });
    }

    /**
     * @test
     * @throws Exception
     */
    public function user_can_remove_direct_permission_assignment(): void
    {
        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.edit', 'name' => 'Edit Posts']);

        // Assign role with the permission
        $role->permissions()->attach($permission);
        $user->assignRole('admin');

        // Give permission directly
        $user->givePermission('posts.edit');
        $this->assertTrue($user->hasPermission('posts.edit'));

        // Remove direct assignment (should return to role-based permission)
        $user->removePermission('posts.edit');

        // User should still have permission via role
        $this->assertTrue($user->hasPermission('posts.edit'));
    }

    /**
     * @test
     * @throws Exception
     */
    public function user_can_sync_direct_permissions(): void
    {
        $user = User::factory()->create();
        $permission1 = SimplePermissions::model('permission')::create(['code' => 'posts.create', 'name' => 'Create Posts']);
        $permission2 = SimplePermissions::model('permission')::create(['code' => 'posts.edit', 'name' => 'Edit Posts']);
        $permission3 = SimplePermissions::model('permission')::create(['code' => 'posts.delete', 'name' => 'Delete Posts']);

        // Give some permissions
        $user->givePermission('posts.create');
        $user->givePermission('posts.edit');

        // Sync to only have posts.delete
        $user->syncPermissions(['posts.delete']);

        $this->assertFalse($user->hasPermission('posts.create'));
        $this->assertFalse($user->hasPermission('posts.edit'));
        $this->assertTrue($user->hasPermission('posts.delete'));
    }
}
