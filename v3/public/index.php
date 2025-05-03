<?php

header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/../vendor/autoload.php';

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use V3\App\Utilities\HttpStatus;
use V3\App\Utilities\ResponseHandler;

$response = ['success' => false];

$dispatcher = FastRoute\simpleDispatcher(
    function (RouteCollector $r) {
        /**
      * Portal routes
    */
        // Login routes
        $r->addRoute('POST', '/portal/auth/login', ['Portal\AuthController', 'handleAuthRequest']);
        $r->addRoute('POST', '/portal/auth/logout', ['Portal\AuthController', 'logout']);
        // Student routes
        $r->addRoute('POST', '/portal/students', ['Portal\StudentController', 'addStudent']);
        $r->addRoute('POST', '/portal/students/{id:\d+}/course-registrations', ['Portal\CourseRegistrationController', 'registerStudentCourses']);
        $r->addRoute('GET', '/portal/students/{id:\d+}/registered-courses', ['Portal\CourseController', 'getStudentRegisteredCourses']);
        $r->addRoute('GET', '/portal/students', ['Portal\StudentController', 'getAllStudents']);
        $r->addRoute('GET', '/portal/students/{id}/result-terms', ['Portal\ResultController', 'getResultTermsByStudent']);
        $r->addRoute('GET', '/portal/students/{id:\d+}', ['Portal\StudentController', 'getStudentById']);

        // Class routes
        $r->addRoute('POST', '/portal/classes/{id:\d+}/attendance', ['Portal\AttendanceController', 'addClassAttendance']);
        $r->addRoute('POST', '/portal/classes/{id:\d+}/course-registrations', ['Portal\CourseRegistrationController', 'registerClassCourses']);
        $r->addRoute('POST', '/portal/classes/{id:\d+}/course-registrations/duplicate', ['Portal\CourseRegistrationController', 'duplicateRegistration']);
        $r->addRoute('GET', '/portal/classes/{id:\d+}/attendance', ['Portal\AttendanceController', 'getClassAttendance']);
        $r->addRoute('GET', '/portal/classes/{id:\d+}/students', ['Portal\StudentController', 'getStudentsByClass']);
        $r->addRoute('GET', '/portal/classes/{id:\d+}/registered-students', ['Portal\StudentController', 'getClassRegisteredStudents']);
        $r->addRoute('GET', '/portal/classes/{id:\d+}/registered-courses', ['Portal\CourseController', 'getClassRegisteredCourses']);

        // Staff routes
        $r->addRoute('POST', '/portal/staff', ['Portal\StaffController', 'addStaff']);
        $r->addRoute('GET', '/portal/staff', ['Portal\StaffController', 'getStaff']);
        $r->addRoute('GET', '/portal/staff/{id}', ['Portal\StaffController', 'getStaffById']);

        // course routes
        $r->addRoute('POST', '/portal/courses/{id}/attendance', ['Portal\AttendanceController', 'addCourseAttendance']);
        $r->addRoute('GET', '/portal/course-registrations ', ['Portal\CourseRegistrationController', 'getAllCourseRegistrations']);
        $r->addRoute('GET', '/portal/course-registrations/terms', ['Portal\CourseRegistrationController', 'getRegistrationTerms']);
        $r->addRoute('GET', '/portal/courses/{id}/attendance', ['Portal\AttendanceController', 'getCourseAttendance']);

        $r->addRoute('POST', '/portal/assessments', ['Portal\AssessmentController', 'addAssessment']);
        $r->addRoute('GET', '/portal/assessments', ['Portal\AssessmentController', 'getAllAssessments']);
        $r->addRoute('GET', '/portal/assessments/{id:\d+}', ['Portal\AssessmentController', 'getAssessmentById']);

        $r->addRoute('POST', '/portal/grades', ['Portal\GradeController', 'addGrade']);
        $r->addRoute('GET', '/portal/grades', ['Portal\GradeController', 'fetchGrades']);

        $r->addRoute('PUT', '/portal/attendance/{id:\d+}', ['Portal\AttendanceController', 'updateAttendance']);
        $r->addRoute('GET', '/portal/attendance', ['Portal\AttendanceController', 'getAllAttendance']);
        $r->addRoute('GET', '/portal/attendance/{id:\d+}', ['Portal\AttendanceController', 'getAttendanceById']);

        $r->addRoute('GET', '/portal/course-assignments', ['Portal\CourseAssignmentController', 'getAssignments']);
    }
);

// Fetch method and URI
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$queryString = '';

// Strip query string (?foo=bar) from URI
if (false !== $pos = strpos($uri, '?')) {
    $queryString  = substr($uri, $pos + 1);
    $uri  = substr($uri, 0, $pos);
}

// parse the query string into an associative array.
$queryParams = [];
if (!empty($queryString)) {
    parse_str($queryString, $queryParams);
}

$uri = rawurldecode($uri);
$uri = str_replace('/api3/v3', '', $uri);

// Dispatch the route
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        $response['message'] = 'Route not found.';
        http_response_code(HttpStatus::NOT_FOUND);
        ResponseHandler::sendJsonResponse($response);
        break;

    case Dispatcher::METHOD_NOT_ALLOWED:
        $response['message'] = 'Method not allowed.';
        http_response_code(HttpStatus::METHOD_NOT_ALLOWED);
        ResponseHandler::sendJsonResponse($response);
        break;

    case Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        $vars = array_merge($vars, $queryParams);

        // Split the handler (e.g [AuthController, handleAuthRequest])
        [$class, $method] = $handler;

        // Instantiate and call the method
        $controller = "V3\App\Controllers\\$class";

        if (class_exists($controller) && method_exists($controller, $method)) {
            (new $controller())->$method($vars);
        } else {
            $response['message'] = 'Invalid handler';
            http_response_code(HttpStatus::SERVICE_UNAVAILABLE);
            ResponseHandler::sendJsonResponse($response);
        }
        break;
}
