<?php

namespace V3\App\Utilities;

class Permission {
    private static array $permissions = [
        'admin' => ['create_student', 'view_student', 'update_student', 'delete_student', 'take_attendance', 'create_course', '...'],
        'staff' => ['view_student', 'take_attendance', 'update_attendance', 'view_course'],
        'student' => ['view_student']
    ];

    /**
     * Checks if a given role has permission to perform a specified action.
     *
     * @param string $action The action to check.
     * @param string $role   The role of the user.
     *
     * @return bool
     */
    public static function hasPermission(string $action, string $role): bool
    {
        return isset(self::$permissions[$role]) && in_array($action, self::$permissions[$role]);
    }
}
