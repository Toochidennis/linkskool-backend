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

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\Results\CourseRegistrationService;

#[Group('/portal')]
class CourseRegistrationController extends BaseController
{
    private CourseRegistrationService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new CourseRegistrationService(pdo: $this->pdo);
    }

    #[Route(
        '/students/{student_id:\d+}/course-registrations',
        'POST',
        ['auth', 'role:admin']
    )]
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
    }

    #[Route(
        '/classes/{class_id:\d+}/course-registrations',
        'POST',
        ['auth', 'role:admin']
    )]
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
    }

    #[Route(
        '/classes/{class_id:\d+}/course-registrations/duplicate',
        'POST',
        ['auth', 'role:admin']
    )]
    public function duplicateLastTermRegistrations(array $vars)
    {
        $data = $this->validate(data: $vars, rules: ['class_id' => 'required|integer']);

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
    }

    #[Route(
        '/course-registrations/terms',
        'GET',
        ['auth', 'role:admin']
    )]
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

        $sessions = $this->service->getClassRegistrationTerms($data['class_id']);
        $performance = $this->service->getClassRegisteredCoursesWithAverageScores($data);
        $this->respond([
            'success' => true,
            'response' => ['sessions' => $sessions, 'chart_data' => $performance]
        ]);
    }

    #[Route(
        '/classes/{class_id:\d+}/course-registrations/history',
        'GET',
        ['auth', 'role:admin']
    )]
    public function getClassRegistrationHistory(array $vars)
    {
        $data = $this->validate(data: $vars, rules: ['class_id' => 'required|integer']);

        $this->respond([
            'success' => true,
            'data' => $this->service->getClassRegistrationHistory((int) $data['class_id'])
        ]);
    }

    #[Route(
        '/classes/{class_id:\d+}/registered-courses',
        'GET',
        ['auth', 'role:admin']
    )]
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

        $courses = $this->service->getClassRegisteredCourses($data);
        $this->respond(['success' => true, 'data' => $courses]);
    }

    #[Route(
        '/classes/{class_id:\d+}/course-registrations/average-scores',
        'GET',
        ['auth', 'role:admin']
    )]
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

        $result = $this->service->getClassRegisteredCoursesWithAverageScores($data);
        $this->respond(['success' => true, 'data' => $result]);
    }

    #[Route(
        '/courses/{course_id:\d+}/students',
        'GET',
        ['auth', 'role:admin']
    )]
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

        $students = $this->service->getStudentsForCourseInClass($data);
        $this->respond(['success' => true, 'data' => $students]);
    }

    #[Route(
        '/students/{student_id:\d+}/registered-courses',
        'GET',
        ['auth', 'role:admin']
    )]
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

        $this->respond([
            'success' => true,
            'data' =>  $this->service->getCoursesRegisteredByStudent($data)
        ]);
    }

    #[Route(
        '/classes/{class_id:\d+}/registered-students',
        'GET',
        ['auth', 'role:admin']
    )]
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
        $result = $this->service->getStudentsRegistrationStatus($data);
        $this->respond(['success' => true, 'registered_students' => $result]);
    }
}
