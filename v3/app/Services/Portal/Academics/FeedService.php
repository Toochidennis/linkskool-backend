<?php

namespace V3\App\Services\Portal\Academics;

use V3\App\Common\Enums\ContentType;
use V3\App\Common\Utilities\FileHandler;
use V3\App\Models\Portal\ELearning\Content;

class FeedService
{
    private Content $content;
    private FileHandler $fileHandler;

    private array $types = [
        'news' => ContentType::NEWS->value,
        'reply' => ContentType::REPLY->value,
        'question' => ContentType::QUESTION->value,
    ];

    public function __construct(\PDO $pdo)
    {
        $this->content = new Content($pdo);
        $this->fileHandler = new FileHandler();
    }

    public function addContent(array $data)
    {
        $files = json_encode($this->fileHandler->handleFiles($data['files'] ?? []));

        $payload = [
            "title" => $data['title'],
            "body" => $data['content'],
            "parent" => $data['parent_id'] ?? 0,
            "author_name" => $data['author_name'],
            "author_id" => $data['author_id'],
            "url" => $files,
            "upload_date" => date('Y-m-d H:i:s'),
            "type" => $this->types[$data['type']],
            "term" => $data['term']
        ];

        return $this->content->insert($payload);
    }

    public function updateContent(array $data)
    {
        $files = json_encode($this->fileHandler->handleFiles($data['files'] ?? [], true));
        $payload = [
            "title" => $data['title'],
            "body" => $data['content'],
            "parent" => empty($data['parent_id']) ? 0 : $data['parent_id'],
            "author_name" => $data['author_name'],
            "author_id" => $data['author_id'],
            "url" => $files,
            "type" => $this->types[$data['type']],
            "term" => $data['term']
        ];

        return $this->content
            ->where('id', '=', $data['news_id'])
            ->update($payload);
    }

    public function getContents(array $filters): array
    {
        $contents =  $this->content
            ->select([
                'id',
                'title',
                'body AS content',
                'parent AS parent_id',
                'author_name',
                'author_id',
                'url as files',
                'type',
                'upload_date as created_at',
            ])
            ->in('type', [
                ContentType::NEWS->value,
                ContentType::QUESTION->value,
                ContentType::REPLY->value
            ])
            ->where('term', '=', $filters['term'])
            ->paginate($filters['page'] ?? 1, $filters['limit'] ?? 15);

        $grouped = [
            'news'      => [],
            'questions' => []
        ];

        // Index contents by ID for quick lookup
        $byId = [];
        foreach ($contents['data'] as $content) {
            $content['files'] = !empty($content['files'])
                ? json_decode($content['files'], true)
                : [];

            $content['replies'] = [];
            $byId[$content['id']] = $content;
        }

        // Build parent-child relationships
        foreach ($byId as $id => &$content) {
            if (!empty($content['parent_id']) && isset($byId[$content['parent_id']])) {
                $byId[$content['parent_id']]['replies'][] = &$content;
            }
        }
        unset($content); // break reference

        // Group into news and questions (ignore replies at top level)
        foreach ($byId as $id => $content) {
            if (!empty($content['parent_id'])) {
                continue; // already attached as a reply
            }

            if ($content['type'] === ContentType::NEWS->value) {
                $content['type'] =  ContentType::NEWS->label();
                $grouped['news'][] = $content;
            } elseif ($content['type'] === ContentType::QUESTION->value) {
                $content['type'] =  ContentType::QUESTION->label();
                $grouped['questions'][] = $content;
            }
        }

        return [
            'success' => true,
            'data' => $grouped,
            'meta' => $contents['meta'] ?? []
        ];
    }

    public function deleteContent(int $contentId): bool
    {
        return $this->content
            ->where('id', '=', $contentId)
            ->delete();
    }
}
