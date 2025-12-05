<?php

namespace Squareetlabs\LaravelSimplePermissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class AuditLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'properties',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'properties' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(Config::get('simple-permissions.audit.table_name', 'audit_logs'));
    }

    /**
     * Get the user that performed the action.
     */
    public function user()
    {
        return $this->belongsTo(Config::get('auth.providers.users.model'));
    }

    /**
     * Get the subject of the audit log.
     */
    public function subject()
    {
        return $this->morphTo();
    }
}
