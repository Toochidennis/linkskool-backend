<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Utilities\HttpStatus;
use V3\App\Models\Portal\Student;
use V3\App\Traits\ValidationTrait;
use V3\App\Utilities\ResponseHandler;
use V3\App\Controllers\BaseController;
use V3\App\Models\Portal\CourseRegistration;
use V3\App\Services\Portal\CourseRegistrationService;

class CourseRegistrationController extends BaseController
{
    use ValidationTrait;
    private Student $student;
    private CourseRegistration $courseRegistration;
    private CourseRegistrationService $registrationService;


    public function __construct()
    {
        parent::__construct();
        $this->initialize();
    }

    /**
     * Initializes the controller by extracting POST/GET data and connecting to the database.
     */
    public function initialize()
    {
        $this->courseRegistration = new CourseRegistration(pdo: $this->pdo);
        $this->student = new Student(pdo: $this->pdo);
        $this->registrationService = new CourseRegistrationService(pdo: $this->pdo);
    }

    /**
     * Handles course registration.
     */
    public function registerCourses(string $type = '')
    {
        $requiredFields = ['courses', 'year', 'term', 'class_id'];
        $data = $this->validateData(data: $this->post, requiredFields: $requiredFields);

        try {
            $courses = $data['courses'];
            $classId = $data['class_id'];

            if ($type === 'class') {
                $students = $this->student
                    ->select(['id AS student_id'])
                    ->where('student_class', '=', $classId)
                    ->get();
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
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function registerClassCourses()
    {
        $this->registerCourses(type: 'class');
    }

    public function getRegistrationTerms(array $params)
    {
        $requiredFields = ['year', 'class_id'];
        $data = $this->validateData($params, $requiredFields);

        try {
            $formatted = [];
            // Fetch existing registrations for the class.
            $registrations = $this->courseRegistration
                ->select(columns: ['year', 'term'])
                ->where('class', '=', $data['class_id'])
                ->get();

            foreach ($registrations as $registration) {
                $year = $registration['year'];
                $term = $registration['term'];

                // Group by year
                if (!isset($formatted[$year])) {
                    $formatted[$year] = ['terms' => []];
                }

                if (!isset($formatted[$year]['terms'][$term])) {
                    $formatted[$year]['terms'] = [];
                }
                $formatted[$year]['terms'][] = $term;
            }

            $this->response = ['success' => true, 'sessions' => $formatted];
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    /**
     * Duplicates course registrations for a class if the academic year remains unchanged.
     *
     * This method performs the following steps:
     * 1. Validates and cleans the input data using the registration service.
     * 2. Fetches the current registered courses for the specified class.
     * 3. Checks if the current academic year matches the new data.
     * 4. Remove duplicates from the current registrations.
     * 5. Calls the registration service to register courses for these students.
     * 6. Sends a JSON response with the courseRegistration.
     *
     * @return void
     */
    public function duplicateRegistration()
    {
        $requiredFields = ['year', 'term', 'class_id'];
        $data = $this->validateData($this->post, $requiredFields);

        try {
            // Fetch existing registrations for the class.
            $oldRegistrations = $this->courseRegistration
                ->select(columns: ['year', 'course AS course_id', 'reg_no AS student_id'])
                ->where('class', '=', $data['class_id'])
                ->get();

            // Check if there are any existing registrations.
            if (empty($oldRegistrations)) {
                http_response_code(HttpStatus::NOT_FOUND);
                $this->response['message'] = 'No existing registrations found to duplicate.';
                ResponseHandler::sendJsonResponse($this->response);
            }

            // Ensure that the academic year remains the same.
            $oldYear = $oldRegistrations[0]['year'];
            $newYear = $data['year'];
            if ($oldYear !== $newYear) {
                http_response_code(HttpStatus::BAD_REQUEST);
                $this->response['message'] = "Cannot duplicate registrations: academic year mismatch (existing: $oldYear, new: $newYear).";
                ResponseHandler::sendJsonResponse($this->response);
            }

            // Remove duplicates if any.
            $uniqueStudentIds = array_unique(array_column($oldRegistrations, 'student_id'));
            $students = [];
            foreach ($uniqueStudentIds as $studentId) {
                $students[] = [
                    'student_id' => $studentId
                ];
            }

            $uniqueCourseIds = array_unique(array_column($oldRegistrations, 'course_id'));
            $courses = [];
            foreach ($uniqueCourseIds as $courseId) {
                $courses[] = [
                    'course_id' => $courseId
                ];
            }

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
                ? ['success' => true, 'message' => 'Registration copied successfully']
                : ['success' => false, 'message' => 'Failed to copy registration'];
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function unregisterCourses() {}
}
