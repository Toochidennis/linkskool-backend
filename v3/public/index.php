<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use V3\App\Common\Utilities\Logger;
use V3\App\Common\Utilities\EnvLoader;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Common\Utilities\ResponseHandler;

// Load environment variables if required
EnvLoader::load();

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
        $r->addRoute('POST', '/portal/students/result/comment', ['Portal\Results\ResultCommentController', 'store']);
        $r->addRoute('POST', '/portal/students/{id:\d+}/course-registrations', ['Portal\Results\CourseRegistrationController', 'registerStudentCourses']);
        $r->addRoute('GET', '/portal/students/{student_id:\d+}/registered-courses', ['Portal\Results\CourseRegistrationController', 'getCoursesRegisteredByStudent']);
        $r->addRoute('GET', '/portal/students', ['Portal\StudentController', 'getAllStudents']);
        $r->addRoute('GET', '/portal/students/{id:\d+}/result-terms', ['Portal\Results\StudentResultController', 'getResultTerms']);
        $r->addRoute('GET', '/portal/students/{student_id:\d+}/result', ['Portal\Results\StudentResultController', 'getStudentResult']);
        $r->addRoute('GET', '/portal/students/{id:\d+}', ['Portal\Academics\StudentController', 'getStudentById']);

        // Class routes
        $r->addRoute('POST', '/portal/classes/{id:\d+}/attendance', ['Portal\Academics\AttendanceController', 'addClassAttendance']);
        $r->addRoute('POST', '/portal/classes/{id:\d+}/course-registrations', ['Portal\Results\CourseRegistrationController', 'registerClassCourses']);
        $r->addRoute('POST', '/portal/classes/{id:\d+}/course-registrations/duplicate', ['Portal\Results\CourseRegistrationController', 'duplicateLastTermRegistrations']);
        $r->addRoute('GET', '/portal/classes/{class_id:\d+}/course-registrations/history', ['Portal\Results\CourseRegistrationController', 'getClassRegistrationHistory']);
        $r->addRoute('GET', '/portal/classes/{id:\d+}/attendance', ['Portal\Academics\AttendanceController', 'getClassAttendance']);
        $r->addRoute('GET', '/portal/classes/{id:\d+}/students', ['Portal\Academics\StudentController', 'getStudentsByClass']);
        $r->addRoute('GET', '/portal/classes/{class_id:\d+}/registered-students', ['Portal\Results\CourseRegistrationController', 'getStudentRegistrationStatusInClass']);
        $r->addRoute('GET', '/portal/classes/{class_id:\d+}/registered-courses', ['Portal\Results\CourseRegistrationController', 'getRegisteredCoursesForClass']);
        $r->addRoute('GET', '/portal/classes/{class_id:\d+}/course-registrations/average-scores', ['Portal\Results\CourseRegistrationController', 'getRegisteredCoursesWithAvgScores']);
        $r->addRoute('GET', '/portal/classes/{class_id}/courses/{course_id}/results', ['Portal\Results\ClassCourseResultController', 'getCourseResultsForClass']);
        $r->addRoute('GET', '/portal/classes/{class_id}/students-result', ['Portal\Results\ClassCourseResultController', 'getStudentsResultForClass']);
        $r->addRoute('GET', '/portal/classes/{class_id}/composite-result', ['Portal\Results\ClassCourseResultController', 'getClassCompositeResult']);

        // Staff routes
        $r->addRoute('POST', '/portal/staff', ['Portal\Academics\StaffController', 'addStaff']);
        $r->addRoute('GET', '/portal/staff', ['Portal\Academics\StaffController', 'getStaff']);
        $r->addRoute('GET', '/portal/staff/{id}', ['Portal\Academics\StaffController', 'getStaffById']);

        // course routes
        $r->addRoute('POST', '/portal/courses/{id}/attendance', ['Portal\Academics\AttendanceController', 'addCourseAttendance']);
        $r->addRoute('GET', '/portal/course-registrations/terms', ['Portal\Results\CourseRegistrationController', 'getClassRegistrationTerms']);
        $r->addRoute('GET', '/portal/courses/{course_id:\d+}/students', ['Portal\Results\CourseRegistrationController', 'getStudentsForCourseInClass']);
        $r->addRoute('GET', '/portal/courses/{id}/attendance', ['Portal\Academics\AttendanceController', 'getCourseAttendance']);

        $r->addRoute('POST', '/portal/assessments', ['Portal\Results\AssessmentController', 'addAssessments']);
        $r->addRoute('PUT', '/portal/assessments/{id:\d+}', ['Portal\Results\AssessmentController', 'updateAssessment']);
        $r->addRoute('GET', '/portal/assessments', ['Portal\Results\AssessmentController', 'getAllAssessments']);
        $r->addRoute('GET', '/portal/assessments/{level_id:\d+}', ['Portal\Results\AssessmentController', 'getAssessmentByLevel']);
        $r->addRoute('DELETE', '/portal/assessments/{id:\d+}', ['Portal\Results\AssessmentController', 'deleteAssessment']);

        $r->addRoute('POST', '/portal/grades', ['Portal\Results\GradeController', 'addGrades']);
        $r->addRoute('PUT', '/portal/grades/{id:\d+}', ['Portal\Results\GradeController', 'updateGrade']);
        $r->addRoute('GET', '/portal/grades', ['Portal\Results\GradeController', 'getGrades']);
        $r->addRoute('DELETE', '/portal/grades/{id:\d+}', ['Portal\Results\GradeController', 'deleteGrade']);

        $r->addRoute('PUT', '/portal/attendance/{id:\d+}', ['Portal\Academics\AttendanceController', 'updateAttendance']);
        $r->addRoute('GET', '/portal/attendance/history', ['Portal\Academics\AttendanceController', 'getAttendanceHistory']);
        $r->addRoute('GET', '/portal/attendance/{id:\d+}', ['Portal\Academics\AttendanceController', 'getAttendanceById']);

        $r->addRoute('GET', '/portal/course-assignments', ['Portal\Academics\CourseAssignmentController', 'getCourseAssignments']);

        $r->addRoute('PUT', '/portal/result/class-result', ['Portal\Results\ResultController', 'updateResult']);

        // Movies
        $r->addRoute('GET', '/public/movies/all', ['Explore\MovieController', 'getSampleFromAllCategories']);
        $r->addRoute('GET', '/public/movies/category/{cat}', ['Explore\MovieController', 'getByCategory']);
        $r->addRoute('GET', '/public/movies/genres', ['Explore\MovieController', 'getAllGenres']);
        $r->addRoute('GET', '/public/movies/genre/{id}', ['Explore\MovieController', 'getByGenre']);

        // New endpoints
        $r->addRoute('POST', '/portal/syllabus', ['Portal\ELearning\SyllabusController', 'createSyllabus']);
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
