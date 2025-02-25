<?php
namespace V3\App\Traits;

use V3\App\Utilities\Permission;

trait PermissionTrait
{
    /**
     * Checks if the current user (by role) is allowed to perform a given action.
     *
     * @param string $action The action to be performed (e.g., 'create_student', 'view_student', 'take_attendance', etc.)
     * @param string $role   The role of the user (e.g., 'admin', 'staff', 'student').
     *
     * @return bool True if allowed, false otherwise.
     */
    public function checkPermission(string $action, string $role): bool
    {
        return Permission::hasPermission($action, $role);
    }
}
