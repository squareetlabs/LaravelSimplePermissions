<?php

namespace Squareetlabs\LaravelSimplePermissions\Tests\Unit\Models;

use Squareetlabs\LaravelSimplePermissions\Models\Ability;
use Squareetlabs\LaravelSimplePermissions\Tests\TestCase;
use Squareetlabs\LaravelSimplePermissions\Tests\Models\User;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Exception;

class AbilityTest extends TestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function it_can_create_an_ability(): void
    {
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.view', 'name' => 'View Posts']);

        $ability = SimplePermissions::model('ability')::create([
            'permission_id' => $permission->id,
            'title' => 'View Post #1',
            'entity_id' => 1,
            'entity_type' => 'App\\Models\\Post',
        ]);

        $this->assertInstanceOf(Ability::class, $ability);
        $this->assertEquals($permission->id, $ability->permission_id);
        $this->assertEquals('View Post #1', $ability->title);
    }

    /**
     * @test
     * @throws Exception
     */
    public function ability_can_be_assigned_to_user(): void
    {
        $user = User::factory()->create();
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.view', 'name' => 'View Posts']);

        $ability = SimplePermissions::model('ability')::create([
            'permission_id' => $permission->id,
            'title' => 'View Post #1',
            'entity_id' => 1,
            'entity_type' => 'App\\Models\\Post',
        ]);

        $ability->users()->attach($user, ['forbidden' => false]);

        $this->assertTrue($user->abilities()->where('abilities.id', $ability->id)->exists());
    }
}

