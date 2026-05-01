<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Services\Explore\ProgramContentAnalyticsService;
use V3\App\Services\Explore\ProgramContentQuizService;
use V3\App\Services\Explore\ProgramContentService;

#[Group('/public/learning')]
class ProgramContentController extends ExploreBaseController
{
    private ProgramContentService $programContentService;
    private ProgramContentQuizService $programContentQuizService;
    private ProgramContentAnalyticsService $programContentAnalyticsService;

    public function __construct()
    {
        parent::__construct();
        $this->programContentService = new ProgramContentService($this->pdo);
        $this->programContentQuizService = new ProgramContentQuizService($this->pdo);
        $this->programContentAnalyticsService = new ProgramContentAnalyticsService($this->pdo);
    }

    #[Route('/programs', 'GET', ['api'])]
    public function getProgramsWithCourses(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            [
                'birth_date' => 'nullable|date',
                'profile_id' => 'nullable|integer',
            ]
        );

        $data = $this->programContentService->getProgramsWithCourses(
            $validated['birth_date'] ?? null,
            isset($validated['profile_id']) ? (int) $validated['profile_id'] : null
        );

        $this->respond(
            [
                'success' => true,
                'message' => 'Programs and courses retrieved successfully.',
                'data' => $data,
            ]
        );
    }

    #[Route('/programs/content', 'GET', )]
    public function getProgramsWithCoursesContent(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            [
                'birth_date' => 'nullable|date',
                'profile_id' => 'nullable|integer',
            ]
        );

        $data = $this->programContentService->getProgramsWithCoursesContent(
            $validated['birth_date'] ?? null,
            isset($validated['profile_id']) ? (int) $validated['profile_id'] : null
        );

        $this->respond(
            [
                'success' => true,
                'message' => 'Program content retrieved successfully.',
                'data' => $data,
            ]
        );
    }

    #[Route('/cohorts/{cohort_id}', 'GET', ['api'])]
    public function getActiveCohortByCourse(array $vars)
    {
        $validated = $this->validate(
            $vars,
            [
                'cohort_id' => 'required|integer',
            ]
        );

        $result = $this->programContentService
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

        $lessons = $this->programContentService->getLessonsByCohort($validated['cohort_id']);

        $this->respond(
            [
                'success' => true,
                'message' => 'Lessons retrieved successfully.',
                'data' => $lessons,
            ]
        );
    }

    #[Route('/cohorts/{cohort_id}/lessons/v2', 'GET', ['api'])]
    public function cohortLessonsV2(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            [
                'cohort_id' => 'required|integer',
                'profile_id' => 'required|integer'
            ]
        );

        $response = $this->programContentService->getLessonsByCohortWithNextCourse(
            $validated['cohort_id'],
            $validated['profile_id'],
        );

        $this->respond(
            [
                'success' => true,
                'message' => 'Lessons retrieved successfully.',
                'data' => $response,
            ]
        );
    }

    #[Route('/cohorts/{cohort_id}/profiles/{profile_id}/lessons', 'GET', ['api'])]
    public function getCohortLessonsWithSubmission(array $vars)
    {
        $validated = $this->validate(
            $vars,
            [
                'cohort_id' => 'required|integer',
                'profile_id' => 'required|integer',
            ]
        );

        $rows = $this->programContentService->getCohortLessonsWithSubmission(
            $validated['cohort_id'],
            $validated['profile_id']
        );

        $this->respond(
            [
                'success' => true,
                'message' => 'Cohort lessons with submissions retrieved successfully.',
                'data' => $rows
            ]
        );
    }

    #[Route('/lessons/{lesson_id}', 'GET', ['api'])]
    public function getCohortLessonById(array $vars)
    {
        $validated = $this->validate(
            $vars,
            [
                'profile_id' => 'required|integer',
                'lesson_id' => 'required|integer',
            ]
        );

        $result = $this->programContentService
            ->getLessonById($validated['lesson_id'], $validated['profile_id']);

        return $this->respond([
            'success' => true,
            'data' => $result
        ]);
    }

    #[Route('/lessons/{lesson_id}/quizzes', 'GET', ['api'])]
    public function courseLessonQuizzes(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            [
                'lesson_id' => 'required|integer',
            ]
        );

        $quizzes = $this->programContentQuizService
            ->getLessonQuiz($validated['lesson_id']);

        $this->respond(
            [
                'success' => true,
                'message' => 'Lesson quizzes retrieved successfully.',
                'data' => $quizzes,
            ]
        );
    }

    #[Route('/profiles/{profile_id}/stats', 'GET', ['api'])]
    public function getUserLearningStats(array $vars)
    {
        $validated = $this->validate(
            $vars,
            [
                'profile_id' => 'required|integer',
            ]
        );

        $profileId = $validated['profile_id'];

        $row = $this->programContentAnalyticsService->getUserLearningStats($profileId);

        $this->respond(
            [
                'success' => true,
                'message' => 'Learning stats retrieved successfully.',
                'data' => $row
            ]
        );
    }

    #[Route('/cohorts/{cohort_id}/profiles/{profile_id}/lesson-performance', 'GET', ['api'])]
    public function getLessonPerformanceByCohortAndProfile(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            [
                'profile_id' => 'required|integer',
                'cohort_id' => 'required|integer',
            ]
        );

        $data = $this->programContentAnalyticsService
            ->getLessonPerformanceByProfile(
                (int) $validated['profile_id'],
                (int) $validated['cohort_id']
            );

        $this->respond(
            [
                'success' => true,
                'message' => 'Lesson performance retrieved successfully.',
                'data' => $data,
            ]
        );
    }

    #[Route('/cohorts/{cohort_id}/profiles/{profile_id}/leaderboard', 'GET', ['api'])]
    public function getLeaderboardByCohortAndProfile(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            [
                'profile_id' => 'required|integer',
                'cohort_id' => 'required|integer',
            ]
        );

        $data = $this->programContentAnalyticsService
            ->getLeaderboardByCohort(
                (int) $validated['profile_id'],
                (int) $validated['cohort_id']
            );

        $this->respond(
            [
                'success' => true,
                'message' => 'Leaderboard retrieved successfully.',
                'data' => $data,
            ]
        );
    }

    #[Route('/profiles/{profile_id}/upcoming-cohorts/{slug}', 'GET', ['api'])]
    public function upcomingCohorts(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            [
                'profile_id' => 'required|integer',
                'slug' => 'required|string',
            ]
        );

        $data = $this->programContentService->getUpcomingCohorts(
            $validated['slug'],
            (int) $validated['profile_id'],
        );

        $this->respond(
            [
                'success' => true,
                'message' => 'Upcoming cohorts retrieved successfully.',
                'data' => $data,
            ]
        );
    }
}
