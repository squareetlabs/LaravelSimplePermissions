<?php

namespace Squareetlabs\LaravelSimplePermissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Config;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions as SimplePermissionsFacade;

class Ability extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['id', 'permission_id', 'title', 'entity_id', 'entity_type'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (Config::get('simple-permissions.primary_key.type') === 'uuid') {
            $this->keyType = 'string';
            $this->incrementing = false;
        }
    }

    /**
     * Get all the users that are assigned this ability.
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(SimplePermissionsFacade::model('user'), 'entity', 'entity_ability')
            ->withPivot('forbidden')
            ->withTimestamps();
    }

    /**
     * Get all the groups that are assigned this ability.
     */
    public function groups(): MorphToMany
    {
        return $this->morphedByMany(SimplePermissionsFacade::model('group'), 'entity', 'entity_ability')
            ->withPivot('forbidden')
            ->withTimestamps();
    }

    /**
     * Get all the roles that are assigned this ability.
     */
    public function roles(): MorphToMany
    {
        return $this->morphedByMany(SimplePermissionsFacade::model('role'), 'entity', 'entity_ability')
            ->withPivot('forbidden')
            ->withTimestamps();
    }
}
