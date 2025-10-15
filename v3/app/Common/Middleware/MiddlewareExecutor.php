<?php

namespace V3\App\Common\Middleware;

use V3\App\Common\Traits\AuthenticatesRequests;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Common\Utilities\ResponseHandler;

class MiddlewareExecutor
{
    use AuthenticatesRequests;

    public static function run(array $middlewares): void
    {
        $allowedRoles = [];

        foreach ($middlewares as $middleware) {
            if ($middleware === 'api') {
                self::verifyAPIKey();
            } elseif ($middleware === 'auth') {
                self::verifyAPIKey();
                self::verifyJWT();
            } elseif (str_starts_with($middleware, 'role:')) {
                $allowedRoles[] = explode(':', $middleware)[1];
            }
        }

        if (!empty($allowedRoles)) {
            self::verifyRole($allowedRoles);
        }
    }

    private static function verifyRole(array $allowedRoles): void
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (!str_starts_with($authHeader, 'Bearer ')) {
            ResponseHandler::sendJsonResponse([
                'success' => false,
                'message' => "Invalid token. Missing 'Bearer ' prefix.",
                'status'  => HttpStatus::BAD_REQUEST
            ]);
        }

        $token = substr($authHeader, 7);
        $payload = self::decodeJWT($token);
        $userRole = $payload['data']['role'] ?? null;

        if (!$userRole || !in_array($userRole, $allowedRoles, true)) {
            ResponseHandler::sendJsonResponse([
                'success' => false,
                'message' => 'Forbidden: insufficient privileges',
                'status'  => HttpStatus::FORBIDDEN
            ]);
        }

        $_SESSION['role'] = $userRole;
    }
}
