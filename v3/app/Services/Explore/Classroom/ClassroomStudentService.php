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

    public function listStudents(array $data): array
    {
        $institutionId = (int) $data['institution_id'];
        $page = max(1, (int) ($data['page'] ?? 1));
        $limit = max(1, min(100, (int) ($data['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $where = ['s.institution_id = :institution_id'];
        $params = ['institution_id' => $institutionId];

        if (!empty($data['level_id'])) {
            $where[] = 's.level_id = :level_id';
            $params['level_id'] = (int) $data['level_id'];
        }

        if (!empty($data['name'])) {
            $term = '%' . $data['name'] . '%';
            $where[] = '(s.first_name LIKE :name OR s.last_name LIKE :name OR s.middle_name LIKE :name)';
            $params['name'] = $term;
        }

        if (!empty($data['reg_number'])) {
            $where[] = 's.reg_number LIKE :reg_number';
            $params['reg_number'] = '%' . $data['reg_number'] . '%';
        }

        $whereSql = implode(' AND ', $where);

        $countSql = "
            SELECT COUNT(*) AS total
            FROM classroom_students s
            WHERE {$whereSql}
        ";

        $countRows = $this->model->rawQuery($countSql, $params);
        $total = (int) ($countRows[0]['total'] ?? 0);

        $sql = "
            SELECT
                s.*,
                l.name AS level_name
            FROM classroom_students s
            LEFT JOIN level l
                ON s.level_id = l.id
            WHERE {$whereSql}
            ORDER BY s.id DESC
            LIMIT :limit OFFSET :offset
        ";

        $rows = $this->model->rawQuery($sql, [
            ...$params,
            'limit' => $limit,
            'offset' => $offset,
        ]);

        return [
            'data' => $rows,
            'meta' => [
                'total' => $total,
                'limit' => $limit,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $limit),
                'has_next' => $page * $limit < $total,
                'has_prev' => $page > 1,
            ],
        ];
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
