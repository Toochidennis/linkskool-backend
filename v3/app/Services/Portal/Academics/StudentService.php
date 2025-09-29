<?php

namespace V3\App\Services\Portal\Academics;

use PDO;
use V3\App\Common\Utilities\FileHandler;
use V3\App\Models\Portal\Academics\Student;
use V3\App\Models\Portal\Academics\SchoolSettings;

class StudentService
{
    private Student $student;
    private SchoolSettings $schoolSettings;
    private FileHandler $fileHandler;

    /**
     * StudentRegistrationService constructor.
     *
     */
    public function __construct(PDO $pdo)
    {
        $this->student = new Student($pdo);
        $this->schoolSettings = new SchoolSettings($pdo);
        $this->fileHandler = new FileHandler();
    }


    public function insertStudentRecord(array $data): bool
    {
        if (!empty($data['photo']) && is_array($data['photo'])) {
            $data['photo']['type'] = 'image';
            $file = $this->fileHandler->handleFiles($data['photo']);
            $data['photo'] = $file[0]['old_file_name'];
        }
        $payload = [
            'picture_ref' => $data['photo'] ?? '',
            'surname' => $data['surname'],
            'first_name' => $data['first_name'],
            'middle' => $data['middle_name'] ?? '',
            'sex' => $data['gender'],
            'birthdate' => $data['birth_date'] ?? '',
            'address' => $data['address'] ?? '',
            'city' => $data['city'] ?? '',
            'state' => $data['state'] ?? '',
            'country' => $data['country'] ?? '',
            'email' => $data['email'] ?? '',
            'religion' => $data['religion'] ?? '',
            'guardian_name' => $data['guardian_name'] ?? '',
            'guardian_address' => $data['guardian_address'] ?? '',
            'guardian_email' => $data['guardian_email'] ?? '',
            'guardian_phone_no' => $data['guardian_phone'] ?? '',
            'local_government_origin' => $data['lga_origin'] ?? '',
            'state_origin' => $data['state_origin'] ?? '',
            'nationality' => $data['nationality'] ?? '',
            'health_status' => $data['health_status'] ?? '',
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

    public function updateStudentRecord(array $data): bool
    {
        if (!empty($data['photo']) && is_array($data['photo'])) {
            $data['photo']['type'] = 'image';
            $file = $this->fileHandler->handleFiles($data['photo']);
            $data['photo'] = $file[0]['old_file_name'];
        }

        $payload = [
            'picture_ref' => $data['photo'] ?? '',
            'surname' => $data['surname'],
            'first_name' => $data['first_name'],
            'middle' => $data['middle'] ?? '',
            'sex' => $data['gender'],
            'birthdate' => $data['birth_date'] ?? '',
            'address' => $data['address'] ?? '',
            'city' => $data['city'] ?? '',
            'state' => $data['state'] ?? '',
            'country' => $data['country'] ?? '',
            'email' => $data['email'] ?? '',
            'religion' => $data['religion'] ?? '',
            'guardian_name' => $data['guardian_name'] ?? '',
            'guardian_address' => $data['guardian_address'] ?? '',
            'guardian_email' => $data['guardian_email'] ?? '',
            'guardian_phone_no' => $data['guardian_phone_no'] ?? '',
            'local_government_origin' => $data['lga_origin'] ?? '',
            'state_origin' => $data['state_origin'] ?? '',
            'nationality' => $data['nationality'] ?? '',
            'health_status' => $data['health_status'] ?? '',
            'status' => $data['status'] ?? '',
            'past_record' => $data['past_record'] ?? '',
            'result' => $data['result'] ?? '',
            'student_class' => $data['class_id'],
            'student_level' => $data['level_id']
        ];

        return $this->student
            ->where('id', '=', $data['id'])
            ->update(data: $payload);
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
    public function fetchStudents(): array
    {
        return $this->student
            ->select(
                columns: [
                    'id',
                    'picture_ref AS photo',
                    'surname',
                    'first_name',
                    'middle',
                    'sex AS gender',
                    'birthdate AS birth_date',
                    'address',
                    'city',
                    'state',
                    'country',
                    'email',
                    'religion',
                    'guardian_name',
                    'guardian_address',
                    'guardian_email',
                    'guardian_phone_no',
                    'local_government_origin AS lga_origin',
                    'state_origin',
                    'nationality',
                    'health_status',
                    'date_admitted',
                    'status AS student_status',
                    'past_record',
                    'result AS academic_result',
                    'student_class AS class_id',
                    'student_level AS level_id',
                    'registration_no'
                ]
            )
            ->orderBy('surname', 'ASC')
            ->get();
    }

    public function deleteStudent(int $id): bool
    {
        return $this->student
            ->where('id', '=', $id)
            ->delete();
    }
}
