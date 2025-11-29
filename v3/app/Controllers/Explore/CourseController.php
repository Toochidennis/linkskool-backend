<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\CourseService;

#[Group('/public')]
class CourseController extends ExploreBaseController
{
    private CourseService $courseService;

    public function __construct()
    {
        parent::__construct();
        $this->courseService = new CourseService($this->pdo);
    }

    #[Route('/courses', 'POST', ['api', 'auth', 'role:admin'])]
    public function storeCourse(): void
    {
        $data = $this->validate($this->getRequestData(), [
            'course_name' => 'required|string|filled',
        ]);

        $courseId = $this->courseService->createCourse($data);

        if ($courseId <= 0) {
            $this->respondError(
                'Failed to create course.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'Course created successfully.',
            'course_id' => $courseId
        ]);
    }

    #[Route('/courses/{id:\d+}', 'PUT', ['api', 'auth', 'role:admin'])]
    public function updateCourse(array $vars): void
    {
        $data = $this->validate(
            \array_merge($this->getRequestData(), $vars),
            [
                'id' => 'required|integer',
                'course_name' => 'required|string|filled',
            ]
        );

        $updated = $this->courseService->updateCourse($data['id'], $data);

        if (!$updated) {
            $this->respondError(
                'Failed to update course.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'Course updated successfully.'
        ]);
    }

    #[Route('/courses', 'GET', ['api', 'auth', 'role:admin'])]
    public function getAllCourses(): void
    {
        $courses = $this->courseService->getAllCourses();

        $this->respond([
            'success' => true,
            'data' => $courses
        ]);
    }

    #[Route('/courses/{id:\d+}', 'DELETE', ['api', 'auth', 'role:admin'])]
    public function deleteCourse(array $vars): void
    {
        $data = $this->validate($vars, [
            'id' => 'required|integer',
        ]);

        $deleted = $this->courseService->deleteCourse($data['id']);

        if (!$deleted) {
            $this->respondError('Failed to delete course.', HttpStatus::BAD_REQUEST);
        }

        $this->respond([
            'success' => true,
            'message' => 'Course deleted successfully.'
        ]);
    }
}
