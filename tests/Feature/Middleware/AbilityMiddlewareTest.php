<?php

namespace Squareetlabs\LaravelSimplePermissions\Tests\Feature\Middleware;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Squareetlabs\LaravelSimplePermissions\Middleware\Ability;
use Squareetlabs\LaravelSimplePermissions\Tests\TestCase;
use Squareetlabs\LaravelSimplePermissions\Tests\Models\User;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;
use Exception;

// Create a test model for abilities
class TestPost extends Model
{
    protected $table = 'test_posts';
    protected $fillable = ['id', 'title'];
    public $timestamps = false;
}

class AbilityMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create test table
        if (!\Illuminate\Support\Facades\Schema::hasTable('test_posts')) {
            \Illuminate\Support\Facades\Schema::create('test_posts', function ($table) {
                $table->id();
                $table->string('title');
            });
        }
    }

    /**
     * @test
     * @throws Exception
     */
    public function middleware_allows_access_with_correct_ability(): void
    {
        $user = User::factory()->create();
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.view']);

        // Create a test post
        $post = TestPost::create(['id' => 1, 'title' => 'Test Post']);

        $ability = SimplePermissions::model('ability')::create([
            'permission_id' => $permission->id,
            'title' => 'View Post #1',
            'entity_id' => $post->id,
            'entity_type' => TestPost::class,
        ]);

        $ability->users()->attach($user, ['forbidden' => false]);

        Auth::login($user);

        // Create request with route parameter
        $request = Request::create('/test/1', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Create a mock route with parameters properly bound
        $route = \Illuminate\Support\Facades\Route::get('/test/{post_id}', function () {});
        // Manually set parameters to avoid binding issues
        $reflection = new \ReflectionClass($route);
        $parametersProperty = $reflection->getProperty('parameters');
        $parametersProperty->setAccessible(true);
        $parametersProperty->setValue($route, ['post_id' => $post->id]);
        
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        // The middleware expects: ability, entityClass, routeParamName
        // Format: ability:posts.view,TestPost,post_id
        $middleware = new Ability();
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        }, 'posts.view', TestPost::class, 'post_id');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     * @throws Exception
     */
    public function middleware_denies_access_without_ability(): void
    {
        $user = User::factory()->create();
        $permission = SimplePermissions::model('permission')::create(['code' => 'posts.view']);

        // Create a test post
        $post = TestPost::create(['id' => 1, 'title' => 'Test Post']);

        // Don't assign ability to user

        Auth::login($user);

        // Create request with route parameter
        $request = Request::create('/test/1', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Create a mock route with parameters properly bound
        $route = \Illuminate\Support\Facades\Route::get('/test/{post_id}', function () {});
        // Manually set parameters to avoid binding issues
        $reflection = new \ReflectionClass($route);
        $parametersProperty = $reflection->getProperty('parameters');
        $parametersProperty->setAccessible(true);
        $parametersProperty->setValue($route, ['post_id' => $post->id]);
        
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        $middleware = new Ability();

        try {
            $middleware->handle($request, function ($req) {
                return response('OK');
            }, 'posts.view', TestPost::class, 'post_id');
            $this->fail('Expected HttpException was not thrown');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }
}

