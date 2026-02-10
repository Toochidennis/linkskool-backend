<?php

namespace V3\App\Services\Portal;

use PDO;
use Exception;
use V3\App\Common\Traits\AuthenticatesRequests;
use V3\App\Models\Portal\Academics\{ClassModel, SchoolSettings, Course, Level, Staff, Student};

class AuthService
{
    use AuthenticatesRequests;

    private Level $level;
    private Course $course;
    private Staff $staffModel;
    private Student $studentModel;
    private ClassModel $classModel;
    private SchoolSettings $schoolSettings;


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
            ->where('password', '=', $password)
            ->first();

        if ($staff && $this->verifyPassword($staff['password'], $password)) {
            return $this->generateLoginResponse(
                id: $staff['id'],
                name: $staff['surname'] ?? '',
                accessLevel: $staff['access_level']
            );
        }

        // Attempt login as student
        $student = $this->studentModel
            ->select(columns: ['id', 'surname', 'password'])
            ->where('registration_no', '=', $username)
            ->first();

        if ($student && $this->verifyPassword($student['password'], $password)) {
            return [
                'data'  => $this->getStudentData($student['id']),
                'token' => self::generateJWT($student['id'], $student['surname'], 'student'),
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

    private function generateLoginResponse(int $id, string $name, int $accessLevel): array
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
            'data' => $data,
            'token' => self::generateJWT(userId: $id, name: $name, role: $role)
        ];
    }

    private function getAdminData(int $id): array
    {
        return [
            'profile' => $this->staffModel
                ->select(['id as staff_id', "CONCAT(surname, ' ', first_name, ' ', middle) AS name", 'email'])
                ->where('id', '=', $id)
                ->first() + ['role' => 'admin'],

            'settings' => $this->getSchoolSetting(),

            'classes' => $this->classModel
                ->select(['id', 'class_name', 'level AS level_id', 'form_teacher'])
                ->get(),

            'levels' => $this->level
                ->select(['id', 'level_name'])
                ->get(),
            "courses" => $this->course
                ->select(['id', 'course_name'])
                ->get()
        ];
    }

    private function getStaffData($id): array
    {
        return [
            'profile' => $this->staffModel
                ->select(["id AS staff_id, CONCAT(surname, ' ', first_name, ' ', middle) AS name", 'email'])
                ->where('id', '=', $id)
                ->first() + ['role' => 'staff'],

            'settings' => $this->getSchoolSetting(),

            'form_classes' => $this->getLevelsAndClasses($id),

            'courses' => $this->getStaffAssignedCourses($id)
        ];
    }

    private function getStudentData($id): array
    {
        return [
            'profile' => $this->studentModel
                ->select([
                    'students_record.id',
                    'students_record.picture_ref AS picture_url',
                    "CONCAT(surname, ' ', first_name, ' ', middle) AS name",
                    'students_record.registration_no',
                    'students_record.student_class AS class_id',
                    'students_record.student_level AS level_id',
                    'class_table.class_name'
                ])
                ->join('class_table', 'students_record.student_class = class_table.id')
                ->where('students_record.id', '=', $id)
                ->first() + ['role' => 'student'],

            'settings' => $this->getSchoolSetting(),
        ];
    }

    private function getSchoolSetting(): array
    {
        return $this->schoolSettings
            ->select(['name AS school_name', 'year', 'term'])
            ->first();
    }

    public function getStaffAssignedCourses($teacherId): array
    {
        $setting = $this->getSchoolSetting();

        $rows = $this->classModel
            ->select([
                'class_table.id AS class_id',
                'class_table.class_name',
                'course_table.id AS course_id',
                'course_table.course_name',
                "COUNT(result_table.id) AS num_of_students"
            ])
            ->join('staff_course_table', 'class_table.id = staff_course_table.class')
            ->join('course_table', 'course_table.id = staff_course_table.course')
            ->join(
                'result_table',
                function ($join) {
                    $join->on('result_table.class', '=', 'class_table.id')
                        ->on('result_table.course', '=', 'course_table.id');
                },
                'LEFT'
            )
            ->where('staff_course_table.ref_no', $teacherId)
            ->where('staff_course_table.term', $setting['term'])
            ->where('staff_course_table.year', $setting['year'])
            ->groupBy(['class_id', 'class_name', 'course_id', 'course_name'])
            ->orderBy(['class_name' => 'ASC', 'course_name' => 'ASC'])
            ->get();

        $grouped = [];

        foreach ($rows as $row) {
            $classId = $row['class_id'];

            if (!isset($grouped[$classId])) {
                $grouped[$classId] = [
                    'class_id' => $row['class_id'],
                    'class_name' => $row['class_name'],
                    'courses' => []
                ];
            }

            $grouped[$classId]['courses'][] = [
                'course_id' => $row['course_id'],
                'course_name' => $row['course_name'],
                'num_of_students' => $row['num_of_students'],
            ];
        }

        return array_values($grouped);
    }
    private function getLevelsAndClasses($teacherId): array
    {
        $rows = $this->level
            ->select([
                'level_table.id AS level_id',
                'level_table.level_name',
                'class_table.id AS class_id',
                'class_table.class_name'
            ])
            ->join('class_table', 'level_table.id = class_table.level')
            ->where('class_table.form_teacher', $teacherId)
            ->get();

        $grouped = [];

        foreach ($rows as $row) {
            $levelId = $row['level_id'];

            if (!isset($grouped[$levelId])) {
                $grouped[$levelId] = [
                    'level_id' => $row['level_id'],
                    'level_name' => $row['level_name'],
                    'classes' => []
                ];
            }

            $grouped[$levelId]['classes'][] = [
                'class_id' => $row['class_id'],
                'class_name' => $row['class_name'],
            ];
        }

        return array_values($grouped);
    }

    private function verifyPassword(string $userPassword, string $password): bool
    {
        $passwordHash = password_hash($userPassword, PASSWORD_DEFAULT);
        return password_verify($password, $passwordHash);
    }
}
