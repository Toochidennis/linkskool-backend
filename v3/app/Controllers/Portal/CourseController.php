<?php

/**
 * This class helps handles student's course result
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
use V3\App\Models\Portal\Course;
use V3\App\Utilities\HttpStatus;
use V3\App\Traits\ValidationTrait;
use V3\App\Controllers\BaseController;

/**
 * Class CourseController
 *
 * Handles course-related operations.
 */
class CourseController extends BaseController
{
    use ValidationTrait;

    private Course $course;

    public function __construct()
    {
        parent::__construct();
        $this->initialize();
    }

    private function initialize()
    {
        $this->course = new Course(pdo: $this->pdo);
    }

    public function addCourse()
    {
        $requiredFields = ['course_name', 'course_code'];
        $data = $this->validateData($this->post, $requiredFields);

        try {
            $courseId = $this->course->insert($data);

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
            $result = $this->course->get();
            $this->respond(['success' => true, 'courses' => $result]);
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
