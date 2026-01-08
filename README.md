# Squareetlabs/LaravelSimplePermissions

[![Latest Stable Version](https://poser.pugx.org/squareetlabs/laravel-simple-permissions/v/stable)](https://packagist.org/packages/squareetlabs/laravel-simple-permissions)
[![PHP Version Require](https://poser.pugx.org/squareetlabs/laravel-simple-permissions/require/php)](https://packagist.org/packages/squareetlabs/laravel-simple-permissions)
[![License](https://poser.pugx.org/squareetlabs/laravel-simple-permissions/license)](https://packagist.org/packages/squareetlabs/laravel-simple-permissions)

A comprehensive and flexible Laravel package for advanced permission management. This package provides a robust system for managing roles, permissions, groups, and entity-specific abilities.

**Core Functionality:**

- **Role-Based Access Control (RBAC)**: Define custom roles with specific permission sets. Roles can be assigned to users to manage access levels efficiently.

- **Permission System**: Implement fine-grained permissions using a code-based system (e.g., `posts.create`, `users.edit`). Permissions are global entities that can be assigned to roles and groups. Supports wildcard permissions for flexible access patterns.

- **Group Management** (Optional): Organize users into groups. Groups can have their own permission sets, allowing for efficient permission management when multiple users need the same access level. This feature is optional and can be skipped if not needed.

- **Entity-Specific Abilities**: Grant or deny permissions for specific model instances (e.g., allowing a user to edit a particular post but not others). This provides the most granular level of access control.

- **Caching & Performance**: Intelligent caching system to optimize permission checks, reducing database queries and improving application performance.

- **Audit Logging**: Optional comprehensive audit trail that logs all permission-related actions including role assignments and permission changes.

- **Laravel Integration**: Seamlessly integrates with Laravel's built-in authorization system, including Policies, Blade directives, and middleware for route protection.

## Key Features

- ✅ **Roles & Permissions**: Flexible role system with granular permissions
- ✅ **Groups** (Optional): Organize users into groups with shared permissions
- ✅ **Abilities**: Entity-specific permissions for individual models
- ✅ **Smart Caching**: Caching system to optimize permission checks
- ✅ **Audit Logging**: Complete action logging (optional)
- ✅ **Blade Directives**: Blade directives for permission checks in views
- ✅ **Policies**: Integration with Laravel's Policy system
- ✅ **Middleware**: Middleware for route protection
- ✅ **Artisan Commands**: CLI tools for management
- ✅ **Events**: Event system for permission changes (RoleAssigned, RoleRemoved, AbilityGranted, AbilityRevoked)
- ✅ **Validation**: Automatic validation of permission codes
- ✅ **Performance**: Optimized queries with eager loading

## Requirements

- PHP >= 8.1
- Laravel 8.x, 9.x, 10.x, 11.x or 12.x

## Installation

### 1. Install the Package

```bash
composer require squareetlabs/laravel-simple-permissions
```

### 2. Publish Configuration and Migrations

```bash
php artisan vendor:publish --provider="Squareetlabs\LaravelSimplePermissions\SimplePermissionsServiceProvider"
```

This will publish:
- `config/simple-permissions.php` - Configuration file
- Database migrations

### 3. Configure the User Model

Add the `HasPermissions` trait to your `User` model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Squareetlabs\LaravelSimplePermissions\Traits\HasPermissions;

class User extends Model
{
    use HasPermissions;
    
    // ... rest of your code
}
```

### 4. Run Migrations

> ⚠️ **IMPORTANT**: Always do backups before running migrations.

```bash
php artisan migrate
```

> [!NOTE]
> If you wish to use custom foreign keys and table names, modify `config/simple-permissions.php` before running migrations.

#### Optional Features Configuration

You can enable or disable optional features (Groups and Abilities) via configuration. When disabled, related migrations won't be published and related functionality will be skipped.

Configure in `config/simple-permissions.php` or via environment variables:

```env
# Disable groups feature
SIMPLE_PERMISSIONS_GROUPS_ENABLED=false

# Disable abilities feature
SIMPLE_PERMISSIONS_ABILITIES_ENABLED=false
```

Or in `config/simple-permissions.php`:

```php
'features' => [
    'groups' => [
        'enabled' => env('SIMPLE_PERMISSIONS_GROUPS_ENABLED', true),
    ],
    'abilities' => [
        'enabled' => env('SIMPLE_PERMISSIONS_ABILITIES_ENABLED', true),
    ],
],
```

> [!NOTE]
> **Important**: Configure these settings **before** publishing migrations. If you disable a feature after migrations have been published, you'll need to manually remove the related migration files or tables.

**Optional Migrations:**
- **Groups** (`create_groups_table.php`, `create_group_user_table.php`): Only published if `features.groups.enabled` is `true`
- **Abilities** (`create_abilities_table.php`, `create_entity_ability_table.php`): Only published if `features.abilities.enabled` is `true`
- **Audit Logging** (`create_audit_logs_table.php`): Always published (table creation is handled by AuditService)

All other migrations are **essential** for the package to function properly.

### 5. Optional Configuration

#### Enable Caching

To improve performance, enable caching in `.env`:

```env
SIMPLE_PERMISSIONS_CACHE_ENABLED=true
SIMPLE_PERMISSIONS_CACHE_DRIVER=redis
SIMPLE_PERMISSIONS_CACHE_TTL=3600
```

#### Enable Audit Logging

To log all permission actions:

```env
SIMPLE_PERMISSIONS_AUDIT_ENABLED=true
SIMPLE_PERMISSIONS_AUDIT_LOG_CHANNEL=stack
```

## Configuration

The configuration file `config/simple-permissions.php` contains all options:

### Custom Models

```php
'models' => [
    'user' => App\Models\User::class,
    // ... other models
],
```

### Cache

```php
'cache' => [
    'enabled' => env('SIMPLE_PERMISSIONS_CACHE_ENABLED', true),
    'driver' => env('SIMPLE_PERMISSIONS_CACHE_DRIVER', 'redis'),
    'ttl' => env('SIMPLE_PERMISSIONS_CACHE_TTL', 3600),
    'prefix' => 'simple_permissions',
    'tags' => true,
],
```

## Basic Usage

### Creating Roles and Permissions

```php
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;

// Create permissions
$viewPost = SimplePermissions::model('permission')::create(['code' => 'posts.view', 'name' => 'View Posts']);
$createPost = SimplePermissions::model('permission')::create(['code' => 'posts.create', 'name' => 'Create Posts']);

// Create role
$adminRole = SimplePermissions::model('role')::create(['code' => 'admin', 'name' => 'Administrator']);

// Assign permissions to role
$adminRole->permissions()->attach([$viewPost->id, $createPost->id]);
```

### Assigning Roles to Users

```php
// Assign role to user
$user->assignRole('admin');

// Remove role from user
$user->removeRole('admin');

// Sync roles (replaces all existing roles)
$user->syncRoles(['admin', 'editor']);
```

### Checking Permissions

```php
// Check if user has a permission (direct or via role/group)
if ($user->hasPermission('posts.create')) {
    // User can create posts
}

// Check if user has a role
if ($user->hasRole('admin')) {
    // User is admin
}

// Check specific ability on an entity
if ($user->hasAbility('edit', $post)) {
    // User can edit this specific post
}
```

## Users

The `HasPermissions` trait provides the following methods:

```php
// Check if user has a role (or roles)
// $require = true: all roles are required
// $require = false: at least one of the roles
$user->hasRole('admin', $require = false)
$user->hasRole(['admin', 'editor'], $require = false)

// Check if user has a permission (or permissions)
// $require = true: all permissions are required
// $require = false: at least one of the permissions
$user->hasPermission('posts.create', $require = false)
$user->hasPermission(['posts.create', 'posts.edit'], $require = false)

// Check if user has an ability on an entity
$user->hasAbility('posts.edit', $post)

// Allow ability for user on an entity
$user->allowAbility('posts.edit', $post)

// Forbid ability for user on an entity
$user->forbidAbility('posts.edit', $post)

// Remove ability from user
$user->removeAbility('posts.edit', $post)
```

## Roles & Permissions

### Wildcard Permissions

You can use wildcards for permissions:

- `posts.*` - All permissions starting with `posts.`
- `*` - All permissions (if enabled in config)

### Checking Permissions

```php
// Check multiple permissions (OR)
if ($user->hasPermission(['posts.create', 'posts.edit'], false)) {
    // User can create OR edit posts
}

// Check multiple permissions (AND)
if ($user->hasPermission(['posts.create', 'posts.edit'], true)) {
    // User can create AND edit posts
}
```

## Abilities

> [!NOTE]
> **Abilities are optional**. Enable/disable via `SIMPLE_PERMISSIONS_ABILITIES_ENABLED` in your `.env` file or `config/simple-permissions.php`. When disabled, ability-related migrations won't be published. The `hasAbility()` method will fall back to checking global permissions, and methods like `allowAbility()`, `forbidAbility()`, and `removeAbility()` will throw an exception.

Abilities allow specific permissions for individual entities.

### Creating and Assigning Abilities

You can use helper methods for easier ability management:

```php
// Allow user to edit a specific post
$user->allowAbility('posts.edit', $post);

// Forbid user to edit a specific post
$user->forbidAbility('posts.edit', $post);

// Remove ability from user
$user->removeAbility('posts.edit', $post);
```

Or use the direct approach:

```php
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;

// Create a permission first
$permission = SimplePermissions::model('permission')::create(['code' => 'posts.edit']);

// Create an ability for a specific entity
$ability = SimplePermissions::model('ability')::create([
    'permission_id' => $permission->id,
    'title' => 'Edit Post #1',
    'entity_id' => $post->id,
    'entity_type' => get_class($post),
]);

// Allow user to edit a specific post
$ability->users()->attach($user, ['forbidden' => false]);

// Forbid user to edit a specific post
$ability->users()->attach($user, ['forbidden' => true]);

// Remove ability from user
$ability->users()->detach($user);
```

### Checking an Ability

```php
if ($user->hasAbility('posts.edit', $post)) {
    // User can edit this specific post
}
```

## Groups

> [!NOTE]
> **Groups are optional**. Enable/disable via `SIMPLE_PERMISSIONS_GROUPS_ENABLED` in your `.env` file or `config/simple-permissions.php`. When disabled, group-related migrations won't be published and group functionality will be skipped automatically.

Groups allow organizing users with shared permissions. This is useful when multiple users need the same set of permissions and you want to manage them collectively.

### Creating and Managing Groups

```php
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions;

// Create group
$group = SimplePermissions::model('group')::create(['code' => 'moderators', 'name' => 'Moderators']);

// Assign permissions to group
$permission = SimplePermissions::model('permission')::where('code', 'posts.moderate')->first();
$group->permissions()->attach($permission);

// Add users to group
$group->users()->attach($user);

// Remove users from group
$group->users()->detach($user);
```

## Middleware

The package provides middleware for route protection.

### Usage in Routes

```php
// Check role
Route::middleware(['role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});

// Check permission
Route::middleware(['permission:posts.create'])->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
});

// Check ability
// Format: ability:action,entity_class,route_parameter_name
Route::middleware(['ability:edit,App\Models\Post,post_id'])->group(function () {
    Route::put('/posts/{post_id}', [PostController::class, 'update']);
});
```

### OR Operations

```php
// User must have admin OR root
Route::middleware(['role:admin|root'])->group(function () {
    // ...
});
```

## Blade Directives

The package includes Blade directives for permission checks in views:

```blade
{{-- Check role --}}
@role('admin')
    <button>Admin Panel</button>
@endrole

{{-- Check permission --}}
@permission('posts.create')
    <a href="{{ route('posts.create') }}">New Post</a>
@endpermission

{{-- Check ability --}}
@ability('edit', $post)
    <button>Edit Post</button>
@endability
```

## Policies

The package integrates with Laravel's Policy system.

### Generate a Policy

```bash
php artisan permissions:policy PostPolicy --model=Post
```

### Using the Policy

```php
// In a controller
if ($user->can('view', $post)) {
    // User can view the post
}

// In a view
@can('update', $post)
    <button>Edit</button>
@endcan
```

## Events

The package dispatches events when permissions change, allowing you to hook into these actions:

### Available Events

- `RoleAssigned`: Dispatched when a role is assigned to a user
- `RoleRemoved`: Dispatched when a role is removed from a user
- `AbilityGranted`: Dispatched when an ability is granted to a user
- `AbilityRevoked`: Dispatched when an ability is revoked from a user

### Listening to Events

```php
use Squareetlabs\LaravelSimplePermissions\Events\RoleAssigned;
use Squareetlabs\LaravelSimplePermissions\Events\AbilityGranted;

// In your EventServiceProvider
protected $listen = [
    RoleAssigned::class => [
        // Your listeners here
    ],
    AbilityGranted::class => [
        // Your listeners here
    ],
];
```

### Example Listener

```php
use Squareetlabs\LaravelSimplePermissions\Events\RoleAssigned;

class LogRoleAssignment
{
    public function handle(RoleAssigned $event)
    {
        // Log the role assignment
        Log::info("User {$event->user->id} was assigned role {$event->role->code}");
    }
}
```

## Artisan Commands

The package includes several useful commands:

### Management

```bash
// List all roles
php artisan permissions:roles

// Show role details
php artisan permissions:show-role {role}

// List all permissions
php artisan permissions:list

// Sync permissions from configuration
php artisan permissions:sync

// Export permissions
php artisan permissions:export --format=json

// Import permissions
php artisan permissions:import --file=permissions.json

// Clear permissions cache
php artisan permissions:clear-cache

// Generate a policy
php artisan permissions:policy PostPolicy --model=Post
```
