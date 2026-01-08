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


    #[Route('/categories-and-courses', 'GET', ['api'])]
    public function getCategoriesAndCourses(): void
    {
        $data = $this->learningPathService->getCategoriesAndCourses();

        $this->respond(
            [
                'success' => true,
                'message' => 'Categories and courses retrieved successfully.',
                'data' => $data,
            ]
        );
    }

    #[Route('/lessons', 'GET', ['api'])]
    public function courseLessons(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            [
                'category_id' => 'required|integer',
                'course_id' => 'required|integer',
            ]
        );

        $lessons = $this->learningPathService
            ->courseLessons($validated['category_id'], $validated['course_id']);

        $this->respond(
            [
                'success' => true,
                'message' => 'Lessons retrieved successfully.',
                'data' => $lessons,
            ]
        );
    }
}
