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
                ->rawQuery(
                    query: "SELECT c.id, c.course_name FROM course_table c 
                        INNER JOIN result_table rt ON c.id = rt.course 
                        AND rt.term = ? AND rt.year = ? AND
                        rt.class = ? WHERE rt.reg_no = ?",
                    bindings: [
                        $data['term'],
                        $data['year'],
                        $data['class_id'],
                        $data['id']
                    ]
                );
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
