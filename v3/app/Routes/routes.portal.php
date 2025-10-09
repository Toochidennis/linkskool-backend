<?php

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Common\Utilities\ResponseHandler;
use V3\App\Common\Routing\CustomRouteCollector;
use V3\App\Controllers\Portal\Academics\AttendanceController;

return function (CustomRouteCollector $r) {

    $r->addGroup('/portal', function (CustomRouteCollector $r) {

        $r->get('/status', function () {
            ResponseHandler::sendJsonResponse([
                'data' => ['message' => 'Portal is up and running'],
                'status' => HttpStatus::OK
            ]);
        });

        $r->addGroup('/students', function (CustomRouteCollector $r) {
            $r->get('', ['Portal\Academics\StudentController', 'getAllStudents']);
            $r->post('', ['Portal\Academics\StudentController', 'addStudent']);
            $r->put('/{id:\d+}', ['Portal\Academics\StudentController', 'updateStudentRecord']);
            $r->get('/{id:\d+}', ['Portal\Academics\StudentController', 'getStudentById']);
            $r->delete('/{id:\d+}', ['Portal\Academics\StudentController', 'deleteStudent']);
        });

        $r->addGroup('/classes', function (CustomRouteCollector $r) {
            $r->post('', ['Portal\Academics\ClassController', 'addClass']);
            $r->post('/{class_id:\d+}/attendance', function (array $vars) {
                $vars['type'] = 'class';
                return (new AttendanceController())->addAttendance($vars);
            });
            $r->get('', ['Portal\Academics\ClassController', 'getClasses']);
        });

        $r->addGroup('/courses', function (CustomRouteCollector $r) {
            $r->post('', ['Portal\Academics\CourseController', 'addCourse']);
            $r->post('/{course_id:\d+}/attendance', function (array $vars) {
                $vars['type'] = 'course';
                return (new AttendanceController())->addAttendance($vars);
            });
            $r->get('', ['Portal\Academics\CourseController', 'getCourses']);
            $r->delete('/{id:\d+}', ['Portal\Academics\CourseController', 'deleteCourse']);
        });

        $r->addGroup('/payments', function (CustomRouteCollector $r) {
            $r->post('/accounts', ['Portal\Payments\AccountController', 'store']);
            $r->get('/accounts', ['Portal\Payments\AccountController', 'get']);
        });
    });
};
