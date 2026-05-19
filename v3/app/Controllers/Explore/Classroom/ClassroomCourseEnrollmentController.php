<?php

namespace V3\App\Controllers\Explore\Classroom;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\Explore\ExploreBaseController;
use V3\App\Services\Explore\Classroom\ClassroomCourseEnrollmentService;

#[Group('/public/classroom/enrollments')]
class ClassroomCourseEnrollmentController extends ExploreBaseController
{
    private ClassroomCourseEnrollmentService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ClassroomCourseEnrollmentService($this->pdo);
    }

    #[Route('', 'GET', ['api'])]
    public function getEnrolledCourses(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'student_id'     => 'required|integer',
                'institution_id' => 'required|integer',
            ]
        );

        $courses = $this->service->getEnrolledCourses(
            (int) $validated['student_id'],
            (int) $validated['institution_id']
        );

        $this->respond(
            [
                'status' => true,
                'data'   => $courses,
            ],
            HttpStatus::OK
        );
    }

    #[Route('', 'POST', ['api'])]
    public function enroll(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'join_code'      => 'required|string',
                'institution_id' => 'required|integer',
                'student_id'     => 'required|integer',
            ]
        );

        try {
            $result = $this->service->enroll($validated);
        } catch (\InvalidArgumentException $e) {
            $this->respondError($e->getMessage(), HttpStatus::BAD_REQUEST);
        }

        $this->respond(
            [
                'status'  => true,
                'message' => 'Enrolled successfully.',
                'data'    => $result,
            ],
            HttpStatus::CREATED
        );
    }
}
