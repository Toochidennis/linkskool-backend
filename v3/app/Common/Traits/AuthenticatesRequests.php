<?php

namespace V3\App\Common\Traits;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Common\Utilities\ResponseHandler;

trait AuthenticatesRequests
{
    private static function getSecretKey(): string
    {
        return getenv('JWT_SECRET_KEY');
    }

    private static function generateJWT(string $userId, string $name, string $role, string $type = 'access'): string
    {
        $issuedAt = time();
        $expirationTime = $type === 'refresh'
            ? $issuedAt + 7776000  // 90 days
            : $issuedAt + 172800; // 2 days
        $payload = [
            'iss'  => 'linkskool.com',
            'aud'  => 'linkskool.com',
            'iat'  => $issuedAt,
            'exp'  => $expirationTime,
            'type' => $type,
            'data' => [
                'id'   => $userId,
                'name' => $name,
                'role' => $role
            ]
        ];

        return JWT::encode($payload, self::getSecretKey(), 'HS256');
    }


    protected static function decodeJWT(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key(self::getSecretKey(), 'HS256'));
            return json_decode(json_encode($decoded), true);
        } catch (ExpiredException $e) {
            ResponseHandler::sendJsonResponse([
                'success' => false,
                'message' => 'Token expired.',
                'status'  => HttpStatus::UNAUTHORIZED
            ]);
        } catch (Exception $e) {
            ResponseHandler::sendJsonResponse([
                'success' => false,
                'message' => 'Authentication failed.',
                'status'  => HttpStatus::UNAUTHORIZED
            ]);
        }
    }

    public static function refreshToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key(self::getSecretKey(), 'HS256'));
            $data = json_decode(json_encode($decoded), true);
        } catch (ExpiredException $e) {
            ResponseHandler::sendJsonResponse([
                'success' => false,
                'message' => 'Session expired. Please log in again.',
                'status'  => HttpStatus::UNAUTHORIZED
            ]);
        } catch (Exception $e) {
            ResponseHandler::sendJsonResponse([
                'success' => false,
                'message' => 'Authentication failed.',
                'status'  => HttpStatus::UNAUTHORIZED
            ]);
        }

        if (!isset($data['data']['id'], $data['data']['name'], $data['data']['role'])) {
            ResponseHandler::sendJsonResponse([
                'success' => false,
                'message' => 'Authentication failed.',
                'status'  => HttpStatus::UNAUTHORIZED
            ]);
        }

        if (($data['type'] ?? '') !== 'refresh') {
            ResponseHandler::sendJsonResponse([
                'success' => false,
                'message' => 'Authentication failed.',
                'status'  => HttpStatus::UNAUTHORIZED
            ]);
        }

        $accessToken = self::generateJWT($data['data']['id'], $data['data']['name'], $data['data']['role']);

        return [
            'access_token'  => $accessToken,
            'refresh_token' => self::generateJWT($data['data']['id'], $data['data']['name'], $data['data']['role'], 'refresh'),
        ];
    }

    private static function validateAPIKey(string $apiKey): bool
    {
        $API_KEY = getenv('API_KEY');
        return hash_equals($API_KEY, $apiKey);
    }

    public static function verifyJWT(): void
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (!str_starts_with($authHeader, 'Bearer ')) {
            ResponseHandler::sendJsonResponse([
                'success' => false,
                'message' => 'Authentication failed.',
                'status'  => HttpStatus::UNAUTHORIZED
            ]);
        }

        $token = substr($authHeader, 7);
        self::decodeJWT($token);
    }

    public static function verifyAPIKey(): void
    {
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);
        $apiKey = $headers['x-api-key'] ?? '';

        if (empty($apiKey)) {
            ResponseHandler::sendJsonResponse([
                'success' => false,
                'message' => 'API Key is required.',
                'status'  => HttpStatus::BAD_REQUEST
            ]);
        }

        if (!self::validateAPIKey($apiKey)) {
            ResponseHandler::sendJsonResponse([
                'success' => false,
                'message' => 'Unauthorized: Invalid API Key.',
                'status'  => HttpStatus::UNAUTHORIZED
            ]);
        }
    }
}
