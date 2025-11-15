<?php

namespace V3\App\Services\Portal\Academics;

use PDO;
use V3\App\Common\Utilities\FileHandler;
use V3\App\Models\Portal\Academics\Level;
use V3\App\Models\Portal\Academics\Student;
use V3\App\Models\Portal\Academics\SchoolSettings;

class StudentService
{
    private Student $student;
    private SchoolSettings $schoolSettings;
    private FileHandler $fileHandler;
    private Level $level;

    /**
     * StudentRegistrationService constructor.
     *
     */
    public function __construct(PDO $pdo)
    {
        $this->student = new Student($pdo);
        $this->schoolSettings = new SchoolSettings($pdo);
        $this->fileHandler = new FileHandler();
        $this->level = new Level($pdo);
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
            'date_admitted' => date('Y'),
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

    private function getLevels(): array
    {
        return $this->level
            ->select(columns: [
                'level_table.id AS level_id',
                'level_table.level_name',
                'class_table.class_name',
                'class_table.id AS class_id'
            ])
            ->join(
                table: 'class_table',
                condition: 'level_table.id = class_table.level',
                type: 'INNER'
            )
            ->orderBy(['level_table.level_name' => 'ASC', 'class_table.class_name' => 'ASC'])
            ->get();
    }

    private function getStudents(): array
    {
        return $this->student
            ->where('status', '=', 1)
            ->where('student_level', '>', 0)
            ->get();
    }

    private function maleStudentsCount(array $students): int
    {
        return \count(array_filter(
            $students,
            fn($s) => \in_array(strtolower(trim($s['sex'] ?? $s['gender'])), ['male', 'm'], true)
        ));
    }

    private function femaleStudentsCount(array $students): int
    {
        return \count(array_filter(
            $students,
            fn($s) => \in_array(strtolower(trim($s['sex'] ?? $s['gender'])), ['female', 'f'], true)
        ));
    }

    /**
     * Create fast indexes for students.
     */
    private function indexStudents(array $students): array
    {
        $byLevel = [];
        $byClass = [];

        foreach ($students as $s) {
            $levelId = $s['student_level'];
            $classId = $s['student_class'];

            $byLevel[$levelId][] = $s;
            $byClass[$classId][] = $s;
        }

        return [
            'byLevel' => $byLevel,
            'byClass' => $byClass
        ];
    }

    public function fetchStudentsMetrics(): array
    {
        $students = $this->getStudents();
        $levels = $this->getLevels();

        $indexed = $this->indexStudents($students);

        $levelsMetrics = [];

        foreach ($levels as $level) {
            $levelId = $level['level_id'];
            $classId = $level['class_id'];

            $levelStudents  = $indexed['byLevel'][$levelId] ?? [];
            $classStudents  = array_filter(
                $levelStudents,
                fn($s) => $s['student_class'] == $classId
            );

            if (!isset($levelsMetrics[$levelId])) {
                $levelsMetrics[$levelId] = [
                    'level_id' => $levelId,
                    'level_name' => $level['level_name'],
                    'total_students'  => \count($levelStudents),
                    'classes' => []
                ];
            }

            $levelsMetrics[$levelId]['classes'][] = [
                'class_id' => $classId,
                'class_name' => $level['class_name'],
                'total_students'  => \count($classStudents)
            ];
        }

        return [
            'total_students' => \count($students),
            'male_students'  => $this->maleStudentsCount($students),
            'female_students' => $this->femaleStudentsCount($students),
            'levels' => array_values($levelsMetrics)
        ];
    }

    public function fetchStudentsByLevel($filters): array
    {
        $result = $this->student
            ->select([
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
            ])
            ->where('student_level', '=', $filters['level_id'])
            ->where('status', '=', 1)
            ->orderBy('surname', 'ASC')
            ->paginate(
                page: $filters['page'] ?? 1,
                limit: $filters['limit'] ?? 15
            );

        $students = $result['data'] ?? [];

        return [
            'male_students' => $this->maleStudentsCount($students),
            'female_students' => $this->femaleStudentsCount($students),
            'students' => $result
        ];
    }

    public function deleteStudent(int $id): bool
    {
        return $this->student
            ->where('id', '=', $id)
            ->delete();
    }
}
