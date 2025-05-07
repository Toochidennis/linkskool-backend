<?php

namespace V3\App\Services\Portal;

use PDO;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use V3\App\Utilities\EnvLoader;
use V3\App\Models\Portal\Level;
use V3\App\Models\Portal\Staff;
use V3\App\Utilities\HttpStatus;
use V3\App\Models\Portal\Course;
use V3\App\Models\Portal\Student;
use V3\App\Models\Portal\ClassModel;
use V3\App\Utilities\ResponseHandler;
use V3\App\Models\Portal\SchoolSettings;

class AuthService
{
    private Level $level;
    private Course $course;
    private Staff $staffModel;
    private Student $studentModel;
    private ClassModel $classModel;
    private SchoolSettings $schoolSettings;

    /**
     * AuthenticationService constructor.
     *
     * @param PDO $db A PDO connection to the school database.
     */
    public function __construct(PDO $pdo)
    {
        $this->staffModel = new Staff($pdo);
        $this->studentModel = new Student($pdo);
        $this->level = new Level($pdo);
        $this->course = new Course($pdo);
        $this->classModel = new ClassModel($pdo);
        $this->schoolSettings = new SchoolSettings($pdo);
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
        // Attempt login as staff
        $staff = $this->staffModel
            ->select(columns: ['id', 'staff_no', 'surname', 'access_level', 'password'])
            ->where('staff_no', '=', $username)
            ->first();

        if ($staff && $this->verifyPassword($staff['password'], $password)) {
            return $this->generateLoginResponse(
                id: $staff['id'],
                name: $staff['surname'],
                accessLevel: $staff['access_level']
            );
        }

        // Attempt login as student
        $student = $this->studentModel
            ->select(columns: ['id', 'registration_no', 'surname', 'password'])
            ->where('registration_no', '=', $username)
            ->first();

        if ($student && $this->verifyPassword($student['password'], $password)) {
            return [
                'token' => $this->generateJWT($student['id'], $student['surname'], 'student'),
                'role'  => 'student',
                'data'  => $student
            ];
        }

        throw new Exception('Invalid credentials.');
    }

    /**
     * Generates a login response based on the user's ID and access level.
     *
     * This function determines the user's role based on the given access level
     * and fetches the corresponding data for the user (admin or staff).
     * It also generates a JWT token for authentication.
     *
     * @param int    $id          The unique identifier of the user.
     * @param string $name        The name of the user.
     * @param int    $accessLevel The access level of the user (1, 2, or 3).
     *
     * @throws Exception If the access level is not recognized.
     *
     * @return array The response containing user data and a JWT token.
     */

    private function generateLoginResponse(int $id, string $name, int $accessLevel)
    {
        $role = match ($accessLevel) {
            2 => 'admin',
            1, 3 => 'staff',
            default => throw new Exception('Forbidden'),
        };

        $data = match ($role) {
            'admin' => $this->getAdminData($id),
            'staff' => $this->getStaffData($id),
            default => [],
        };

        return [
            'data'  => $data,
            'token' => $this->generateJWT(userId: $id, name: $name, role: $role)
        ];
    }

    private function getAdminData(int $id): array
    {
        return [
            'profile' => $this->staffModel
                ->select(["CONCAT(surname, ' ', first_name, ' ', middle) AS name", 'email'])
                ->where('id', '=', $id)
                ->first() + ['role' => 'admin'],

            'settings' => $this->schoolSettings
                ->select(['name AS school_name', 'year', 'term'])
                ->first() ?? [],

            'classes' => $this->classModel
                ->select(['id', 'class_name', 'level AS level_id', 'form_teacher'])
                ->get() ?? [],

            'levels' => $this->level
                ->select(['id', 'level_name'])
                ->get() ?? [],
            "courses" => $this->course
                ->select(['id', 'course_name'])
                ->get() ?? []
        ];
    }

    private function getStaffData($id)
    {
        return [
            'profile' => $this->staffModel
                ->select(["CONCAT(surname, ' ', first_name, ' ', middle) AS name", 'email'])
                ->where('id', '=', $id)
                ->first() + ['role' => 'staff']
        ];
    }

    // Generate JWT Token
    private function generateJWT($userId, $name, $role)
    {
        EnvLoader::load();
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

        $headers = array_change_key_case(getallheaders(), CASE_LOWER);

        // die(print_r($headers));
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
