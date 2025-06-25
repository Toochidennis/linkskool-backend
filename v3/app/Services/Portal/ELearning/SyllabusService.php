<?php

namespace V3\App\Services\Portal\ELearning;

use Exception;
use PDO;
use V3\App\Models\Portal\ELearning\SyllabusModel;

class SyllabusService
{
    private SyllabusModel $syllabusModel;
    private const CONTENT_TYPE = 100;

    public function __construct(PDO $pdo)
    {
        $this->syllabusModel = new SyllabusModel($pdo);
    }

    public function create(array $data)
    {
        $payload = [
            'title' => $data['title'],
            'description' => $data['description'],
            'type' => self::CONTENT_TYPE,
            'course_id' => $data['course_id'],
            'course_name' => $data['course_name'],
            'level' => $data['level_id'],
            'path_label' => json_encode($data['classes']),
            'author_id' => $data['creator_id'],
            'author_name' => $data['creator_name'],
            'term' => $data['term'],
            'upload_date' => date('Y-m-d H:i:s'),
        ];

        $newId = $this->syllabusModel->insert($payload);
        if (!$newId) {
            throw new Exception("Failed to create syllabus.");
        }

        return $newId;
    }

    public function updateSyllabus($data): bool
    {
        $payload = [
            'title' => $data['title'],
            'description' => $data['description'],
            'path_label' => json_encode($data['classes'])
        ];

        return $this->syllabusModel
            ->where('id', '=', $data['id'])
            ->update($payload);
    }

    /**
     * Retrieves syllabus content filtered by term, level, and type.
     *
     * @param array $filters Must include 'term' and 'level_id'.
     * @return array List of syllabus items with 'classes' decoded from path_label.
     */
    public function getSyllabus(array $filters): array
    {
        $results = $this->syllabusModel
            ->select(columns: [
                'id',
                'title',
                'description',
                'path_label AS classes, author_name, term, upload_date'
            ])
            ->where('term', '=', $filters['term'])
            ->where('level', '=', $filters['level_id'])
            ->where('type', '=', self::CONTENT_TYPE)
            ->get();

        return array_map(function ($row) {
            $row['classes'] = json_decode($row['classes'], true);
            return $row;
        }, $results);
    }

    public function deleteSyllabus(int $id): bool
    {
        return $this->syllabusModel
            ->where('id', '=', $id)
            ->delete();
    }
}
