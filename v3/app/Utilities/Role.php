<?php
namespace V3\App\Utilities;

class Role{
    public static function checkAccess($requiredRoles = [])
        {
            session_start();
            $userRole = $_SESSION['role'] ?? 'guest'; // Default to guest
    
            if (!in_array($userRole, $requiredRoles)) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Access denied. Insufficient permissions.',
                ]);
                exit;
            }
        }
}