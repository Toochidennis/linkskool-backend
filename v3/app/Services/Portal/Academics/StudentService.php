<?php

namespace V3\App\Services\Portal\Academics;

use PDO;
use Exception;
use V3\App\Models\Portal\Academics\Student;
use V3\App\Models\Portal\Academics\SchoolSettings;

class StudentService
{
    private Student $student;
    private SchoolSettings $schoolSettings;

    /**
     * StudentRegistrationService constructor.
     *
     * @param Student             $student
     * @param SchoolSettings      $schoolSettings
     */
    public function __construct(PDO $pdo)
    {
        $this->student = new Student($pdo);
        $this->schoolSettings = new SchoolSettings($pdo);
    }


    public function insertStudentRecord($data): bool
    {
        $studentId = $this->student->insert($data);

        if ($studentId) {
            $prefixResult = $this->schoolSettings->select(['student_prefix'])->first();
            $data['password'] = $this->generatePassword($data['surname']);

            if (!empty($prefixResult)) {
                $studentPrefix = $prefixResult['student_prefix'];
                $studentRegNumber = "$studentPrefix$studentId";
            } else {
                $studentRegNumber = "000$studentId";
            }

            $updateStudentStmt = $this->student
                ->where('id', '=', $studentId)
                ->update(data: ['registration_no' => $studentRegNumber]);

            return $updateStudentStmt;
        }

        return false;
    }

    /**
     * Generates a hashed password using the student's surname as a seed.
     *
     * @param  string $surname
     * @return string
     */
    public function generatePassword(string $surname): string
    {
        return substr($surname, 0, 4) . rand(10000, 90000);
    }

    public function getStudentsByClass(int $classId)
    {
        return $this->student
            ->select(columns: [
                'id',
                "CONCAT(surname, ' ', first_name, ' ', middle) AS student_name"
            ])
            ->where(column: "student_class", operator: '=', value: $classId)
            ->get();
    }

    /**
     * Get students record.
     */
    public function getAllStudents(): array
    {
        return $this->student
            ->select(
                columns: [
                    'id',
                    'picture_ref AS picture_url',
                    'surname',
                    'first_name',
                    'middle',
                    'registration_no',
                    'student_class AS class_id',
                    'student_level AS level_id'
                ]
            )
            ->get();
    }
}
