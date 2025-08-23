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
            'course_id' => $data['course_id'],
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
            'author_id' => $data['user_id'],
            'author_name' => $data['user_name'],
            'body' => $data['comment'],
            'level' => $data['level_id'],
            'course_id' => $data['course_id'],
            'course_name' => $data['course_name'],
            'term' => $data['term'],
        ];

        return $this->content
            ->where('id', $data['id'])
            ->update($payload);
    }

    private function getComments(array $filters, bool $paginate = false): array
    {
        $query =  $this->content
            ->select([
                'id',
                'category AS content_id',
                'description AS content_title',
                'level AS level_id',
                'course_id',
                'course_name',
                'term',
                'author_id',
                'author_name',
                'body AS comment',
                'upload_date'
            ])
            ->where('category', $filters['content_id'])
            ->where('type', ContentType::COMMENT->value)
            ->orderBy('upload_date', 'DESC');

        if ($paginate) {
            return $query->paginate($filters['page'], $filters['limit']);
        }

        return $query->get();
    }

    public function getCommentsByContentId(int $contentId, int $page = 1, int $limit = 20): array
    {
        return $this->getComments(
            filters: [
                'content_id' => $contentId,
                'page' => $page,
                'limit' => $limit
            ],
            paginate: true
        );
    }

    public function getContentsComments(int $syllabusId)
    {
        $contents = $this->content
            ->select(['id', 'title', 'type'])
            ->where('outline', '=', $syllabusId)
            ->where('type', '<>', ContentType::TOPIC->value)
            ->orderBy('rank')
            ->get();

        if (empty($contents)) {
            return [];
        }

        $streams = [];
        foreach ($contents as $content) {
            $streams[] = [
                'id' => $content['id'],
                'title' => $content['title'],
                'type' => ContentType::tryFrom($content['type'])?->label(),
                'comments' => $this->getComments(['content_id' => $content['id']])
            ];
        }

        return $streams;
    }

    public function deleteComment(int $id): bool
    {
        return $this->content
            ->where('id', $id)
            ->delete();
    }
}
