<?php

namespace V3\App\Common\Traits;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Common\Utilities\ResponseHandler;

trait AuthenticatesRequests
{
    // Generate JWT Token
    private static function generateJWT($userId, $name, $role)
    {
        $secretKey = getenv('JWT_SECRET_KEY');
        $issuedAt = time();
        $expirationTime = $issuedAt + 2592000; // Token valid for 30 days

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
        $secretKey = getenv('JWT_SECRET_KEY');

        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

            $_SESSION['user_id'] = $decoded->data->id;
            $_SESSION['role'] = $decoded->data->role;

            return $decoded;
        } catch (\Firebase\JWT\ExpiredException $e) {
            $payload = explode('.', $token)[1];
            $decoded = json_decode(base64_decode($payload));

            $newToken = self::generateJWT(
                $decoded->data->id,
                $decoded->data->name,
                $decoded->data->role
            );

            http_response_code(HttpStatus::UNAUTHORIZED);
            ResponseHandler::sendJsonResponse([
                'success' => true,
                'statusCode' => 401,
                'message' => 'Token refreshed.',
                'token' => $newToken
            ]);
        } catch (Exception $e) {
            http_response_code(HttpStatus::UNAUTHORIZED);
            ResponseHandler::sendJsonResponse([
                'success' => false,
                'message' => 'Invalid token.'
            ]);
        }
    }

    private static function validateAPIKey($apiKey): bool
    {
        $API_KEY = getenv('API_KEY');
        return hash_equals($API_KEY, $apiKey);
    }

    public static function verifyJWT()
    {
        $response = ['success' => false, 'message' => ''];
        $headers = getallheaders();
        if (!isset($headers['Authorization']) || empty($headers['Authorization'])) {
            http_response_code(HttpStatus::BAD_REQUEST);
            $response['message'] = 'Token is required.';
            ResponseHandler::sendJsonResponse($response);
        }

        $authHeader = $headers['Authorization'];
        if (!str_starts_with($authHeader, 'Bearer ')) {
            http_response_code(HttpStatus::BAD_REQUEST);
            $response['message'] = "Invalid token. Are you missing 'Bearer '?";
            ResponseHandler::sendJsonResponse($response);
        }

        $token = substr($authHeader, 7);

        if (!self::validateJWT($token)) {
            http_response_code(HttpStatus::UNAUTHORIZED);
            $response['message'] = 'Unauthorized: Have you logged in?';
            ResponseHandler::sendJsonResponse($response);
        }
    }

    public static function verifyAPIKey()
    {
        $response = ['success' => false, 'message' => ''];

        $headers = array_change_key_case(getallheaders(), CASE_LOWER);

        if (!isset($headers['x-api-key']) || empty($headers['x-api-key'])) {
            http_response_code(HttpStatus::BAD_REQUEST);
            $response['message'] = 'API Key is required.';
            ResponseHandler::sendJsonResponse(response: $response);
        }

        if (!self::validateAPIKey(apiKey: $headers['x-api-key'])) {
            http_response_code(HttpStatus::UNAUTHORIZED);
            $response['message'] = 'Unauthorized: Invalid API Key.';
            ResponseHandler::sendJsonResponse($response);
        }
    }
}
