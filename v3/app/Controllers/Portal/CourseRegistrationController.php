<?php

namespace V3\App\Controllers\Portal;

use V3\App\Models\Portal\Result;
use V3\App\Services\Portal\CourseRegistrationService;
use V3\App\Utilities\DatabaseConnector;
use V3\App\Utilities\DataExtractor;
use V3\App\Utilities\ResponseHandler;
use V3\App\Models\Portal\Student;

class CourseRegistrationController
{
    private CourseRegistrationService $registrationService;
    private Result $result;
    private array $post;
    private Student $student;
    private array $response = ['success' => false];

    public function __construct()
    {
        $this->initialize();
    }

    public function initialize()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->post = DataExtractor::extractPostData();
            $dbname = $this->post['_db'] ?? '';
        } else {
            $dbname = $_GET['_db'] ?? '';
        }

        if (!empty($dbname)) {
            $db = DatabaseConnector::connect(dbname: $dbname);
            $this->result = new Result(pdo: $db);
            $this->student = new Student(pdo: $db);
        } else {
            $this->response['message'] = '_db is required.';
            http_response_code(400);
            ResponseHandler::sendJsonResponse(response: $this->response);
        }
    }

    public function registerCourses()
    {
        try {
            $sanitizedData = $this->registrationService->validateAndGetData(post: $this->post);
        } catch (\InvalidArgumentException $e) {
            http_response_code(response_code: 400);
            $this->response['message'] = $e->getMessage();
            ResponseHandler::sendJsonResponse(response: $this->response);
        }

        try {
            if ($sanitizedData) {
                $data = $sanitizedData['data'];
                $courses = $data['courses'];

                if ($sanitizedData['type'] === 'class') {
                    $classId = $data['class'];

                    $students = $this->student->getStudents(columns: ['id'], conditions: ['class' => $classId]);
                    if ($students) {
                        $this->register($students, $courses, $data['term'], $data['year']);
                    }
                } else {
                    $this->register($data['students'], $courses, $data['term'], $data['year']);
                }
            }
        } catch (\PDOException $e) {
            http_response_code(response_code: 500);
            $this->response['message'] = $e->getMessage();
        }
    }

    public function fetchRegisteredCourses() {}

    public function duplicateRegistration() {}

    public function unregisterCourses() {}

    private function register($students, $courses, $term, $year)
    {
        $index = 0;
        foreach ($students as $key => $studentId) {
            foreach ($courses as $courseId) {
                $isRegistered = $this->result->isCourseIsRegistered(conditions: [
                    'reg_no' => $studentId,
                    'course' => $courseId,
                    'term' => $term,
                    'year' => $year
                ]);

                if (!$isRegistered) {
                    $this->result->registerCourse(data: [
                        'reg_no' => $studentId,
                        'course' => $courseId,
                        'term' => $term,
                        'year' => $year
                    ]);
                }
            }
            $index++;
        }

        if ($index === count($students)) {
            $response = ['success' => true, 'message' => 'Courses registered successfully'];
        }
    }

    private function modifyRegisteredCourses($students, $courses, $term, $year){
//         $coursesToDelete = array_diff($currentCourses, $newCourseIds);
// $coursesToAdd    = array_diff($newCourseIds, $currentCourses);
    }
}
