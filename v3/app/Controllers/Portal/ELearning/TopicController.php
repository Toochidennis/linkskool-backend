<?php

namespace V3\App\Controllers\Portal\ELearning;

use Exception;
use V3\App\Common\Utilities\HttpStatus;
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

        try {
            $newId = $this->topicService->addTopic($data);
            if ($newId) {
                return $this->respond(
                    data: [
                        'success' => true,
                        'topicId' => $newId,
                        'message' => 'Topic created successfully.'
                    ],
                    statusCode: HttpStatus::CREATED
                );
            }

            return $this->respondError('Failed to create topic');
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function get(array $vars)
    {
        $data = $this->validateData($vars, ['syllabus_id']);

        try {
            $this->respond([
                'success' => true,
                'response' => $this->topicService->getTopics($data['syllabus_id'])
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
