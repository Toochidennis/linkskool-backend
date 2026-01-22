<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\CourseContentService;

#[Group('/public')]
class ProgramCourseController extends ExploreBaseController
{
    private CourseContentService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new CourseContentService($this->pdo);
    }

    #[Route('/seed/courses', 'POST')]
    public function create()
    {
        $success = $this->service->seedCourses();

        if (!$success) {
            $this->respondError(
                'Failed to seed courses.'
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Courses seeded successfully.'
            ]
        );
    }

    #[Route('/seed/programs', 'POST')]
    public function createPrograms()
    {
        $success = $this->service->seedPrograms();

        if (!$success) {
            $this->respondError(
                'Failed to seed programs.'
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Programs seeded successfully.'
            ]
        );
    }

    #[Route('/seed/cohort', 'POST')]
    public function createCohort(array $vars)
    {
        $validatedData = $this->validate(
            $vars,
            [
                'course_id' => 'required|integer',
                'program_id' => 'required|integer',
                'course_name' => 'required|string|max:255',
            ]
        );

        $id =  $this->service->seedCohorts($validatedData);

        if (!$id) {
            $this->respondError(
                'Failed to create cohort.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Cohort created successfully.',
                'id' => $id
            ],
            HttpStatus::CREATED
        );
    }

        #[Route('/seed/cohort/lessons', 'POST')]
    public function createCohortLessons(array $vars)
    {
        $validatedData = $this->validate(
            $vars,
            [
                'course_id' => 'required|integer',
                'program_id' => 'required|integer',
                'category_id' => 'required|integer',
                'course_name' => 'required|string|max:255',
                'cohort_id' => 'required|integer',
            ]
        );

        $id =  $this->service->seedCohorts($validatedData);

        if (!$id) {
            $this->respondError(
                'Failed to create cohort.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Cohort created successfully.',
                'id' => $id
            ],
            HttpStatus::CREATED
        );
    }
}
