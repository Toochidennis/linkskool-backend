<?php

namespace V3\App\Services\Portal\ELearning;

use V3\App\Common\Enums\ContentType;
use V3\App\Common\Utilities\FileHandler;
use V3\App\Models\Portal\ELearning\Content;

class AssignmentService
{
    private Content $content;
    private FileHandler $handler;

    public function __construct(\PDO $pdo)
    {
        $this->content = new Content($pdo);
        $this->handler = new FileHandler();
    }

    public function addAssignment(array $data): int|bool
    {
        $payload = $this->buildAddPayload($data);
        $result = $this->handler->handleFiles(
            files: $data['files'],
            groupPath: $this->buildAssignmentGroupPath($data)
        );
        $payload['url'] = json_encode($result);
        return $this->content->insert($payload);
    }

    public function updateAssignment(array $data): bool|int
    {
        $payload = $this->buildUpdatePayload($data);
        $result = $this->handler->handleFiles(
            files: $data['files'],
            isUpdate: true,
            groupPath: $this->buildAssignmentGroupPath($data)
        );
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
            'upload_date' => date('Y-m-d H:i:s'),
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'course_id' => $data['course_id'],
            'course_name' => $data['course_name'],
            'level' => $data['level_id'],
            'term' => $data['term'],
            'type' => ContentType::ASSIGNMENT->value,
            'path_label' => json_encode($data['classes']),
            'body' => $data['grade'],
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
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'body' => $data['grade'],
        ];
    }

    private function buildAssignmentGroupPath(array $data): string
    {
        $courseId = (int)($data['course_id'] ?? 0);
        $syllabusId = (int)($data['syllabus_id'] ?? 0);
        $topicId = (int)($data['topic_id'] ?? 0);
        $contentId = (int)($data['id'] ?? 0);
        $title = $this->toSlug((string)($data['title'] ?? 'assignment'));

        $base = "portal/elearning/assignments/courses/{$courseId}/syllabi/{$syllabusId}/topics/{$topicId}";
        if ($contentId > 0) {
            return "{$base}/content/{$contentId}/{$title}";
        }

        return "{$base}/{$title}";
    }

    private function toSlug(string $text): string
    {
        return strtolower(trim((string)preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
    }
}
