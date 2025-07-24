<?php

namespace V3\App\Services\Portal\ELearning;

use V3\App\Common\Enums\ContentType;
use V3\App\Models\Portal\ELearning\Content;

class TopicService
{
    private Content $content;

    public function __construct(\PDO $pdo)
    {
        $this->content = new Content($pdo);
    }

    public function addTopic(array $data): bool|int
    {
        $payload = [
            'title' => $data['topic'],
            'body' => $data['objective'],
            'path_label' => json_encode($data['classes']),
            'outline' => $data['syllabus_id'],
            'author_name' => $data['creator_name'],
            'author_id' => $data['creator_id'],
            'type' => ContentType::TOPIC->value,
            'upload_date' => date('Y-m-d H:i:s')
        ];

        return $this->content->insert($payload);
    }

    public function getTopics(int $syllabusId): array
    {
        $results = $this->content
            ->select(columns: ['id', 'title AS content', 'body AS objective', 'path_label AS classes'])
            ->where('type', '=', ContentType::TOPIC->value)
            ->where('outline', '=', $syllabusId)
            ->get();

        return array_map(function ($row) {
            $row['classes'] = json_decode($row['classes'], true);
            return $row;
        }, $results);
    }

    public function updateTopic(array $data): bool
    {
        $payload = [
            'title' => $data['topic'],
            'body' => $data['objective'],
            'path_label' => json_encode($data['classes']),
        ];

        $topicId = $this->content
            ->where('id', '=', $data['id'])
            ->update($payload);

        $contentId = $this->content
            ->where('parent', '=', $data['id'])
            ->update([
                'category' => $data['topic']
            ]);

        return $topicId && $contentId;
    }
}
