<?php

namespace Squareetlabs\LaravelSimplePermissions\Support\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class FeatureChecker
{
    /**
     * Check if groups feature is enabled and table exists.
     *
     * @return bool
     */
    public static function isGroupsEnabled(): bool
    {
        return Config::get('simple-permissions.features.groups.enabled', true)
            && Schema::hasTable('groups')
            && Schema::hasTable('group_user');
    }

    /**
     * Check if abilities feature is enabled and table exists.
     *
     * @return bool
     */
    public static function isAbilitiesEnabled(): bool
    {
        return Config::get('simple-permissions.features.abilities.enabled', true)
            && Schema::hasTable('abilities')
            && Schema::hasTable('entity_ability');
    }
}
