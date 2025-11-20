<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\ExamType;
use V3\App\Models\Portal\Academics\Course;

class ExamTypeService
{
    private ExamType $examType;
    private Course $course;

    public function __construct(\PDO $pdo)
    {
        $this->examType = new ExamType($pdo);
        $this->course = new Course($pdo);
    }

    public function createExamType(array $data): int
    {
        $payload = [
            'title' => $data['name'],
            'shortname' => $data['shortname'],
            'course_ids' => json_encode($data['course_ids']),
            'active' => $data['active']
        ];

        return $this->examType->insert($payload);
    }

    public function updateExamType(array $data): bool
    {
        $payload = [
            'title' => $data['name'],
            'shortname' => $data['shortname'],
            'course_ids' => json_encode($data['course_ids']),
            'active' => $data['active']
        ];
        return $this->examType
            ->where('id', '=', $data['id'])
            ->update($payload);
    }

    public function getAllExamTypes(int $active): array
    {
        $examTypes = $this->examType
            ->select(['id, title, shortname, course_ids, active']);

        if ($active === 1) {
            $examTypes = $examTypes->where('active', '=', 1);
        }
        $examTypes = $examTypes->get();

        foreach ($examTypes as &$examType) {
            $courseIds = json_decode($examType['course_ids'], true) ?? [];
            unset($examType['course_ids']);

            $courses = $this->course
                ->select(['id AS course_id', 'course_name'])
                ->in('id', $courseIds)
                ->get();

            $examType['courses'] = $courses;
        }
        return $examTypes;
    }

    public function deleteExamType(int $id): bool
    {
        return $this->examType
            ->where('id', '=', $id)
            ->delete();
    }
}
