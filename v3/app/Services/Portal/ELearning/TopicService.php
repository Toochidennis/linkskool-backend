<?php

namespace V3\App\Services\Portal\ELearning;

use V3\App\Models\Portal\ELearning\Content;

class TopicService
{
    private Content $content;
    private const CONTENT_TYPE = 4;

    public function __construct(\PDO $pdo)
    {
        $this->content = new Content($pdo);
    }

    public function addTopic(array $data): bool|int
    {
        $payload = [
            'title' => $data['content'],
            'body' => $data['objective'],
            'path_label' => json_encode($data['classes']),
            'parent' => $data['syllabus_id'],
            'author_name' => $data['creator_name'],
            'author_id' => $data['creator_id'],
            'type' => self::CONTENT_TYPE,
            'upload_date' => date('Y-m-d H:i:s')
        ];

        return $this->content->insert($payload);
    }

    public function getTopics(int $syllabusId)
    {
        $results = $this->content
            ->select(columns: ['title AS content', 'body AS objective', 'path_label AS classes'])
            ->where('type', '=', self::CONTENT_TYPE)
            ->where('parent', '=', $syllabusId)
            ->get();

        return array_map(function ($row) {
            $row['classes'] = json_decode($row['classes'], true);
            return $row;
        }, $results);
    }
}
