<?php

namespace V3\App\Controllers\Portal\ELearning;

use Exception;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\ELearning\TopicService;

#[Group('/portal')]
class TopicController extends BaseController
{
    private TopicService $topicService;

    public function __construct()
    {
        parent::__construct();
        $this->topicService = new TopicService($this->pdo);
    }

    #[Route('/elearning/topic', 'POST', ['auth', 'role:admin', 'role:staff'])]
    public function store()
    {
        $data =  $this->validate(
            data: $this->post,
            rules: [
                'topic' => 'required|string|filled',
                'objective' => 'required|string|filled',
                'classes' => 'required|array|min:1',
                'classes.*.id' => 'required|integer',
                'classes.*.name' => 'required|string|filled',
                'syllabus_id' => 'required|integer',
                'creator_name' => 'required|string|filled',
                'creator_id' => 'required|integer',
                'course_id' => 'required|integer',
                'course_name' => 'required|string|filled',
                'level_id' => 'required|integer',
                'term' => 'required|integer',
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


    #[Route('/elearning/topic/{id:\d+}', 'PUT', ['auth', 'role:admin', 'role:staff'])]

    public function update()
    {
        $data = $this->validate(
            data: $this->post,
            rules: [
                'id' => 'required|integer',
                'topic' => 'required|string|filled',
                'objective' => 'required|string|filled',
                'classes' => 'required|array|min:1',
                'classes.*.id' => 'required|integer',
                'classes.*.name' => 'required|string|filled',
            ]
        );

        try {
            $newId = $this->topicService->updateTopic($data);

            if ($newId > 0) {
                $this->respond([
                    'success' => true,
                    'message' => 'Topic updated successfully'
                ]);
            }

            $this->respondError('Failed to update', HttpStatus::BAD_REQUEST);
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    #[Route('/elearning/syllabus/{syllabus_id:\d+}/topics', 'GET', ['auth', 'role:admin', 'role:staff'])]
    public function get(array $vars)
    {
        $data = $this->validate($vars, ['syllabus_id' => 'required|integer']);

        try {
            $this->respond([
                'success' => true,
                'response' => $this->topicService->getTopics($data['syllabus_id'])
            ]);
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }
}
