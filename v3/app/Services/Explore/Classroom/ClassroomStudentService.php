<?php

namespace V3\App\Services\Explore\Classroom;

use V3\App\Models\Explore\Classroom\ClassroomStudent;

class ClassroomStudentService
{
    protected ClassroomStudent $model;

    public function __construct(\PDO $pdo)
    {
        $this->model = new ClassroomStudent($pdo);
    }

    public function createStudents(array $data): int
    {
        $rows = [];
        foreach ($data['students'] as $student) {
            $rows[] = [
                'institution_id' => $data['institution_id'],
                'level_id'       => $data['level_id'],
                'first_name'     => $student['first_name'],
                'last_name'      => $student['last_name'],
                'middle_name'    => $student['middle_name'] ?? null,
                'phone'          => $student['phone'] ?? null,
                'reg_number'     => $student['reg_number'],
            ];
        }

        return $this->model->insertMany($rows);
    }
}
