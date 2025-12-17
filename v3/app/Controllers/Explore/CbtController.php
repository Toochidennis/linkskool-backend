<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Common\Utilities\ResponseHandler;
use V3\App\Services\Explore\CbtService;

#[Group("/public/cbt")]
class CbtController extends ExploreBaseController
{
    private CbtService $cbtService;

    public function __construct()
    {
        parent::__construct();
        $this->cbtService = new CbtService($this->pdo);
    }

    /**
     * GET /public/cbt/exams
     * Returns all exam types with their courses and available years.
     */
    #[Route('/exams', 'GET', ['api'])]
    public function getAllExams()
    {
        $this->respond([
            'success' => true,
            'data' => $this->cbtService->getFormattedExamHierarchy()
        ]);
    }

    /**
     * GET /public/cbt/exams/{examTypeId}/courses
     * Returns all courses for a given exam type.
     */
    #[Route('/exams/{examTypeId:\d+}/courses', 'GET', ['api'])]
    public function getCoursesByExamType(array $vars)
    {
        // You’ll later add logic to fetch specific courses for a given examTypeId
        ResponseHandler::sendJsonResponse([
            'success' => true,
            'message' => 'Coming soon: fetch courses by exam type',
            'params'  => $vars
        ]);
    }

    /**
     * GET /public/cbt/exams/{examTypeId}/courses/{courseId}/questions
     * Returns all questions for a given exam type and course.
     */
    #[Route('/exams/{exam_id:\d+}/questions', 'GET', ['api'])]
    public function getQuestions(array $vars)
    {
        $validated = $this->validate($vars, [
            'exam_id' => 'required|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:0'
        ]);

        $questions = $this->cbtService->getExamWithQuestions($validated);

        if (empty($questions['questions'])) {
            $this->respondError(
                'No questions found for the specified exam.',
                HttpStatus::NOT_FOUND
            );
        }

        $this->respond([
            'success' => true,
            'data' => $questions
        ]);
    }

    #[Route('/exams/questions/by-topic', 'GET', ['api'])]
    public function getQuestionsByTopicId(array $vars)
    {
        $validated = $this->validate($vars, [
            'topic_id' => 'required|integer|min:1',
            'course_id' => 'required|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100'
        ]);

        $questions = $this->cbtService->fetchQuestionsByTopicId($validated);

        if (empty($questions)) {
            $this->respondError(
                'No questions found for the specified topic and course.',
                HttpStatus::NOT_FOUND
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'Questions fetched successfully.',
            'data' => $questions
        ]);
    }
}
