<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Services\Explore\StudyContentService;

#[Group('/public/cbt/study')]
class StudyContentController extends ExploreBaseController
{
    private StudyContentService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new StudyContentService($this->pdo);
    }

    #[Route('/exam-types/{exam_type_id:\d+}/courses/{course_id:\d+}/topics', 'GET', ['api'])]
    public function getStudyTopics(array $vars)
    {
        $data = $this->validate($vars, [
            'course_id' => 'required|integer|min:1',
            'exam_type_id' => 'required|integer|min:1',
        ]);

        $topics = $this->service
            ->getStudyTopics($data['exam_type_id'], $data['course_id']);

        $this->respond([
            'success' => true,
            'message' => 'Study topics retrieved successfully.',
            'data' => $topics
        ]);
    }

    #[Route('/topics/{topic_id:\d+}/content', 'GET', ['api'])]
    public function getStudyContent(array $vars)
    {
        $data = $this->validate($vars, [
            'topic_id' => 'required|integer|min:1',
        ]);

        $categories = $this->service->getTopicContentByTopicId($data['topic_id']);

        $this->respond([
            'success' => true,
            'message' => 'Study categories retrieved successfully.',
            'data' => $categories
        ]);
    }
}
