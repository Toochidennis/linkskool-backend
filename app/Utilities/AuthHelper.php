<?php

namespace V3\App\Utilities;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthHelper
{
    // Generate JWT Token
    public static function generateJWT($userId, $name, $role)
    {
        // Load environment variables if required
        EnvLoader::load();

        $secretKey = getenv('JWT_SECRET_KEY');
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600; // Token valid for 1 hour

        $payload = [
            'iss' => 'linkskool.com', // Issuer
            'aud' => 'linkskool.com', // Audience
            'iat' => $issuedAt,         // Issued at
            'exp' => $expirationTime,   // Expiry time
            'data' => [
                'id' => $userId,
                'name' => $name,
                'role' => $role
            ]
        ];

        return JWT::encode($payload, $secretKey, 'HS256');
    }

    // Validate JWT Token
    public function validateJWT($token)
    {
        // Load environment variables if required
        EnvLoader::load();
        $secretKey = getenv('JWT_SECRET_KEY');

        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            return $decoded;
        } catch (\Exception $e) {
            error_log('Token error' . $e->getMessage());

            ResponseHandler::sendJsonResponse(['success' => false, 'message' => 'Invalid or expired token']);
        }
    }

    private static function validateAPIKey($apiKey)
    {
        // Load environment variables if required
        EnvLoader::load();
        $API_KEY = getenv('API_KEY');

        return $apiKey === $API_KEY;
    }

    public static function verifyAPIKey()
    {
        $response = ['success' => false, 'message' => ''];

        if (!isset($_SERVER['HTTP_X_API_KEY']) || empty($_SERVER['HTTP_X_API_KEY'])) {
            http_response_code(400);
            $response['message'] = 'API Key is required.';
            ResponseHandler::sendJsonResponse($response);
        }

        if (!self::validateAPIKey(apiKey: $_SERVER['HTTP_X_API_KEY'])) {
            http_response_code(401);
            $response['message'] = 'Unauthorized: Invalid API Key.';
            ResponseHandler::sendJsonResponse($response);
        }
    }
}
