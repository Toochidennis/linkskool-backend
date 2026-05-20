<?php

namespace V3\App\Services\Explore\Classroom;

use V3\App\Models\Explore\Classroom\ClassroomStudent;
use V3\App\Models\Explore\Level;

class ClassroomStudentService
{
    protected ClassroomStudent $model;
    private Level $levelModel;

    public function __construct(\PDO $pdo)
    {
        $this->model      = new ClassroomStudent($pdo);
        $this->levelModel = new Level($pdo);
    }

    public function listStudents(
        int $institutionId,
        array $filters = [],
        int $page = 1,
        int $limit = 20
    ): array {
        $query = $this->model
            ->select([
                'classroom_students.*',
                'level.name as level_name',
            ])
            ->join('level', 'classroom_students.level_id = level.id', 'LEFT')
            ->where('classroom_students.institution_id', $institutionId);

        if (!empty($filters['level_id'])) {
            $query->where('classroom_students.level_id', $filters['level_id']);
        }

        if (!empty($filters['name'])) {
            $term = '%' . $filters['name'] . '%';
            $query->whereRaw(
                '(`first_name` LIKE ? OR `last_name` LIKE ? OR `middle_name` LIKE ?)',
                [$term, $term, $term]
            );
        }

        if (!empty($filters['reg_number'])) {
            $query->where('reg_number', 'LIKE', '%' . $filters['reg_number'] . '%');
        }

        return $query->paginate($page, $limit);
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
