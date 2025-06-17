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

namespace V3\App\Controllers\Portal\Results;

use Exception;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Results\CourseRegistrationService;

class CourseRegistrationController extends BaseController
{
    private CourseRegistrationService $service;

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
        $this->service = new CourseRegistrationService(pdo: $this->pdo);
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
            $register = $this->service->registerCourses(
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
            $register = $this->service->registerClassCourses($data);

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

    public function getClassRegistrationTerms(array $vars)
    {
        $data = $this->validateData($vars, ['class_id']);

        try {
            $sessions = $this->service->getClassRegistrationTerms($data['class_id']);
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
    public function duplicateLastTermRegistrations(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['id']);

        try {
            $result = $this->service
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

    public function getClassRegistrationHistory(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['class_id']);

        try {
            $history = $this->service->getClassRegistrationHistory((int) $data['class_id']);
            $this->respond(['success' => true, 'data' => $history]);
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function getRegisteredCoursesForClass(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['class_id', 'year', 'term']);
        try {
            $courses = $this->service->getClassRegisteredCourses($data);
            $this->respond(['success' => true, 'data' => $courses]);
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function getRegisteredCoursesWithAvgScores(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['year', 'term', 'class_id']);
        try {
            $result = $this->service->getClassRegisteredCoursesWithAverageScores($data);
            $this->respond(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function getStudentsForCourseInClass(array $vars)
    {
        $data = $this->validateData(
            data: $vars,
            requiredFields: ['class_id', 'course_id', 'year', 'term']
        );

        try {
            $students = $this->service->getStudentsForCourseInClass($data);
            $this->respond(['success' => true, 'data' => $students]);
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function getCoursesRegisteredByStudent(array $vars)
    {
        $data = $this->validateData(
            data: $vars,
            requiredFields: ['class_id', 'student_id', 'year', 'term']
        );
        try {
            $courses = $this->service->getCoursesRegisteredByStudent($data);
            $this->respond(['success' => true, 'data' => $courses]);
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function getStudentRegistrationStatusInClass(array $vars)
    {
        $data = $this->validateData($vars, ['class_id', 'term', 'year']);
        try {
            $result = $this->service->getStudentsRegistrationStatus($data);
            $this->respond(['success' => true, 'registered_students' => $result]);
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function unregisterCourses()
    {
        // TODO
    }
}
