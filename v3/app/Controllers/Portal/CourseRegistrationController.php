<?php

/**
 * This file handles course registration logic
 *
 * PHP version 8.2+
 *
 * @category Controller
 * @package  LinkSkool
 * @author   ToochiDennis <dennistoochukwu@gmail.com>
 * @license  MIT
 * @link     https://linkskool.net
 */

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Utilities\HttpStatus;
use V3\App\Models\Portal\Student;
use V3\App\Traits\ValidationTrait;
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

            if ($register) {
                return $this->respond(
                    ['success' => true, 'message' => 'Course(s) registered successfully'],
                    HttpStatus::CREATED
                );
            }

            return $this->respondError(
                'No courses were registered. Possibly already registered or invalid input.',
                HttpStatus::BAD_REQUEST
            );
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
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
            $register = $this->registrationService->registerClassCourses($data);

            if ($register) {
                return $this->respond(
                    ['success' => true, 'message' => 'Course(s) registered successfully'],
                    HttpStatus::CREATED
                );
            }

            return $this->respondError(
                'No courses were registered. Possibly already registered or invalid input.',
                HttpStatus::BAD_REQUEST
            );
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getRegistrationTerms(array $params)
    {
        $data = $this->validateData($params, ['class_id']);

        try {
            $sessions = $this->registrationService->getRegistrationTerms($data['class_id']);
            $this->respond(['success' => true, 'sessions' => $sessions]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
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

        try {
            $result = $this->registrationService
                ->duplicateRegistrationForNextTerm($data['id']);

            if (!$result) {
                return $this->respondError(
                    message: 'No courses were registered. Possibly already registered or invalid input.',
                    statusCode: HttpStatus::BAD_REQUEST
                );
            }

            return $this->respond(
                data: [
                    'success' => true,
                    'message' => 'Course(s) registered successfully.'
                ],
                statusCode: HttpStatus::CREATED
            );
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function unregisterCourses()
    {
    }
}
