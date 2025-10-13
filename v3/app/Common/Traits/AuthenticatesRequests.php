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
        return getenv('JWT_SECRET_KEY') ?: 'fallback_secret';
    }

    private static function generateJWT(string $userId, string $name, string $role): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + 2592000; // 30 days
        $payload = [
            'iss'  => 'linkskool.com',
            'aud'  => 'linkskool.com',
            'iat'  => $issuedAt,
            'exp'  => $expirationTime,
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
            $decoded = json_decode(base64_decode(explode('.', $token)[1] ?? ''), true);

            if (!isset($decoded['data'])) {
                ResponseHandler::sendJsonResponse([
                    'success' => false,
                    'message' => 'Token structure invalid or corrupted.',
                    'status'  => HttpStatus::UNAUTHORIZED
                ]);
            }

            $newToken = self::generateJWT(
                $decoded['data']['id'],
                $decoded['data']['name'],
                $decoded['data']['role']
            );

            ResponseHandler::sendJsonResponse([
                'success' => true,
                'message' => 'Token refreshed.',
                'token'   => $newToken,
                'status'  => HttpStatus::UNAUTHORIZED
            ]);
        } catch (Exception $e) {
            ResponseHandler::sendJsonResponse([
                'success' => false,
                'message' => 'Invalid or malformed token.',
                'status'  => HttpStatus::UNAUTHORIZED
            ]);
        }
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
                'message' => "Invalid token. Missing 'Bearer ' prefix.",
                'status'  => HttpStatus::BAD_REQUEST
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
