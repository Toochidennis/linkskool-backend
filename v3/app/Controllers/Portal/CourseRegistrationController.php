<?php

namespace V3\App\Controllers\Portal;

use Exception;
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

    /**
     * Initializes the controller by extracting POST/GET data and connecting to the database.
     */
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
            $this->registrationService = new CourseRegistrationService(pdo: $db);
        } else {
            $this->response['message'] = '_db is required.';
            http_response_code(400);
            ResponseHandler::sendJsonResponse(response: $this->response);
        }
    }

    /**
     * Handles course registration.
     */
    public function registerCourses()
    {
        try {
            $data = $this->registrationService->validateAndGetData(post: $this->post);
        } catch (\InvalidArgumentException $e) {
            http_response_code(response_code: 400);
            $this->response['message'] = $e->getMessage();
            ResponseHandler::sendJsonResponse(response: $this->response);
        }

        try {
            if ($data) {
                $courses = $data['courses'];
                $classId = $data['class_id'];

                if ($data['type'] === 'class') {
                    $students = $this->student->getStudents(columns: ['id AS student_id'], conditions: ['student_class' => $classId]);
                }

                $register = ($data['type'] === 'class') ?
                    $this->registrationService->register(
                        $students,
                        $courses,
                        $data['term'],
                        $data['year'],
                        $classId
                    )
                    :
                    $this->registrationService->register(
                        $data['students'],
                        $courses,
                        $data['term'],
                        $data['year'],
                        $classId
                    );

                $this->response = $register ?
                    ['success' => true, 'message' => 'Courses registered successfully']
                    : ['success' => false, 'message' => 'Failed to register courses'];
            }
        } catch (Exception $e) {
            http_response_code(response_code: 500);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function fetchRegisteredCourses() {}

    /**
     * Duplicates course registrations for a class if the academic year remains unchanged.
     *
     * This method performs the following steps:
     * 1. Validates and cleans the input data using the registration service.
     * 2. Fetches the current registered courses for the specified class.
     * 3. Checks if the current academic year matches the new data.
     * 4. Extracts unique student IDs and course IDs from the current registrations.
     * 5. Calls the registration service to register courses for these students.
     * 6. Sends a JSON response with the result.
     *
     * @return void
     */
    public function duplicateRegistration(): void
    {
        try {
            // Validate and clean the input data.
            $data = $this->registrationService->validateAndCleanData($this->post);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            $this->response['message'] = $e->getMessage();
            ResponseHandler::sendJsonResponse($this->response);
        }

        try {
            // Fetch existing registrations for the class.
            $oldRegistrations = $this->result->getRegisteredCourses(
                columns: ['year', 'course AS course_id', 'reg_no AS student_id'],
                conditions: ['class' => $data['class_id']]
            );

            // Check if there are any existing registrations.
            if (empty($oldRegistrations)) {
                http_response_code(404);
                $this->response['message'] = 'No existing registrations found to duplicate.';
                ResponseHandler::sendJsonResponse($this->response);
            }

            // Ensure that the academic year remains the same.
            $oldYear = $oldRegistrations[0]['year'];
            $newYear = $data['year'];
            if ($oldYear !== $newYear) {
                http_response_code(400);
                $this->response['message'] = "Cannot duplicate registrations: academic year mismatch (existing: $oldYear, new: $newYear).";
                ResponseHandler::sendJsonResponse($this->response);
            }

            // Extract student IDs from the current registrations.
            $students = array_map(
                fn($registration) => ['student_id' => $registration['student_id']],
                $oldRegistrations
            );

            // Extract course IDs. 
            $courses = array_map(
                fn($registration) => ['course_id' => $registration['course_id']],
                $oldRegistrations
            );

            // Remove duplicates if any.
            $courses = array_values(array_unique($courses));
            $students = array_values(array_unique($students));

            // Call the registration service to duplicate the registrations.
            $register = $this->registrationService->register(
                $students,
                $courses,
                $data['term'],
                $oldYear,
                $data['class_id']
            );

            // Set response based on registration success.
            $this->response = $register
                ? ['success' => true, 'message' => 'Courses registered successfully']
                : ['success' => false, 'message' => 'Failed to register courses'];
        } catch (\PDOException $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        } catch (Exception $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function unregisterCourses() {}
}
