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
        $this->topicService = new TopicService($this->pdo);
    }


    #[Route('/generate/{limit:\d+}', 'GET', ['api'])]
    public function generateTopics(array $vars): void
    {
        $validated = $this->validate($vars, [
            'limit' => 'required|integer|min:1'
        ]);

        $result = $this->topicService->processQuestions($validated['limit']);
        $this->respond([
            'success' => true,
            'data' => $result
        ]);
    }


    #[Route('', 'GET', ['api'])]
    public function fetchSyllabusAndTopics(array $vars)
    {
        $validated = $this->validate($vars, [
            'course_id' => 'required|integer|min:1',
            'exam_type_id' => 'required|integer|min:1'
        ]);

        $syllabusAndTopics = $this->topicService->getSyllabusAndTopics(
            $validated['course_id'],
            $validated['exam_type_id']
        );

        $this->respond([
            'success' => true,
            'message' => 'Syllabus and topics fetched successfully.',
            'data' => $syllabusAndTopics
        ]);
    }
}
