<?php

namespace Squareetlabs\LaravelSimplePermissions\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PermissionRevoked
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param mixed $user
     * @param mixed $permission
     */
    public function __construct(
        public $user,
        public $permission
    ) {
    }
}
