<?php

namespace V3\App\Services\Portal\Academics;

use V3\App\Models\Portal\ELearning\Content;

class FeedService
{
    private Content $content;

    public function __construct(\PDO $pdo)
    {
        $this->content = new Content($pdo);
    }

    public function addNews(array $data)
    {
        //TODO
    }
}
