<?php

namespace V3\App\Services\Portal\ELearning;

use V3\App\Common\Enums\ContentType;
use V3\App\Models\Portal\ELearning\Content;

class ContentCommentService
{
    private Content $content;

    public function __construct(\PDO $pdo)
    {
        $this->content = new Content($pdo);
    }

    public function addComment(array $data): bool
    {
        $payload  = [
            'description' => $data['content_title'],
            'category' => $data['content_id'],
            'author_id' => $data['user_id'],
            'author_name' => $data['user_name'],
            'body' => $data['comment'],
            'type' => ContentType::COMMENT->value,
            'level' => $data['level_id'],
            'course' => $data['course_id'],
            'course_name' => $data['course_name'],
            'term' => $data['term'],
            'upload_date' => date('Y-m-d H:i:s')
        ];

        return $this->content->insert($payload);
    }

    public function updateComment(array $data): bool
    {
        $payload  = [
            'description' => $data['content_title'],
            'category' => $data['content_id'],
            'author_id' => $data['author_id'],
            'author_name' => $data['author_name'],
            'body' => $data['comment'],
            'level' => $data['level_id'],
            'course' => $data['course_id'],
            'course_name' => $data['course_name'],
            'term' => $data['term'],
        ];

        return $this->content
            ->where('id', $data['id'])
            ->update($payload);
    }

    public function getCommentsByContentId(int $contentId, int $page = 1, int $limit = 20): array
    {
        return $this->content
            ->select([
                'id',
                'category AS content_id',
                'description AS content_title',
                'level AS level_id',
                'course AS course_id',
                'course_name',
                'term',
                'author_id',
                'author_name',
                'body AS comment',
                'upload_date'
            ])
            ->where('category', $contentId)
            ->where('type', ContentType::COMMENT->value)
            ->orderBy('upload_date', 'DESC')
            ->paginate($page, $limit);
    }

    public function deleteComment(int $id): bool
    {
        return $this->content
            ->where('id', $id)
            ->delete();
    }
}
