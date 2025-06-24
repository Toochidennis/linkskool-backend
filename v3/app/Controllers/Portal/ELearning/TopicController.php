<?php

namespace V3\App\Controllers\Portal\ELearning;

use Exception;
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

    public function store()
    {
        $data =  $this->validateData(
            data: $this->post,
            requiredFields: [
                'topic',
                'objective',
                'classes',
                'syllabus_id',
                'creator_name',
                'creator_id'
            ]
        );

        try{

        }catch(Exception $e){
            $this->respondError($e->getMessage());
        }
    }
}
