<?php

namespace Squareetlabs\LaravelSimplePermissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Squareetlabs\LaravelSimplePermissions\Support\Facades\SimplePermissions as SimplePermissionsFacade;
use Squareetlabs\LaravelSimplePermissions\Rules\ValidPermission;

class Permission extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['name', 'code'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

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

        // Validate permission code format before saving
        static::saving(function ($permission) {
            if (isset($permission->code)) {
                $validator = Validator::make(
                    ['code' => $permission->code],
                    ['code' => [new ValidPermission()]]
                );

                if ($validator->fails()) {
                    throw new \Illuminate\Validation\ValidationException($validator);
                }
            }
        });
    }

    /**
     * Get all the groups that are assigned this permission.
     */
    public function groups(): MorphToMany
    {
        return $this->morphedByMany(SimplePermissionsFacade::model('group'), 'entity', 'entity_permission');
    }

    /**
     * Get all the roles that are assigned this permission.
     */
    public function roles(): MorphToMany
    {
        return $this->morphedByMany(SimplePermissionsFacade::model('role'), 'entity', 'entity_permission');
    }
}
