<?php

/**
 * This file handles course registration logic
 *
 * PHP version 7.4+
 *
 * @category Controller
 * @package  LinkSkool
 * @author   ToochiDennis <dennistoochukwu@gmail.com>
 * @license  MIT License
 * @link     https://linkskool.example.com
 */

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
     * Initializes the controller by extracting POST/GET
     * data and connecting to the database.
     */
    public function initialize()
    {
        $this->courseRegistration = new CourseRegistration(pdo: $this->pdo);
        $this->student = new Student(pdo: $this->pdo);
        $this->registrationService = new CourseRegistrationService(pdo: $this->pdo);
    }

    /**
     * Handles course registration.
     * Fields are defined using dot notation to support validation of nested data.
     *
     * For example:
     * 'user.profile.name' will validate $data['user']['profile']['name'].
     */
    public function registerStudentCourses(array $vars)
    {
        $requiredFields = [
            'registered_courses',
            'registered_courses.*.course_id',
            'class_id',
            'term',
            'year',
            'student_id'
        ];

        $data = $this->validateData(
            data: $this->post + ['student_id' => $vars['id']],
            requiredFields: $requiredFields
        );

        try {
            $register = $this->registrationService->register(
                $data['student_id'],
                $data['registered_courses'],
                $data['term'],
                $data['year'],
                $data['class_id']
            );

            $this->response = $register ?
                ['success' => true, 'message' => 'Course(s) registered successfully']
                : ['success' => false, 'message' => 'Failed to register courses'];
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function registerClassCourses(array $vars)
    {
        $requiredFields = [
            'registered_courses',
            'registered_courses.*.course_id',
            'class_id',
            'term',
            'year'
        ];

        $data = $this->validateData(
            data: $this->post + ['class_id' => $vars['id']],
            requiredFields: $requiredFields
        );

        try {
            $students = $this->student
                ->select(['id AS student_id'])
                ->where('student_class', '=', $data['class_id'])
                ->get();

            if ($students) {
                $register = $this->registrationService->register(
                    $students,
                    $data['registered_courses'],
                    $data['term'],
                    $data['year'],
                    $data['class_id']
                );

                $this->response = $register ?
                    ['success' => true, 'message' => 'Course(s) registered successfully']
                    : ['success' => false, 'message' => 'Failed to register courses'];
            }
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getRegistrationTerms(array $params)
    {
        $requiredFields = ['class_id'];
        $data = $this->validateData($params, $requiredFields);

        try {
            $formatted = [];
            // Fetch existing registrations for the class.
            $registrations = $this->courseRegistration
                ->select(columns: ['year', 'term'])
                ->where('class', '=', $data['class_id'])
                ->groupBy(['term', 'year'])
                ->orderBy('year', 'DESC')
                ->get();

            foreach ($registrations as $registration) {
                $year = $registration['year'];
                $term = $registration['term'];

                if ($year === '0000') {
                    continue;
                }

                // Group by year
                if (!isset($formatted[$year])) {
                    $formatted[$year] = ['terms' => []];
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
    public function duplicateRegistration(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['id']);
        $classId = $data['id'];

        try {
            // Get the most recent registration (by year + term)
            $lastReg = $this->courseRegistration
                ->select(columns: ['year', 'term'])
                ->where('class', '=', $classId)
                ->orderBy(['year' => 'DESC', 'term' => 'DESC'])
                ->first();

            if (empty($lastReg)) {
                http_response_code(HttpStatus::NOT_FOUND);
                $this->response['message'] = 'No previous registrations found to duplicate.';
                return ResponseHandler::sendJsonResponse($this->response);
            }

            $oldYear = $lastReg['year'];
            $oldTerm = $lastReg['term'];

            // Calculate the new year and term
            $newTerm = $oldTerm < 3 ? $oldTerm + 1 : 1;
            $newYear = $oldTerm < 3 ? $oldYear : $oldYear + 1;

            // Fetch existing registrations for the class.
            $oldRegistrations = $this->courseRegistration
                ->select(columns: ['course AS course_id', 'reg_no AS student_id'])
                ->where('class', '=', $classId)
                ->where('year', '=', $oldYear)
                ->where('term', '=', $oldTerm)
                ->get();

            if (empty($oldRegistrations)) {
                http_response_code(HttpStatus::NOT_FOUND);
                $this->response['message'] = 'No existing registrations found to duplicate.';
                ResponseHandler::sendJsonResponse($this->response);
            }

            // Extract unique students and courses
            $students = array_map(
                fn($id) => ['student_id' => $id],
                array_unique(array_column($oldRegistrations, 'student_id'))
            );
            $courses  = array_map(
                fn($id) => ['course_id' => $id],
                array_unique(array_column($oldRegistrations, 'course_id'))
            );

            // Register for new term/year
            $register = $this->registrationService
                ->register(
                    $students,
                    $courses,
                    $newTerm,
                    $newYear,
                    $classId
                );

            // Set response based on registration success.
            $this->response = $register
                ? ['success' => true, 'message' => "Registration copied to Term $newTerm, $newYear"]
                : ['success' => false, 'message' => 'Failed to copy registration'];
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function unregisterCourses()
    {
    }
}
