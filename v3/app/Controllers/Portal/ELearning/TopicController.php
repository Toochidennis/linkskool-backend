<?php 

namespace V3\App\Controllers\Portal\ELearning;

use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\ELearning\TopicService;

class TopicController extends BaseController
{
    private TopicService $topicService;

    public function __construct()
    {
        parent::__construct();
        $this->topicService = new TopicService($this->pdo);
    }

    public function store
}