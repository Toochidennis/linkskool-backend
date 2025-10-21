<?php

/**
 * This class helps handles course
 *
 * PHP version 8.2+
 *
 * @category Controller
 * @package Linkskool
 * @author ToochiDennis <dennistoochukwu@gmail.com>
 * @license MIT
 * @link https://www.linkskool.net
 */

namespace V3\App\Controllers\Portal\Academics;

use Illuminate\Support\Arr;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\Academics\CourseService;

#[Group('/portal')]
class CourseController extends BaseController
{
    private CourseService $courseService;

    public function __construct()
    {
        parent::__construct();
        $this->courseService = new CourseService(pdo: $this->pdo);
    }

    #[Route('/courses', 'POST', ['auth', 'role:admin'])]
    public function addCourse()
    {
        $data = $this->validate(
            data: $this->post,
            rules: [
                'course_name' => 'required|string|filled',
                'course_code' => 'required|string|filled'
            ]
        );

        $courseId = $this->courseService->insertCourse($data);

        if ($courseId) {
            return $this->respond([
                'success' => true,
                'message' => 'Course added successfully.',
                'course_id' => $courseId
            ], HttpStatus::CREATED);
        }

        return $this->respondError('Failed to create course');
    }

    #[Route('/courses/{id:\d+}', 'PUT', ['auth', 'role:admin'])]
    public function updateCourse(array $vars)
    {
        $data = $this->validate(
            data: array_merge($this->post, $vars),
            rules: [
                'id' => 'required|integer|filled',
                'course_name' => 'required|string|filled',
                'course_code' => 'required|string|filled'
            ]
        );

        $updated = $this->courseService->updateCourse($data);

        if ($updated) {
            return $this->respond([
                'success' => true,
                'message' => 'Course updated successfully.'
            ], HttpStatus::OK);
        }

        return $this->respondError(
            'Failed to update course',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route('/courses', 'GET', ['auth', 'role:admin'])]
    public function getCourses()
    {
        $this->respond([
            'success' => true,
            'response' => $this->courseService->fetchCourses(),
        ]);
    }

    #[Route('/courses/{id:\d+}', 'DELETE', ['auth', 'role:admin'])]
    public function deleteCourse(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'id' => 'required|integer'
            ]
        );

        $deleted = $this->courseService->deleteCourse(Arr::get($data, 'id'));

        if ($deleted) {
            return $this->respond([
                'success' => true,
                'message' => 'Course deleted successfully.'
            ], HttpStatus::OK);
        }

        return $this->respondError(
            'Failed to delete course',
            HttpStatus::BAD_REQUEST
        );
    }
}
