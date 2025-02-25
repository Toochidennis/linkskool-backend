<?php
require_once __DIR__ . '/../vendor/autoload.php';

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use V3\App\Utilities\ResponseHandler;

$response = ['success' => false, 'message' => ''];

$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
    /**  Portal routes  */
    // Login routes
    $r->addRoute('POST', '/portal/auth/login', ['Portal\AuthController', 'handleAuthRequest']);
    $r->addRoute('POST', '/portal/auth/logout', ['Portal\AuthController', 'logout']);
    // Student routes
    $r->addRoute('POST', '/portal/students', ['Portal\StudentController', 'addStudent']);
    $r->addRoute('GET', '/portal/students', ['Portal\StudentController', 'getStudents']);
    $r->addRoute('GET', '/portal/students/{id}', ['Portal\StudentController', 'getStudentById']);
    // Staff routes
    $r->addRoute('POST', '/portal/staff', ['Portal\StaffController', 'addStaff']);
    $r->addRoute('GET', '/portal/staff', ['Portal\StaffController', 'getStaff']);
    $r->addRoute('GET', '/portal/staff/{id}', ['Portal\StaffController', 'getStaffById']);

    $r->addRoute('POST', '/portal/courses/registrations', ['Portal\CourseRegistrationController', 'registerCourses']);
    $r->addRoute('GET', '/portal/courses/registrations', ['Portal\CourseRegistrationController', 'fetchRegisteredCourses']);
    $r->addRoute('GET', '/portal/courses/registrations/terms', ['Portal\CourseRegistrationController', 'getRegistrationTerms']);
    $r->addRoute('POST', '/portal/courses/registrations/duplicate', ['Portal\CourseRegistrationController', 'duplicateRegistration']);

    $r->addRoute('POST', '/portal/assessments', ['Portal\AssessmentController', 'addAssessment']);
    $r->addRoute('GET', '/portal/assessments', ['Portal\AssessmentController', 'fetchAssessments']);
    $r->addRoute('GET', '/portal/assessments/{level}', ['Portal\AssessmentController', 'fetchAssessmentById']);

    $r->addRoute('POST', '/portal/grades', ['Portal\GradeController', 'addGrade']);
    $r->addRoute('GET', '/portal/grades', ['Portal\GradeController', 'fetchGrades']);

    $r->addRoute('POST', '/portal/attendance', ['Portal\AttendanceController', 'addAttendance']);
    $r->addRoute('GET', '/portal/attendance', ['Portal\AttendanceController', 'getAttendance']);
    $r->addRoute('GET', '/portal/attendance/summary', ['Portal\AttendanceController', 'getAttendanceSummary']);
});

// Fetch method and URI
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$queryString = '';

// Strip query string (?foo=bar) from URI
if (false !== $pos = strpos($uri, '?')) {
    $queryString  = substr($uri,  $pos + 1);
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
        http_response_code(404);
        $response['message'] = 'Route not found.';
        ResponseHandler::sendJsonResponse($response);
        break;

    case Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        $response['message'] = 'Method not allowed.';
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
            http_response_code(500);
            $response['message'] = 'Invalid handler';
            ResponseHandler::sendJsonResponse($response);
        }
        break;
}
