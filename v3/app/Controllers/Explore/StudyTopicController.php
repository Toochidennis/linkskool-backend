<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\StudyTopicService;

#[Group('/public/cbt/study')]
class StudyTopicController extends ExploreBaseController
{
    private StudyTopicService $studyTopicService;

    public function __construct()
    {
        parent::__construct();
        $this->studyTopicService = new StudyTopicService($this->pdo);
    }

    #[Route('/topics', 'POST', ['api', 'auth', 'role:admin'])]
    public function storeTopic(): void
    {
        $data = $this->validate($this->getRequestData(), [
            'title' => 'required|string|filled',
            'sub_topics' => 'required|array',
            'course_id' => 'required|integer|min:1',
            'course_name' => 'required|string|filled',
            'category_id' => 'nullable|integer|min:1',
            'category_name' => 'nullable|string|filled',
        ]);

        $topicId = $this->studyTopicService->addTopic($data);

        if ($topicId <= 0) {
            $this->respondError('Failed to create topic.', HttpStatus::BAD_REQUEST);
        }

        $this->respond([
            'success' => true,
            'message' => 'Topic created successfully.',
            'topic_id' => $topicId,
        ]);
    }

    #[Route('/topics/{id:\d+}', 'PUT', ['api', 'auth', 'role:admin'])]
    public function updateTopic(array $vars): void
    {
        $data = $this->validate([...$this->getRequestData(), ...$vars], [
            'id' => 'required|integer|min:1',
            'title' => 'required|string|filled',
            'sub_topics' => 'required|array',
            'course_id' => 'required|integer|min:1',
            'course_name' => 'required|string|filled',
            'category_id' => 'nullable|integer|min:1',
            'category_name' => 'nullable|string|filled',
        ]);

        $updated = $this->studyTopicService->updateTopic($data['id'], $data);

        if (!$updated) {
            $this->respondError('Failed to update topic.', HttpStatus::BAD_REQUEST);
        }

        $this->respond([
            'success' => true,
            'message' => 'Topic updated successfully.',
        ]);
    }

    #[Route('/topics', 'GET', ['api'])]
    public function getAllTopics(): void
    {
        $this->respond([
            'success' => true,
            'data' => $this->studyTopicService->getAllTopics(),
        ]);
    }

    #[Route('/courses/{course_id:\d+}/topics', 'GET', ['api'])]
    public function getTopicsByCourseId(array $vars): void
    {
        $data = $this->validate($vars, [
            'course_id' => 'required|integer|min:1',
        ]);

        $this->respond([
            'success' => true,
            'message' => 'Topics retrieved successfully.',
            'data' => $this->studyTopicService->getTopicsByCourseId($data['course_id']),
        ]);
    }
}
