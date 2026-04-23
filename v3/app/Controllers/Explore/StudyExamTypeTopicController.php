<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\StudyExamTypeTopicsService;

#[Group('/public/cbt/study')]
class StudyExamTypeTopicController extends ExploreBaseController
{
    private StudyExamTypeTopicsService $studyExamTypeTopicsService;

    public function __construct()
    {
        parent::__construct();
        $this->studyExamTypeTopicsService = new StudyExamTypeTopicsService($this->pdo);
    }

    #[Route('/exam-types/{exam_type_id:\d+}/topics', 'POST', ['api', 'auth', 'role:admin'])]
    public function linkTopicsToExamType(array $vars): void
    {
        $data = $this->validate([...$this->getRequestData(), ...$vars], [
            'exam_type_id' => 'required|integer|min:1',
            'course_id' => 'required|integer|min:1',
            'topic_ids' => 'required|array',
            'topic_ids.*' => 'required|integer|min:1',
        ]);

        $linked = $this->studyExamTypeTopicsService->linkTopicToExamType($data);

        if (!$linked) {
            $this->respondError('Failed to link topics to exam type.', HttpStatus::BAD_REQUEST);
        }

        $this->respond([
            'success' => true,
            'message' => 'Topics linked to exam type successfully.',
        ]);
    }

    #[Route('/exam-types/{exam_type_id:\d+}/topics', 'GET', ['api'])]
    public function getTopicsByExamType(array $vars): void
    {
        $data = $this->validate($vars, [
            'exam_type_id' => 'required|integer|min:1',
        ]);

        $this->respond([
            'success' => true,
            'data' => $this->studyExamTypeTopicsService->getTopicsByExamType($data['exam_type_id']),
        ]);
    }
}
