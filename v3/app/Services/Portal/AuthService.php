<?php

namespace V3\App\Services\Portal;

use PDO;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use V3\App\Models\Portal\Staff;
use V3\App\Utilities\EnvLoader;
use V3\App\Models\Portal\Student;
use V3\App\Utilities\HttpStatus;
use V3\App\Utilities\ResponseHandler;

class AuthService
{
    private Staff $staffModel;
    private Student $studentModel;

    /**
     * AuthenticationService constructor.
     *
     * @param PDO $db A PDO connection to the school database.
     */
    public function __construct(PDO $db)
    {
        $this->staffModel = new Staff($db);
        $this->studentModel = new Student($db);
    }

    /**
     * Attempts to authenticate a user by username and password.
     *
     * @param string $username The staff_no or registration_no.
     * @param string $password The password provided by the user.
     *
     * @return array Returns an array containing the generated token, the user role, and user data.
     * @throws Exception If the user is not found or the password is invalid.
     */
    public function login(string $username, string $password): array
    {
        // Try fetching the staff record
        $staff = $this->staffModel
            ->select(columns: ['id', 'staff_no', 'surname', 'access_level', 'password'])
            ->where('staff_no', '=', $username)
            ->first();

        if ($staff) {
            if (!$this->verifyPassword($staff['password'], $password)) {
                throw new Exception('Invalid password.');
            }

            // Determine role based on access level.
            $role = match ($staff['access_level']) {
                2 => 'admin',
                1, 3 => 'staff',
                default => throw new Exception('Forbidden'),
            };

            $token = $this->generateJWT(
                userId: $staff['id'],
                name: $staff['surname'],
                role: $role
            );

            return [
                'token' => $token,
                'role'  => $role,
                'user'  => $staff
            ];
        }

        // If no staff record, try fetching the student record.
        $student = $this->studentModel
            ->select(columns: ['id', 'registration_no', 'surname', 'password'])
            ->where('registration_no', '=', $username)
            ->first();

        if (!$student) {
            throw new Exception('User not found.');
        }

        if (!$this->verifyPassword($student['password'], $password)) {
            throw new Exception('Invalid password.');
        }

        $token = $this->generateJWT(
            userId: $student['id'],
            name: $student['surname'],
            role: 'student'
        );

        return [
            'token' => $token,
            'role'  => 'student',
            'user'  => $student
        ];
    }

    // Generate JWT Token
    private function generateJWT($userId, $name, $role)
    {
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
        try {
            EnvLoader::load();
            $secretKey = getenv('JWT_SECRET_KEY');
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

            $_SESSION['user_id'] = $decoded->data->id;
            $_SESSION['role'] = $decoded->data->role;

            return $decoded;
        } catch (Exception $e) {
            http_response_code(HttpStatus::BAD_REQUEST);
            error_log('Token error' . $e->getMessage());
            ResponseHandler::sendJsonResponse(['success' => false, 'message' => 'Invalid or expired token']);
        }
    }

    private static function validateAPIKey($apiKey): bool
    {
        EnvLoader::load();
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

        $headers = getallheaders();

        #die(print_r($headers));
        if (!isset($headers['x-api-key']) || empty($headers['x-api-key'])) {
            http_response_code(HttpStatus::BAD_REQUEST);
            $response['message'] = 'API Key is required.';
            ResponseHandler::sendJsonResponse($response);
        }

        if (!self::validateAPIKey(apiKey: $headers['x-api-key'])) {
            http_response_code(HttpStatus::UNAUTHORIZED);
            $response['message'] = 'Unauthorized: Invalid API Key.';
            ResponseHandler::sendJsonResponse($response);
        }
    }

    private function verifyPassword(string $userPassword, string $password): bool
    {
        $passwordHash = password_hash($userPassword, PASSWORD_DEFAULT);
        return password_verify($password, $passwordHash);
    }
}
