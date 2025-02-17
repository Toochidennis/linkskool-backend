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
    private static function validateJWT($token)
    {
        // Load environment variables if required
        EnvLoader::load();
        $secretKey = getenv('JWT_SECRET_KEY');

        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

            $_SESSION['user_id'] = $decoded->data->id;
            $_SESSION['role'] = $decoded->data->role;
            
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


    public static function verifyJWT() {
        $response = ['success' => false, 'message' => ''];

        $headers = getallheaders();
        if(!isset($headers['Authorization']) || empty($headers['Authorization'])){
            http_response_code(400);
            $response['message'] = 'Token is required.';
            ResponseHandler::sendJsonResponse($response);
        }

        $authHeader = $headers['Authorization'];
        if(!str_starts_with($authHeader, 'Bearer ')){
            http_response_code(400);
            $response['message'] = "Invalid token. Are you missing 'Bearer '?";
            ResponseHandler::sendJsonResponse($response);
        }

        $token = substr($authHeader, 7);

        if(!self::validateJWT($token)){
            http_response_code(401);
            $response['message'] = 'Unauthorized: Have you logged in?';
            ResponseHandler::sendJsonResponse($response);
        }
    }

    public static function verifyAPIKey()
    {
        $response = ['success' => false, 'message' => ''];

        $headers = getallheaders();

        #die(print_r($headers));
        if (!isset($headers['x-api-key']) || empty($headers['x-api-key'])) {
            http_response_code(400);
            $response['message'] = 'API Key is required.';
            ResponseHandler::sendJsonResponse($response);
        }

        if (!self::validateAPIKey(apiKey: $headers['x-api-key'])) {
            http_response_code(401);
            $response['message'] = 'Unauthorized: Invalid API Key.';
            ResponseHandler::sendJsonResponse($response);
        }
    }
}
