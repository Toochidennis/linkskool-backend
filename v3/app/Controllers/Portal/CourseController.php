<?php

namespace V3\App\Controllers\Portal;

use V3\App\Models\Portal\Course;
use V3\App\Utilities\DataExtractor;
use V3\App\Utilities\ResponseHandler;
use V3\App\Utilities\DatabaseConnector;
use V3\App\Services\Portal\AuthService;
use V3\App\Services\Portal\CourseService;


/**
 * Class CourseController
 *
 * Handles course-related operations.
 */
class CourseController
{
    private array $response = ['success' => false, 'message' => ''];
    private array $post;
    private Course $course;
    private CourseService $courseService;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        AuthService::verifyAPIKey();
        AuthService::verifyJWT();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->post = DataExtractor::extractPostData();
            $dbname = $this->post['_db'] ?? '';
        } else {
            $dbname = $_GET['_db'] ?? '';
        }

        if (!empty($dbname)) {
            $db = DatabaseConnector::connect(dbname: $dbname);
            $this->course = new Course(pdo: $db);
        } else {
            $this->response['message'] = '_db is required.';
            http_response_code(401);
            ResponseHandler::sendJsonResponse(response: $this->response);
        }

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

            if ($result) {
                $courses  = array_map(function ($row) {
                    return [
                        'id' => $row['id'],
                        'course_name' => $row['course_name'],
                        'course_code' => $row['course_code']
                    ];
                }, $result);

                $this->response = ['success' => true, 'courses' => $courses];
            }else{
                $this->response = ['success' => true, 'courses' => []];
            }
        } catch (\PDOException $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getCourseById() {}

    public function deleteCourse() {}
}
