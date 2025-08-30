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
        $data = $this->validateData(data: $this->post, requiredFields: ['course_name', 'course_code']);

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

    public function updateCourse()
    {
        // TODO
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

    public function getCourseById()
    {
        // TODO:
    }

    public function deleteCourse()
    {
        // TODO:
    }
}
