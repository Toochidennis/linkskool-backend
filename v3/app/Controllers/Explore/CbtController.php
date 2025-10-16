<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\ResponseHandler;
use V3\App\Database\DatabaseConnector;
use V3\App\Services\Explore\CbtService;

#[Group("/public/cbt")]
class CbtController
{
    private CbtService $cbtService;

    public function __construct()
    {
        $pdo = DatabaseConnector::connect();
        $this->cbtService = new CbtService($pdo);
    }

    /**
     * GET /public/cbt/exams
     * Returns all exam types with their courses and available years.
     */
    #[Route('/exams', 'GET', ['api'])]
    public function getAllExams()
    {
        ResponseHandler::sendJsonResponse([
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
        ResponseHandler::sendJsonResponse(
            $this->cbtService->getExamWithQuestions($vars['exam_id'])
        );
    }
}
