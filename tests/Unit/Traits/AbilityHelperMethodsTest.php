<?php

namespace Squareetlabs\LaravelSimplePermissions\Tests\Unit\Traits;

use Squareetlabs\LaravelSimplePermissions\Tests\TestCase;
use Squareetlabs\LaravelSimplePermissions\Tests\Models\User;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Exception;

class AbilityHelperMethodsTest extends TestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function user_can_allow_ability(): void
    {
        $user = User::factory()->create();
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.edit']);

        // Create a test entity
        $entityClass = get_class(new class {
            public $id = 1;
            public function getKey() { return $this->id; }
        });
        
        $entity = new $entityClass();
        $entity->id = 1;

        $user->allowAbility('posts.edit', $entity);

        $this->assertTrue($user->hasAbility('posts.edit', $entity));
    }

    /**
     * @test
     * @throws Exception
     */
    public function user_can_forbid_ability(): void
    {
        $user = User::factory()->create();
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.edit']);

        // Create a test entity
        $entityClass = get_class(new class {
            public $id = 1;
            public function getKey() { return $this->id; }
        });
        
        $entity = new $entityClass();
        $entity->id = 1;

        $user->forbidAbility('posts.edit', $entity);

        $this->assertFalse($user->hasAbility('posts.edit', $entity));
    }

    /**
     * @test
     * @throws Exception
     */
    public function user_can_remove_ability(): void
    {
        $user = User::factory()->create();
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.edit']);

        // Create a test entity
        $entityClass = get_class(new class {
            public $id = 1;
            public function getKey() { return $this->id; }
        });
        
        $entity = new $entityClass();
        $entity->id = 1;

        $user->allowAbility('posts.edit', $entity);
        $this->assertTrue($user->hasAbility('posts.edit', $entity));

        $user->removeAbility('posts.edit', $entity);
        // After removal, should fall back to global permission check
        $this->assertFalse($user->hasAbility('posts.edit', $entity));
    }
}

