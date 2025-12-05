<?php

namespace Squareetlabs\LaravelSimplePermissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Config;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions as SimplePermissionsFacade;

class Role extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['code', 'name', 'description'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'permissions',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'permissions',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (Config::get('simple-permissions.primary_key.type') === 'uuid') {
            $this->keyType = 'string';
            $this->incrementing = false;
        }
    }

    /**
     * Bootstrap any application services.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::deleting(static function ($role) {
            $role->permissions()->detach();
            $role->abilities()->detach();
        });

    }

    /**
     * Get the permissions that belongs to role.
     *
     * @return MorphToMany
     */
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(SimplePermissionsFacade::model('permission'), 'entity', 'entity_permission');
    }

    /**
     * Get the abilities that belongs to role.
     *
     * @return MorphToMany
     */
    public function abilities(): MorphToMany
    {
        return $this->morphToMany(SimplePermissionsFacade::model('ability'), 'entity', 'entity_ability')
            ->withPivot('forbidden')
            ->withTimestamps();
    }
}
