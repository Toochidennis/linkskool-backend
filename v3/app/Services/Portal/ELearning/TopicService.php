<?php

namespace V3\App\Services\Portal\ELearning;

use V3\App\Models\Portal\ELearning\Topic;

class TopicService
{
    private Topic $topic;
    private const CONTENT_TYPE = 4;

    public function __construct(\PDO $pdo)
    {
        $this->topic = new Topic($pdo);
    }

    public function addTopic(array $data): bool|int
    {
        $payload = [
            'title' => $data['topic'],
            'body' => $data['objective'],
            'path_label' => json_encode($data['classes']),
            'parent' => $data['syllabus_id'],
            'author_name' => $data['creator_name'],
            'author_id' => $data['creator_id'],
            'type' => self::CONTENT_TYPE
        ];

        return $this->topic->insert($payload);
    }

    public function getTopics(int $syllabusId)
    {
        $results = $this->topic
            ->select(columns:['title AS topic', 'body AS objective', 'path_label AS classes'])
            ->where('type', '=', self::CONTENT_TYPE)
            ->where('parent', '=', $syllabusId)
            ->get();

        return array_map(function ($row) {
            $row['classes'] = json_decode($row['classes'], true);
            return $row;
        }, $results);
    }
}
