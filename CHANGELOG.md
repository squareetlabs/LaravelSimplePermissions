# Changelog

All notable changes to `squareetlabs/laravel-simple-permissions` will be documented in this file.

## [1.0.6] - 2026-01-21

### Fixed
- **Critical Fix**: `allPermissions()` method now correctly filters out forbidden permissions
  - Previously, the method returned all permissions from roles/groups without excluding permissions marked as `forbidden=true`
  - This caused inconsistency: `hasPermission()` correctly denied forbidden permissions, but `allPermissions()` still included them in the returned array
  - **Impact**: This primarily affected API responses and data exports where `allPermissions()` was used. Authorization checks using `hasPermission()`, Gates, Policies, and Middleware were **not affected** and worked correctly
  - **Solution**: Added filtering logic to exclude permissions with `forbidden=true` from the final permissions array

### Added
- Comprehensive unit tests for forbidden permissions functionality:
  - `test_all_permissions_excludes_forbidden_permissions()` - Verifies forbidden permissions are excluded
  - `test_all_permissions_is_consistent_with_has_permission()` - Ensures consistency between methods
  - `test_forbidden_permissions_have_highest_priority()` - Validates priority order
  - `test_remove_permission_returns_to_role_based_permissions()` - Tests permission restoration
  - `test_multiple_forbidden_permissions_are_all_excluded()` - Validates bulk forbidden handling

### Improved
- Enhanced README documentation with detailed explanation of forbidden permissions
- Added comparison table showing when to use `givePermission()`, `revokePermission()`, and `removePermission()`
- Clarified permission priority order in documentation

### Technical Details
The fix adds the following logic to `allPermissions()` method (after line 249):

```php
// Get forbidden permissions (these override role/group permissions)
$forbiddenPermissions = $this->permissions()
    ->wherePivot('forbidden', true)
    ->get()
    ->pluck('code')
    ->toArray();

// Remove forbidden permissions from the final list
if (!empty($forbiddenPermissions)) {
    $permissions = array_diff($permissions, $forbiddenPermissions);
}

return array_values(array_unique($permissions));
```

### Migration Guide
This is a **non-breaking change**. No action is required to upgrade. The fix ensures that `allPermissions()` correctly reflects the actual permissions a user has, matching the behavior of `hasPermission()`.

If you were working around this issue in your code, you can now safely remove those workarounds.

---

## [1.0.5] - 2025-12-15

### Added
- Initial stable release
- Role-based access control (RBAC)
- Permission system with wildcard support
- Optional groups feature
- Optional abilities feature (entity-specific permissions)
- Direct permission assignments (givePermission, revokePermission, removePermission)
- Smart caching system
- Audit logging
- Blade directives
- Middleware for route protection
- Laravel Policy integration
- Event system
- Artisan commands

### Features
- PHP 8.1+ support
- Laravel 8.x - 12.x compatibility
- Comprehensive documentation
- Unit and feature tests
