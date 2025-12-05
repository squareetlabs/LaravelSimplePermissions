<?php

namespace Squareetlabs\LaravelSimplePermissions\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AbilityGranted
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param mixed $user
     * @param string $permission
     * @param mixed $entity
     */
    public function __construct(
        public $user,
        public string $permission,
        public $entity
    ) {
    }
}

