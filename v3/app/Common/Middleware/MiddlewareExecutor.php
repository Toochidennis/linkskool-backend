<?php

namespace V3\App\Common\Middleware;

use V3\App\Common\Utilities\ResponseHandler;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Common\Traits\AuthenticatesRequests;

class MiddlewareExecutor
{
    use AuthenticatesRequests;

    public static function run(array $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            if ($middleware === 'auth') {
                self::verifyJWT();
            } elseif (str_starts_with($middleware, 'role:')) {
                $role = explode(':', $middleware)[1];
                self::verifyRole($role);
            }
        }
    }

    private static function verifyRole(string $requiredRole): void
    {
        $payload = self::decodeJWT();
        if (!isset($payload['role']) || $payload['role'] !== $requiredRole) {
            ResponseHandler::sendJsonResponse([
                'message' => 'Forbidden: insufficient privileges',
                'status'  => HttpStatus::FORBIDDEN
            ]);
        }
    }
}
