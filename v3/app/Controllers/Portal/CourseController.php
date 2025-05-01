<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Models\Portal\Course;
use V3\App\Utilities\HttpStatus;
use V3\App\Traits\ValidationTrait;
use V3\App\Utilities\ResponseHandler;
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

            $this->response = $courseId ?
                [
                    'success' => true,
                    'message' => 'Course added successfully.',
                ] :
                [
                    'success' => false,
                    'message' => 'Failed to add course',
                ];
        } catch (Exception $e) {
            http_response_code(response_code: HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function updateCourse()
    {
    }

    public function getCourses()
    {
        try {
            $result = $this->course->get();
            $this->response = ['success' => true, 'courses' => $result];
        } catch (Exception $e) {
            http_response_code(response_code: HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getStudentRegisteredCourses(array $vars)
    {
        $data = $this->validateData($vars, ['id', 'class_id', 'term', 'year']);

        try {
            $result = $this->course
                ->select(columns: ['course_table.id', 'course_table.course_name'])
                ->join('result_table', 'course_table.id = result_table.course')
                ->where('result_table.term', '=', $data['term'])
                ->where('result_table.year', '=', $data['year'])
                ->where('result_table.class', '=', $data['class_id'])
                ->where('result_table.reg_no', '=', $data['id'])
                ->get();

                $this->response = ['success' => true, 'registered_courses' => $result];
        } catch (Exception $e) {
            http_response_code(response_code: HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getCourseById()
    {
    }

    public function deleteCourse()
    {
    }
}
