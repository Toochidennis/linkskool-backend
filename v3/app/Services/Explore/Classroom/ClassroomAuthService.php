<?php

namespace V3\App\Services\Explore\Classroom;

use V3\App\Models\Explore\Classroom\ClassroomInstitution;
use V3\App\Models\Explore\Classroom\ClassroomStudent;

class ClassroomAuthService
{
    private ClassroomInstitution $institutionModel;
    private ClassroomStudent $studentModel;

    public function __construct(\PDO $pdo)
    {
        $this->institutionModel = new ClassroomInstitution($pdo);
        $this->studentModel = new ClassroomStudent($pdo);
    }

    public function authenticateStudent(string $regNumber, string $joinCode): array
    {
        $institution = $this->institutionModel->where('join_code', $joinCode)->first();

        if (empty($institution)) {
            return ['status' => 'error', 'message' => 'Invalid join code.'];
        }

        $student = $this->studentModel
            ->where('reg_number', $regNumber)
            ->where('institution_id', $institution['id'])
            ->first();

        if (empty($student)) {
            return ['status' => 'error', 'message' => 'Student not found.'];
        }

        return [
            'status' => 'success',
            'message' => 'Authentication successful.',
            'student' => $student,
            'institution' => $institution,
        ];
    }

    public function authenticateStaff(string $password, string $joinCode): array
    {
        $institution = $this->institutionModel->where('join_code', $joinCode)->first();

        if (empty($institution)) {
            return ['status' => 'error', 'message' => 'Invalid join code.'];
        }

        if (!password_verify($password, $institution['password_hash'])) {
            return ['status' => 'error', 'message' => 'Invalid credentials.'];
        }

        return [
            'status' => 'success',
            'message' => 'Authentication successful.',
            'institution' => $institution,
        ];
    }
}
