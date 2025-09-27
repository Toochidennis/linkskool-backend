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

namespace V3\App\Controllers\Portal;

use Exception;
use Illuminate\Support\Arr;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Academics\CourseService;

/**
 * Class CourseController
 *
 * Handles course-related operations.
 */
class CourseController extends BaseController
{
    private CourseService $courseService;

    public function __construct()
    {
        parent::__construct();
        $this->courseService = new CourseService(pdo: $this->pdo);
    }

    public function addCourse()
    {
        $data = $this->validate(
            data: $this->post,
            rules: [
                'course_name' => 'required|string|filled',
                'course_code' => 'required|string|filled'
            ]
        );

        try {
            $courseId = $this->courseService->insertCourse($data);

            if ($courseId) {
                return $this->respond([
                    'success' => true,
                    'message' => 'Course added successfully.'
                ], HttpStatus::CREATED);
            }

            return $this->respondError('Failed to create course');
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

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

        try {
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
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getCourses()
    {
        try {
            $this->respond([
                'success' => true,
                'response' => $this->courseService->fetchCourses(),
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function deleteCourse(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'id' => 'required|integer|filled'
            ]
        );

        try {
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
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
