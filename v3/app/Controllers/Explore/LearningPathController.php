<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Services\Explore\LearningPathService;

#[Group('/public/learning')]
class LearningPathController extends ExploreBaseController
{
    private LearningPathService $learningPathService;

    public function __construct()
    {
        parent::__construct();
        $this->learningPathService = new LearningPathService($this->pdo);
    }

    #[Route('/programs', 'GET', ['api'])]
    public function getProgramsWithCourses(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            [
                'birth_date' => 'nullable|date',
                'user_id' => 'nullable|integer',
            ]
        );

        $data = $this->learningPathService->getProgramsWithCourses(
            $validated['birth_date'],
            $validated['user_id']
        );

        $this->respond(
            [
                'success' => true,
                'message' => 'Programs and courses retrieved successfully.',
                'data' => $data,
            ]
        );
    }

    #[Route('/cohorts/{cohort_id}')]
    public function getActiveCohortByCourse(array $vars)
    {
        $validated = $this->validate(
            $vars,
            [
                'cohort_id' => 'required|integer',
            ]
        );

        $result = $this->learningPathService
            ->getActiveCohortByCourse($validated['cohort_id']);

        return $this->respond([
            'success' => true,
            'data' => $result
        ]);
    }

    #[Route('/cohorts/{cohort_id}/lessons', 'GET', ['api'])]
    public function cohortLessons(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            [
                'cohort_id' => 'required|integer',
            ]
        );

        $lessons = $this->learningPathService->getLessonsByCohort($validated['cohort_id']);

        $this->respond(
            [
                'success' => true,
                'message' => 'Lessons retrieved successfully.',
                'data' => $lessons,
            ]
        );
    }

    #[Route('/lessons/{id}', 'GET', ['api'])]
    public function getCohortLessonById(array $vars)
    {
        $validated = $this->validate(
            $vars,
            [
                'cohort_id' => 'required|integer',
                'user_id' => 'required|integer',
            ]
        );
    }

    #[Route('/lessons/{id}/quizzes', 'GET', ['api'])]
    public function courseLessonQuizzes(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            [
                'lesson_id' => 'required|integer',
            ]
        );

        $quizzes = $this->learningPathService
            ->getLessonQuiz($validated['lesson_id']);

        $this->respond(
            [
                'success' => true,
                'message' => 'Lesson quizzes retrieved successfully.',
                'data' => $quizzes,
            ]
        );
    }
}
