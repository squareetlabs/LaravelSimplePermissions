<?php

namespace Squareetlabs\LaravelSimplePermissions\Tests\Unit\Models;

use Squareetlabs\LaravelSimplePermissions\Models\Group;
use Squareetlabs\LaravelSimplePermissions\Tests\TestCase;
use Squareetlabs\LaravelSimplePermissions\Tests\Models\User;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Exception;

class GroupTest extends TestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function it_can_create_a_group(): void
    {
        $group = SimplePermissions::model('group')::create([
            'code' => 'developers',
            'name' => 'Developers Group'
        ]);

        $this->assertInstanceOf(Group::class, $group);
        $this->assertEquals('developers', $group->code);
        $this->assertEquals('Developers Group', $group->name);
    }

    /**
     * @test
     * @throws Exception
     */
    public function group_can_have_multiple_permissions(): void
    {
        $group = SimplePermissions::model('group')::create([
            'code' => 'developers',
            'name' => 'Developers'
        ]);

        $permissions = ['posts.view', 'posts.create', 'posts.edit'];
        foreach ($permissions as $code) {
            $permission = SimplePermissions::model('permission')::create(['code' => $code]);
            $group->permissions()->attach($permission);
        }

        $this->assertCount(3, $group->permissions);
    }

    /**
     * @test
     * @throws Exception
     */
    public function group_can_be_retrieved_by_code(): void
    {
        SimplePermissions::model('group')::create([
            'code' => 'developers',
            'name' => 'Developers'
        ]);

        $group = SimplePermissions::model('group')::where('code', 'developers')->first();

        $this->assertInstanceOf(Group::class, $group);
        $this->assertEquals('developers', $group->code);
    }

    /**
     * @test
     * @throws Exception
     */
    public function user_can_be_added_to_group(): void
    {
        $user = User::factory()->create();
        $group = SimplePermissions::model('group')::create([
            'code' => 'developers',
            'name' => 'Developers'
        ]);

        $group->users()->attach($user);

        $this->assertTrue($user->groups()->where('groups.id', $group->id)->exists());
    }
}

