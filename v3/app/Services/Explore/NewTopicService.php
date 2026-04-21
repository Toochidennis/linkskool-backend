<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\NewTopic;

class NewTopicService
{
    private NewTopic $newTopicModel;

    public function __construct(\PDO $pdo)
    {
        $this->newTopicModel = new NewTopic($pdo);
    }

    public function getTopics()
    {
        return $this->newTopicModel->get();
    }

}
