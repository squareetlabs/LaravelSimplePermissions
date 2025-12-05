<?php

namespace Squareetlabs\LaravelSimplePermissions\Tests\Unit\Events;

use Illuminate\Support\Facades\Event;
use Squareetlabs\LaravelSimplePermissions\Events\RoleAssigned;
use Squareetlabs\LaravelSimplePermissions\Events\RoleRemoved;
use Squareetlabs\LaravelSimplePermissions\Events\AbilityGranted;
use Squareetlabs\LaravelSimplePermissions\Events\AbilityRevoked;
use Squareetlabs\LaravelSimplePermissions\Tests\TestCase;
use Squareetlabs\LaravelSimplePermissions\Tests\Models\User;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Exception;

class PermissionEventsTest extends TestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function role_assigned_event_is_dispatched(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);

        $user->assignRole('admin');

        Event::assertDispatched(RoleAssigned::class, function ($event) use ($user, $role) {
            return $event->user->id === $user->id && $event->role->id === $role->id;
        });
    }

    /**
     * @test
     * @throws Exception
     */
    public function role_removed_event_is_dispatched(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $role = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);

        $user->assignRole('admin');
        $user->removeRole('admin');

        Event::assertDispatched(RoleRemoved::class, function ($event) use ($user, $role) {
            return $event->user->id === $user->id && $event->role->id === $role->id;
        });
    }

    /**
     * @test
     * @throws Exception
     */
    public function ability_granted_event_is_dispatched(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.edit']);

        $entityClass = get_class(new class {
            public $id = 1;
            public function getKey() { return $this->id; }
        });
        
        $entity = new $entityClass();
        $entity->id = 1;

        $user->allowAbility('posts.edit', $entity);

        Event::assertDispatched(AbilityGranted::class, function ($event) use ($user, $entity) {
            return $event->user->id === $user->id 
                && $event->permission === 'posts.edit'
                && $event->entity->getKey() === $entity->getKey();
        });
    }

    /**
     * @test
     * @throws Exception
     */
    public function ability_revoked_event_is_dispatched(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.edit']);

        $entityClass = get_class(new class {
            public $id = 1;
            public function getKey() { return $this->id; }
        });
        
        $entity = new $entityClass();
        $entity->id = 1;

        $user->allowAbility('posts.edit', $entity);
        $user->removeAbility('posts.edit', $entity);

        Event::assertDispatched(AbilityRevoked::class, function ($event) use ($user, $entity) {
            return $event->user->id === $user->id 
                && $event->permission === 'posts.edit'
                && $event->entity->getKey() === $entity->getKey();
        });
    }
}

