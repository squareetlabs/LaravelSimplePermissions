<?php

namespace Squareetlabs\LaravelSimplePermissions\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Config;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions as SimplePermissionsFacade;

class Group extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['code', 'name'];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
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

        static::deleting(static function ($group) {
            $group->permissions()->detach();
            $group->abilities()->detach();

        });
    }

    /**
     * Get all group users
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(SimplePermissionsFacade::model('user'), 'group_user', 'group_id', 'user_id');
    }

    /**
     * Get the permissions that belongs to group.
     *
     * @return MorphToMany
     */
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(SimplePermissionsFacade::model('permission'), 'entity', 'entity_permission');
    }

    /**
     * Get the abilities that belongs to group.
     *
     * @return MorphToMany
     */
    public function abilities(): MorphToMany
    {
        return $this->morphToMany(SimplePermissionsFacade::model('ability'), 'entity', 'entity_ability')
            ->withPivot('forbidden')
            ->withTimestamps();
    }

    /**
     * Attach user or users to a group
     *
     * @param Collection|Model $user
     * @return bool
     */
    public function attachUser(Collection|Model $user): bool
    {
        // Convert user to collection if it is the only model
        $users = $user instanceof Collection ? $user : collect([$user]);

        // If there are users left after filtering, synchronize and return the result
        return $users->isNotEmpty() && $this->users()->syncWithoutDetaching($users->pluck('id')) > 0;
    }

    /**
     * Detach user or users from group
     */
    public function detachUser(Collection|Model $user): bool
    {
        // Convert user to collection if it is the only model
        $users = $user instanceof Collection ? $user : collect([$user]);

        // If there are any users left after filtering, we execute detach and return the result
        return $users->isNotEmpty() && $this->users()->detach($users->pluck('id')) > 0;
    }
}
