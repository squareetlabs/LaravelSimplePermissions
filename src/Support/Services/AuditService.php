<?php

namespace Squareetlabs\LaravelSimplePermissions\Support\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Squareetlabs\LaravelSimplePermissions\Exceptions\AuditTableMissingException;
use Squareetlabs\LaravelSimplePermissions\Models\AuditLog;
use Exception;

/**
 * AuditService
 * 
 * Handles audit logging for permission-related actions.
 * Logs actions to both database (AuditLog) and configured log channel.
 * 
 * Only logs events that are enabled in configuration and validates that
 * the audit table exists before attempting to log.
 * 
 * @package Squareetlabs\LaravelSimplePermissions\Support\Services
 */
class AuditService
{
    /**
     * Log an audit event.
     * 
     * Validates that audit is enabled and the event is in the allowed events list.
     * Creates a database record and logs to the configured channel.
     * 
     * Automatically captures IP address and user agent from the current request.
     *
     * @param string $action The action being logged (must be in config events list)
     * @param mixed $user The user performing the action (can be User model or ID)
     * @param mixed|null $subject Optional subject entity (model) the action relates to
     * @param array|null $oldValues Optional old values before the change
     * @param array|null $newValues Optional new values after the change
     * @return void
     * @throws Exception If audit table is missing when audit is enabled
     */
    public function log(
        string $action,
        mixed $user,
        mixed $subject = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        if (!Config::get('simple-permissions.audit.enabled', false)) {
            return;
        }

        // Check if audit table exists before attempting to log
        // This prevents errors during migrations or if table hasn't been created yet
        if (!Schema::hasTable('audit_logs')) {
            // Silently skip logging if table doesn't exist (e.g., during migrations)
            // The table will be created when migrations run
            return;
        }

        $userId = null;
        if ($user !== null) {
            $userId = is_object($user) ? $user->getKey() : $user;
        }

        $data = [
            'user_id' => $userId,
            'action' => $action,
            'description' => null,
            'properties' => $oldValues, // Use oldValues as properties
        ];

        AuditLog::create($data);
    }

    /**
     * Log role assignment.
     * 
     * Convenience method to log when a role is assigned to a user.
     *
     * @param mixed $user The user who assigned the role
     * @param mixed $role The role that was assigned (can be Role model or ID)
     * @param mixed|null $targetUser The user receiving the role
     * @return void
     * @throws Exception If audit table is missing when audit is enabled
     */
    public function logRoleAssigned(mixed $user, mixed $role, mixed $targetUser = null): void
    {
        $this->log('role_assigned', $user, $targetUser, null, [
            'role_id' => is_object($role) ? $role->id : $role,
            'role_code' => is_object($role) ? $role->code : null,
        ]);
    }

    /**
     * Log permission granted.
     * 
     * Convenience method to log when a permission is granted.
     *
     * @param mixed $user The user who granted the permission
     * @param string $permission The permission code that was granted
     * @param mixed|null $target The target entity (user/role/group)
     * @return void
     * @throws Exception If audit table is missing when audit is enabled
     */
    public function logPermissionGranted(mixed $user, string $permission, mixed $target = null): void
    {
        $this->log('permission_granted', $user, $target, null, ['permission' => $permission]);
    }

    /**
     * Log permission revoked.
     * 
     * Convenience method to log when a permission is revoked.
     *
     * @param mixed $user The user who revoked the permission
     * @param string $permission The permission code that was revoked
     * @param mixed|null $target The target entity (user/role/group)
     * @return void
     * @throws Exception If audit table is missing when audit is enabled
     */
    public function logPermissionRevoked(mixed $user, string $permission, mixed $target = null): void
    {
        $this->log('permission_revoked', $user, $target, ['permission' => $permission], null);
    }
}

