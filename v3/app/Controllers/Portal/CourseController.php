<?php

namespace V3\App\Controllers\Portal;

use V3\App\Models\Portal\Course;
use V3\App\Utilities\ResponseHandler;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\CourseService;

/**
 * Class CourseController
 *
 * Handles course-related operations.
 */
class CourseController extends BaseController
{
    private Course $course;
    private CourseService $courseService;

    public function __construct()
    {
        parent::__construct();
        $this->initialize();
    }

    private function initialize()
    {
        $this->course = new Course(pdo: $this->pdo);
        $this->courseService = new CourseService();
    }

    public function addCourse()
    {
        try {
            $data = $this->courseService->validateAndGetData(post: $this->post);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            $this->response['message'] = $e->getMessage();
            ResponseHandler::sendJsonResponse(response: $this->response);
        }

        try {
            $success = $this->course->insertCourse(data: $data);
            $this->response = [
                'success' => true,
                'message' => 'Course added successfully.',
                'course_id' => $success
            ];
        } catch (\PDOException $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        } catch (\Exception $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function updateCourse() {}

    public function getCourses()
    {
        try {
            $result = $this->course->getCourses();

            $courses  = array_map(function ($row) {
                return [
                    'id' => $row['id'],
                    'course_name' => $row['course_name'],
                    'course_code' => $row['course_code']
                ];
            }, $result);

            $this->response = ['success' => true, 'courses' => $courses];
        } catch (\PDOException $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getCourseById() {}

    public function deleteCourse() {}
}
