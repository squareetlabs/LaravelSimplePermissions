<?php

namespace Squareetlabs\LaravelSimplePermissions\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleRemoved
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param mixed $user
     * @param mixed $role
     */
    public function __construct(
        public $user,
        public $role
    ) {
    }
}

