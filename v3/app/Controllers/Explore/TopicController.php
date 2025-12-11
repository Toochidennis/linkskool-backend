<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Services\Explore\TopicService;

#[Group('/public/cbt/topics')]
class TopicController extends ExploreBaseController
{
    private TopicService $topicService;

    public function __construct()
    {
        parent::__construct();
        $this->topicService = new TopicService();
    }

    /**
     * GET /public/cbt/topics/{courseId}
     * Returns all topics for a given course ID.
     */
    #[Route('/{course_id:\d+}', 'GET', ['api'])]
    public function getTopicsByCourseId(array $vars)
    {
        $validated = $this->validate($vars, [
            'course_id' => 'required|integer|min:1'
        ]);

        $topics = $this->topicService->getTopicsByCourseId($validated['course_id']);

        $this->respond([
            'success' => true,
            'message' => 'Topics fetched successfully.',
            'data' => $topics
        ]);
    }
}
