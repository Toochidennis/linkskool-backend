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
        $this->service = new CourseRegistrationService(pdo: $this->pdo);
    }


    public function registerStudentCourses(array $vars)
    {
        $data = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'registered_courses' => 'required|array|min:1',
                'registered_courses.*.course_id' => 'required|integer',
                'class_id' => 'required|integer',
                'term' => 'required|integer',
                'year' => 'required|integer',
                'student_id' => 'required|integer'
            ]
        );

        try {
            $register = $this->service->registerCourses($data);

            if ($register) {
                return $this->respond(
                    [
                        'success' => true,
                        'message' => 'Course(s) registered successfully'
                    ],
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
        $data = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'registered_courses' => 'required|array|min:1',
                'registered_courses.*.course_id' => 'required|integer',
                'class_id' => 'required|integer',
                'term' => 'required|integer',
                'year' => 'required|integer',
            ]
        );

        try {
            $register = $this->service->registerClassCourses($data);

            if ($register) {
                return $this->respond(
                    [
                        'success' => true,
                        'message' => 'Course(s) registered successfully'
                    ],
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


    public function duplicateLastTermRegistrations(array $vars)
    {
        $data = $this->validate(data: $vars, rules: ['class_id' => 'required|integer']);

        try {
            $result = $this->service
                ->duplicateRegistrationForNextTerm($data['class_id']);

            if (!$result) {
                return $this->respondError(
                    message: 'No courses were registered. Possibly already registered or invalid input.',
                    statusCode: HttpStatus::BAD_REQUEST
                );
            }

            return $this->respond(
                data: [
                    'success' => true,
                    'message' => 'Course(s) duplicated successfully.'
                ],
                statusCode: HttpStatus::CREATED
            );
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getClassRegistrationTerms(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'class_id' => 'required|integer',
                'term' => 'required|integer',
                'year' => 'required|integer'
            ]
        );

        try {
            $sessions = $this->service->getClassRegistrationTerms($data['class_id']);
            $performance = $this->service->getClassRegisteredCoursesWithAverageScores($data);
            $this->respond([
                'success' => true,
                'response' => ['sessions' => $sessions, 'chart_data' => $performance]
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getClassRegistrationHistory(array $vars)
    {
        $data = $this->validate(data: $vars, rules: ['class_id' => 'required|integer']);

        try {
            $history = $this->service->getClassRegistrationHistory((int) $data['class_id']);
            $this->respond(['success' => true, 'data' => $history]);
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function getRegisteredCoursesForClass(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'class_id' => 'required|integer',
                'year' => 'required|integer',
                'term' => 'required|integer'
            ]
        );

        try {
            $courses = $this->service->getClassRegisteredCourses($data);
            $this->respond(['success' => true, 'data' => $courses]);
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function getRegisteredCoursesWithAvgScores(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'class_id' => 'required|integer',
                'year' => 'required|integer',
                'term' => 'required|integer'
            ]
        );

        try {
            $result = $this->service->getClassRegisteredCoursesWithAverageScores($data);
            $this->respond(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function getStudentsForCourseInClass(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'class_id' => 'required|integer',
                'course_id' => 'required|integer',
                'year' => 'required|integer',
                'term' => 'required|integer'
            ]
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
        $data = $this->validate(
            data: $vars,
            rules: [
                'class_id' => 'required|integer',
                'student_id' => 'required|integer',
                'year' => 'required|integer',
                'term' => 'required|integer'
            ]
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
        $data = $this->validate(
            data: $vars,
            rules: [
                'class_id' => 'required|integer',
                'year' => 'required|integer',
                'term' => 'required|integer'
            ]
        );

        try {
            $result = $this->service->getStudentsRegistrationStatus($data);
            $this->respond(['success' => true, 'registered_students' => $result]);
        } catch (Exception $e) {
            $this->respondError($e->getMessage());
        }
    }
}
