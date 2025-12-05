<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    | Customize middleware behavior and handling of unauthorized requests.
    */
    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    | Customize middleware behavior and handling of unauthorized requests.
    */
    'middleware' => [

        // Whether to automatically register middleware in the service provider.
        'register' => true,

        // Response method upon unauthorized access: abort or redirect.
        'handling' => 'abort',

        // Handlers for unauthorized access, aligned with the handling method.
        'handlers' => [
            'abort' => [
                'code' => 403,
                'message' => 'User does not have any of the necessary access rights.',
            ],
            'redirect' => [
                'url' => '/home',
                'message' => [
                    'key' => 'error',
                    'content' => '',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Bindings
    |--------------------------------------------------------------------------
    | Define the models used for functionalities and role-based access.
    */
    'models' => [
        'user' => App\Models\User::class,
        'ability' => Squareetlabs\LaravelSimplePermissions\Models\Ability::class,
        'permission' => Squareetlabs\LaravelSimplePermissions\Models\Permission::class,
        'group' => Squareetlabs\LaravelSimplePermissions\Models\Group::class,
        'role' => Squareetlabs\LaravelSimplePermissions\Models\Role::class,
        'auditLog' => Squareetlabs\LaravelSimplePermissions\Models\AuditLog::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    | Specify table names linked to permission-related models.
    */
    'tables' => [
        'permissions' => 'permissions',
        'roles' => 'roles',
        'groups' => 'groups',
        'abilities' => 'abilities',
    ],

    /*
    |--------------------------------------------------------------------------
    | Foreign Keys
    |--------------------------------------------------------------------------
    | Foreign keys for table relationships in package models.
    */
    'foreign_keys' => [
        'user_id' => 'user_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Primary Key Configuration
    |--------------------------------------------------------------------------
    | Configure the type of primary keys used in package tables.
    | Supported types: 'int', 'bigint', 'uuid'
    | 
    | - 'int': Uses standard integer auto-increment IDs
    | - 'bigint': Uses big integer auto-increment IDs (default, recommended)
    | - 'uuid': Uses UUID v4 strings as primary keys
    */
    'primary_key' => [
        'type' => env('SIMPLE_PERMISSIONS_PRIMARY_KEY_TYPE', 'bigint'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Lifecycle
    |--------------------------------------------------------------------------
    | Configure request lifecycle options
    */
    'request' => [
        // Enabling this option caches the permission decision for the request
        'cache_decisions' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Wildcard Permissions
    |--------------------------------------------------------------------------
    | Configure wildcard permission nodes, allowing you to specify super admin
    | permission node(s) that allows a user to perform all actions.
    */
    'wildcards' => [
        'enabled' => false,
        'nodes' => [
            '*',
            '*.*',
            'all'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    | Configure caching for permissions to improve performance.
    */
    'cache' => [
        'enabled' => env('SIMPLE_PERMISSIONS_CACHE_ENABLED', true),
        'driver' => env('SIMPLE_PERMISSIONS_CACHE_DRIVER', 'redis'),
        'ttl' => env('SIMPLE_PERMISSIONS_CACHE_TTL', 3600),
        'prefix' => 'simple_permissions',
        'tags' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Configuration
    |--------------------------------------------------------------------------
    | Configure audit logging for permission-related actions.
    */
    'audit' => [
        'enabled' => env('SIMPLE_PERMISSIONS_AUDIT_ENABLED', false),
        'log_channel' => env('SIMPLE_PERMISSIONS_AUDIT_LOG_CHANNEL', 'permissions'),
        'events' => [
            'role_assigned',
            'permission_granted',
            'permission_revoked',
        ],
    ],

];
