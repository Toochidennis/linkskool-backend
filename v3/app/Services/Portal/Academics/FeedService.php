<?php

namespace V3\App\Services\Portal\Academics;

use V3\App\Common\Enums\ContentType;
use V3\App\Common\Utilities\FileHandler;
use V3\App\Models\Portal\ELearning\Content;

class FeedService
{
    private Content $content;
    private FileHandler $fileHandler;

    public function __construct(\PDO $pdo)
    {
        $this->content = new Content($pdo);
        $this->fileHandler = new FileHandler();
    }

    public function addNews(array $data)
    {
        $files = $this->fileHandler->handleFiles($data['files'] ?? []);
        $payload = [
            "title" => $data['title'],
            "body" => $data['content'],
            "author_name" => $data['author_name'],
            "author_id" => $data['author_id'],
            "url" => $files,
            "upload_date" => $data['published_at'],
            "type" => ContentType::NEWS->value,
            "publish" => 1,
        ];

        return $this->content->insert($payload);
    }
}
