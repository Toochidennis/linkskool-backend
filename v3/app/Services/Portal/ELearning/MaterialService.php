<?php

namespace V3\App\Services\Portal\ELearning;

use V3\App\Common\Enums\ContentType;
use V3\App\Common\Utilities\FileHandler;
use V3\App\Models\Portal\ELearning\Content;

class MaterialService
{
    private Content $content;
    private FileHandler $handler;

    public function __construct(\PDO $pdo)
    {
        $this->content = new Content($pdo);
        $this->handler = new FileHandler();
    }

    public function addMaterial(array $data): int|bool
    {
        $payload = $this->buildAddPayload(data: $data);
        $result = $this->handler->handleFiles(files: $data['files']);
        $payload['url'] = json_encode($result);
        return $this->content->insert(data: $payload);
    }

    public function updateMaterial(array $data): bool|int
    {
        $payload = $this->buildUpdatePayload($data);
        $result = $this->handler->handleFiles(files: $data['files'], isUpdate: true);
        $payload['url'] = json_encode($result);

        return $this->content
            ->where('id', '=', $data['id'])
            ->update($payload);
    }

    /**
     * Build payload base for insert.
     */
    private function buildAddPayload(array $data): array
    {
        return [
            'title' => $data['title'],
            'description' => $data['description'],
            'category' => $data['topic'] ?? '',
            'parent' => $data['topic_id'] ?? 0,
            'outline' => $data['syllabus_id'],
            'author_name' => $data['creator_name'],
            'author_id' => $data['creator_id'],
            'course_id' => $data['course_id'],
            'course_name' => $data['course_name'],
            'level' => $data['level_id'],
            'term' => $data['term'],
            'upload_date' => date('Y-m-d H:i:s'),
            'type' => ContentType::MATERIAL->value,
            'path_label' => json_encode($data['classes']),
        ];
    }

    /**
     * Build payload base for update.
     */
    private function buildUpdatePayload(array $data): array
    {
        return [
            'title' => $data['title'],
            'description' => $data['description'],
            'category' => $data['topic'] ?? '',
            'parent' => $data['topic_id'] ?? 0,
            'path_label' => json_encode($data['classes']),
        ];
    }
}
