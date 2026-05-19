<?php

namespace V3\App\Services\Explore\Classroom;

use V3\App\Models\Explore\Level;
use V3\App\Models\Portal\Academics\Course as SubjectModel;

class ClassroomSettingsService
{
    private Level $levelModel;
    private SubjectModel $subjectModel;

    public function __construct(\PDO $pdo)
    {
        $this->levelModel   = new Level($pdo);
        $this->subjectModel = new SubjectModel($pdo);
    }

    public function getLevels(): array
    {
        $rows = $this->levelModel->select(['id', 'name'])
        ->orderBy('rank', 'ASC')
        ->get();

        return array_map(fn($row) => [
            'id'   => $row['id'],
            'name' => ucwords(strtolower($row['name'])),
        ], $rows);
    }

    public function getSubjects(): array
    {
        $rows = $this->subjectModel
            ->select(['id', 'course_name'])
            ->orderBy('course_name', 'ASC')
            ->get();

        return array_map(fn($row) => [
            'id'   => $row['id'],
            'name' => ucwords(strtolower($row['course_name'])),
        ], $rows);
    }
}
