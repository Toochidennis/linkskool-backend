<?php

namespace V3\App\Services\Portal\Academics;

use PDO;
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


    public function insertStudentRecord(array $data): bool
    {
        $payload = [
            'picture_ref' => $data['photo'] ?? null,
            'surname' => $data['last_name'],
            'first_name' => $data['first_name'],
            'middle' => $data['middle_name'] ?? '',
            'sex' => $data['gender'],
            'birthdate' => $data['birth_date'] ?? null,
            'address' => $data['home_address'] ?? '',
            'city' => $data['city_id'] ?? null,
            'state' => $data['state_id'] ?? null,
            'country' => $data['country_id'] ?? null,
            'email' => $data['email_address'] ?? '',
            'religion' => $data['religion'] ?? '',
            'guardian_name' => $data['guardian_name'] ?? '',
            'guardian_address' => $data['guardian_address'] ?? '',
            'guardian_email' => $data['guardian_email'] ?? '',
            'guardian_phone_no' => $data['guardian_phone'] ?? '',
            'local_government_origin' => $data['lga_origin'] ?? '',
            'state_origin' => $data['state_origin'] ?? '',
            'nationality' => $data['nationality'] ?? '',
            'health_status' => $data['health_info'] ?? '',
            'date_admitted' => date('Y-m-d H:i:s'),
            'status' => $data['student_status'] ?? '',
            'past_record' => $data['past_record'] ?? '',
            'result' => $data['academic_result'] ?? '',
            'student_class' => $data['class_id'],
            'student_level' => $data['level_id'],
            'password' => $this->generatePassword($data['surname'])
        ];

        $studentId = $this->student->insert($payload);

        if ($studentId) {
            $prefixResult = $this->schoolSettings
                ->select(['student_prefix'])
                ->first();

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
