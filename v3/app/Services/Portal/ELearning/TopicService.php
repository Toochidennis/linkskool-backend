<?php

namespace V3\App\Services\Portal\ELearning;

use V3\App\Models\Portal\ELearning\Topic;

class TopicService
{
    private Topic $topic;

    public function __construct(\PDO $pdo)
    {
        $this->topic = new Topic($pdo);
    }
    
}