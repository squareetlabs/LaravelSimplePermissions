<?php

namespace Squareetlabs\LaravelSimplePermissions\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidPermission implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        // Allow single wildcard
        if ($value === '*') {
            return true;
        }

        // Valid permission format: lowercase letters, numbers, underscores, hyphens, dots, and wildcards (*)
        // Examples: 'posts.view', 'users.create', 'admin.settings.edit', 'posts.*', '*.*'
        // Allow wildcards at the end of segments: posts.*, posts.view.*, etc.
        // Pattern: segments separated by dots, each segment can be alphanumeric with _ or - or end with *
        return preg_match('/^([a-z0-9_\-]+|\*)(\.([a-z0-9_\-]+|\*))*$/', $value) === 1;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute must be a valid permission code (e.g., posts.view, users.create).';
    }
}

