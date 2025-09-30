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
use V3\App\Common\Utilities\EnvLoader;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Common\Utilities\ResponseHandler;
use V3\App\Common\Utilities\BirthdayMessenger;

// Load environment variables
EnvLoader::load();
// BirthdayMessenger::send();
// die;

$response = ['success' => false];

$dispatcher = FastRoute\simpleDispatcher(
    function (RouteCollector $r) {
        /**
         * Portal routes
         */

        // Login routes
        $r->addRoute(
            'POST',
            '/portal/auth/login',
            ['Portal\AuthController', 'handleAuthRequest']
        );
        $r->addRoute(
            'POST',
            '/portal/auth/logout',
            ['Portal\AuthController', 'logout']
        );

        // dashboard routes
        $r->addRoute(
            'GET',
            '/portal/dashboard/admin',
            ['Portal\Academics\AcademicOverviewController', 'adminOverview']
        );
        $r->addRoute(
            'GET',
            '/portal/dashboard/staff/{teacher_id:\d+}',
            ['Portal\Academics\AcademicOverviewController', 'teacherOverview']
        );
        $r->addRoute(
            'GET',
            '/portal/dashboard/student',
            ['Portal\Academics\AcademicOverviewController', 'studentOverview']
        );
        // Feeds
        $r->addRoute(
            'POST',
            '/portal/feeds',
            ['Portal\Academics\FeedController', 'addContent']
        );
        $r->addRoute(
            'PUT',
            '/portal/feeds/{news_id:\d+}',
            ['Portal\Academics\FeedController', 'updateContent']
        );
        $r->addRoute(
            'GET',
            '/portal/feeds',
            ['Portal\Academics\FeedController', 'getContents']
        );
        $r->addRoute(
            'GET',
            '/portal/feeds/{news_id:\d+}',
            ['Portal\Academics\FeedController', 'getContentById']
        );
        $r->addRoute(
            'DELETE',
            '/portal/feeds/{news_id:\d+}',
            ['Portal\Academics\FeedController', 'deleteContent']
        );

        // Student routes
        $r->addRoute(
            'POST',
            '/portal/students',
            ['Portal\Academics\StudentController', 'addStudent']
        );
        $r->addRoute(
            'POST',
            '/portal/students/result/comment',
            ['Portal\Results\ResultCommentController', 'store']
        );
        $r->addRoute(
            'POST',
            '/portal/students/skill-behavior',
            ['Portal\Results\StudentSkillBehaviorController', 'store']
        );
        $r->addRoute(
            'POST',
            '/portal/students/{student_id:\d+}/course-registrations',
            ['Portal\Results\CourseRegistrationController', 'registerStudentCourses']
        );
        $r->addRoute(
            'POST',
            '/portal/students/{student_id:\d+}/make-payment',
            ['Portal\Payments\StudentPaymentController', 'makePayment']
        );
        $r->addRoute(
            'POST',
            '/portal/students/{student_id:\d+}/quiz-submissions',
            ['Portal\ELearning\QuizSubmissionController', 'submit']
        );
        $r->addRoute(
            'POST',
            '/portal/students/{student_id:\d+}/assignment-submissions',
            ['Portal\ELearning\AssignmentSubmissionController', 'submit']
        );
        $r->addRoute(
            'PUT',
            '/portal/students/{id}',
            ['Portal\Academics\StudentController', 'updateStudentRecord']
        );
        $r->addRoute(
            'GET',
            '/portal/students/{student_id:\d+}/registered-courses',
            ['Portal\Results\CourseRegistrationController', 'getCoursesRegisteredByStudent']
        );
        $r->addRoute(
            'GET',
            '/portal/students',
            ['Portal\Academics\StudentController', 'getAllStudents']
        );
        $r->addRoute(
            'GET',
            '/portal/students/{id:\d+}/elearning/dashboard',
            ['Portal\ELearning\StudentContentManagerController', 'dashboard']
        );
        $r->addRoute(
            'GET',
            '/portal/students/{id:\d+}/result-terms',
            ['Portal\Results\StudentResultController', 'getResultTerms']
        );
        $r->addRoute(
            'GET',
            '/portal/students/{student_id:\d+}/result/{term:\d+}',
            ['Portal\Results\StudentResultController', 'getStudentTermResult']
        );
        $r->addRoute(
            'GET',
            '/portal/students/{student_id:\d+}/result/annual',
            ['Portal\Results\StudentResultController', 'getStudentAnnualResult']
        );
        $r->addRoute(
            'GET',
            '/portal/students/{id:\d+}',
            ['Portal\Academics\StudentController', 'getStudentById']
        );
        $r->addRoute(
            'GET',
            '/portal/students/{student_id:\d+}/financial-records',
            ['Portal\Payments\StudentPaymentController', 'getFinancialRecords']
        );
        $r->addRoute(
            'GET',
            '/portal/students/{student_id:\d+}/assignment-submissions',
            ['Portal\ELearning\AssignmentSubmissionController', 'getMarkedAssignment']
        );
        $r->addRoute(
            'GET',
            '/portal/students/{student_id:\d+}/quiz-submissions',
            ['Portal\ELearning\QuizSubmissionController', 'getMarkedQuiz']
        );
        $r->addRoute(
            'DELETE',
            '/portal/students/{id:\d+}',
            ['Portal\Academics\StudentController', 'deleteStudent']
        );

        // Level routes
        $r->addRoute(
            'POST',
            '/portal/levels',
            ['Portal\Academics\LevelController', 'addLevel']
        );
        $r->addRoute(
            'PUT',
            '/portal/levels/{id:\d+}',
            ['Portal\Academics\LevelController', 'updateLevel']
        );
        $r->addRoute(
            'GET',
            '/portal/levels/result/performance',
            ['Portal\Results\ClassCourseResultController', 'getAllLevelsPerformance']
        );
        $r->addRoute(
            'GET',
            '/portal/levels',
            ['Portal\Academics\LevelController', 'getLevels']
        );
        $r->addRoute(
            'DELETE',
            '/portal/levels/{id:\d+}',
            ['Portal\Academics\LevelController', 'deleteLevel']
        );

        // Class routes
        $r->addRoute(
            'POST',
            '/portal/classes',
            ['Portal\Academics\ClassController', 'addClass']
        );
        $r->addRoute(
            'POST',
            '/portal/classes/{class_id:\d+}/attendance',
            ['Portal\Academics\AttendanceController', 'addClassAttendance']
        );
        $r->addRoute(
            'POST',
            '/portal/classes/{class_id:\d+}/course-registrations',
            ['Portal\Results\CourseRegistrationController', 'registerClassCourses']
        );
        $r->addRoute(
            'POST',
            '/portal/classes/{class_id:\d+}/course-registrations/duplicate',
            ['Portal\Results\CourseRegistrationController', 'duplicateLastTermRegistrations']
        );
        $r->addRoute(
            'PUT',
            '/portal/classes/{id:\d+}',
            ['Portal\Academics\ClassController', 'updateClass']
        );
        $r->addRoute(
            'GET',
            '/portal/classes',
            ['Portal\Academics\ClassController', 'getClasses']
        );
        $r->addRoute(
            'GET',
            '/portal/classes/{class_id:\d+}/course-registrations/history',
            ['Portal\Results\CourseRegistrationController', 'getClassRegistrationHistory']
        );
        $r->addRoute(
            'GET',
            '/portal/classes/{class_id:\d+}/attendance/single',
            ['Portal\Academics\AttendanceController', 'getSingleClassAttendance']
        );
        $r->addRoute(
            'GET',
            '/portal/classes/{class_id:\d+}/attendance',
            ['Portal\Academics\AttendanceController', 'getAllClassAttendance']
        );
        $r->addRoute(
            'GET',
            '/portal/classes/{class_id:\d+}/students',
            ['Portal\Academics\StudentController', 'getStudentsByClass']
        );
        $r->addRoute(
            'GET',
            '/portal/classes/{class_id:\d+}/registered-students',
            ['Portal\Results\CourseRegistrationController', 'getStudentRegistrationStatusInClass']
        );
        $r->addRoute(
            'GET',
            '/portal/classes/{class_id:\d+}/registered-courses',
            ['Portal\Results\CourseRegistrationController', 'getRegisteredCoursesForClass']
        );
        $r->addRoute(
            'GET',
            '/portal/classes/{class_id:\d+}/course-registrations/average-scores',
            ['Portal\Results\CourseRegistrationController', 'getRegisteredCoursesWithAvgScores']
        );
        $r->addRoute(
            'GET',
            '/portal/classes/{class_id:\d+}/courses/{course_id}/results',
            ['Portal\Results\ClassCourseResultController', 'getCourseResultsForClass']
        );
        $r->addRoute(
            'GET',
            '/portal/classes/{class_id:\d+}/students-result',
            ['Portal\Results\ClassCourseResultController', 'getStudentsResultForClass']
        );
        $r->addRoute(
            'GET',
            '/portal/classes/{class_id:\d+}/composite-result',
            ['Portal\Results\ClassCourseResultController', 'getClassCompositeResult']
        );
        $r->addRoute(
            'GET',
            '/portal/classes/{class_id:\d+}/skill-behavior',
            ['Portal\Results\StudentSkillBehaviorController', 'getStudentsSkillBehavior']
        );
        $r->addRoute(
            'DELETE',
            '/portal/classes/{id:\d+}',
            ['Portal\Academics\ClassController', 'deleteClass']
        );

        // Staff routes
        $r->addRoute(
            'POST',
            '/portal/staff',
            ['Portal\Academics\StaffController', 'addStaff']
        );
        $r->addRoute(
            'PUT',
            '/portal/staff/{id:\d+}',
            ['Portal\Academics\StaffController', 'updateStaff']
        );
        $r->addRoute(
            'GET',
            '/portal/staff',
            ['Portal\Academics\StaffController', 'getStaff']
        );
        $r->addRoute(
            'GET',
            '/portal/staff/{id:\d+}',
            ['Portal\Academics\StaffController', 'getStaffById']
        );
        $r->addRoute(
            'DELETE',
            '/portal/staff/{id:\d+}',
            ['Portal\Academics\StaffController', 'deleteStaff']
        );

        // Course assignments
        $r->addRoute(
            'POST',
            '/portal/course-assignments',
            ['Portal\Academics\CourseAssignmentController', 'storeCourseAssignment']
        );
        $r->addRoute(
            'GET',
            '/portal/course-assignments',
            ['Portal\Academics\CourseAssignmentController', 'getCourseAssignments']
        );

        // Course registration
        $r->addRoute(
            'GET',
            '/portal/course-registrations/terms',
            ['Portal\Results\CourseRegistrationController', 'getClassRegistrationTerms']
        );

        // course routes
        $r->addRoute(
            'POST',
            '/portal/courses',
            ['Portal\Academics\CourseController', 'addCourse']
        );
        $r->addRoute(
            'POST',
            '/portal/courses/{course_id:\d+}/attendance',
            ['Portal\Academics\AttendanceController', 'addCourseAttendance']
        );
        $r->addRoute(
            'PUT',
            '/portal/courses/{id:\d+}',
            ['Portal\Academics\CourseController', 'updateCourse']
        );
        $r->addRoute(
            'GET',
            '/portal/courses',
            ['Portal\Academics\CourseController', 'getCourses']
        );
        $r->addRoute(
            'GET',
            '/portal/courses/{course_id:\d+}/students',
            ['Portal\Results\CourseRegistrationController', 'getStudentsForCourseInClass']
        );
        $r->addRoute(
            'GET',
            '/portal/courses/{course_id:\d+}/attendance/single',
            ['Portal\Academics\AttendanceController', 'getSingleCourseAttendance']
        );
        $r->addRoute(
            'GET',
            '/portal/courses/{course_id:\d+}/attendance',
            ['Portal\Academics\AttendanceController', 'getAllCourseAttendance']
        );
        $r->addRoute(
            'DELETE',
            '/portal/courses/{id:\d+}',
            ['Portal\Academics\CourseController', 'deleteCourse']
        );

        // Assessment routes
        $r->addRoute(
            'POST',
            '/portal/assessments',
            ['Portal\Results\AssessmentController', 'addAssessments']
        );
        $r->addRoute(
            'PUT',
            '/portal/assessments/{id:\d+}',
            ['Portal\Results\AssessmentController', 'updateAssessment']
        );
        $r->addRoute(
            'GET',
            '/portal/assessments',
            ['Portal\Results\AssessmentController', 'getAllAssessments']
        );
        $r->addRoute(
            'GET',
            '/portal/assessments/{level_id:\d+}',
            ['Portal\Results\AssessmentController', 'getAssessmentByLevel']
        );
        $r->addRoute(
            'DELETE',
            '/portal/assessments/{id:\d+}',
            ['Portal\Results\AssessmentController', 'deleteAssessment']
        );

        $r->addRoute(
            'POST',
            '/portal/grades',
            ['Portal\Results\GradeController', 'addGrades']
        );
        $r->addRoute(
            'PUT',
            '/portal/grades/{id:\d+}',
            ['Portal\Results\GradeController', 'updateGrade']
        );
        $r->addRoute(
            'GET',
            '/portal/grades',
            ['Portal\Results\GradeController', 'getGrades']
        );
        $r->addRoute(
            'DELETE',
            '/portal/grades/{id:\d+}',
            ['Portal\Results\GradeController', 'deleteGrade']
        );

        $r->addRoute(
            'PUT',
            '/portal/attendance/{id:\d+}',
            ['Portal\Academics\AttendanceController', 'updateAttendance']
        );
        $r->addRoute(
            'GET',
            '/portal/attendance/history',
            ['Portal\Academics\AttendanceController', 'getAttendanceHistory']
        );
        $r->addRoute(
            'GET',
            '/portal/attendance/{id:\d+}',
            ['Portal\Academics\AttendanceController', 'getAttendanceDetails']
        );
        $r->addRoute(
            'PUT',
            '/portal/result/class-result',
            ['Portal\Results\ResultController', 'updateResult']
        );

        $r->addRoute(
            'POST',
            '/portal/skill-behavior',
            ['Portal\Academics\SkillBehaviorController', 'store']
        );
        $r->addRoute(
            'PUT',
            '/portal/skill-behavior/{id:\d+}',
            ['Portal\Academics\SkillBehaviorController', 'update']
        );
        $r->addRoute(
            'GET',
            '/portal/skill-behavior',
            ['Portal\Academics\SkillBehaviorController', 'get']
        );

        // Movies
        $r->addRoute(
            'GET',
            '/public/movies/all',
            ['Explore\MovieController', 'getSampleFromAllCategories']
        );
        $r->addRoute(
            'GET',
            '/public/movies/category/{cat}',
            ['Explore\MovieController', 'getByCategory']
        );
        $r->addRoute(
            'GET',
            '/public/movies/genres',
            ['Explore\MovieController', 'getAllGenres']
        );
        $r->addRoute(
            'GET',
            '/public/movies/genre/{id:\d+}',
            ['Explore\MovieController', 'getByGenre']
        );
        $r->addRoute(
            'GET',
            '/public/movies/shorts',
            ['Explore\MovieController', 'getAllShorts']
        );
        $r->addRoute(
            'GET',
            '/public/key-buddy/content',
            ['Explore\TemporalController', 'getContent']
        );

        // Elearning
        $r->addRoute(
            'POST',
            '/portal/elearning/syllabus',
            ['Portal\ELearning\SyllabusController', 'store']
        );
        $r->addRoute(
            'POST',
            '/portal/elearning/topic',
            ['Portal\ELearning\TopicController', 'store']
        );
        $r->addRoute(
            'POST',
            '/portal/elearning/material',
            ['Portal\ELearning\MaterialController', 'store']
        );
        $r->addRoute(
            'POST',
            '/portal/elearning/assignment',
            ['Portal\ELearning\AssignmentController', 'store']
        );
        $r->addRoute(
            'POST',
            '/portal/elearning/quiz',
            ['Portal\ELearning\QuizController', 'store']
        );
        $r->addRoute(
            'POST',
            '/portal/elearning/{content_id:\d+}/comments',
            ['Portal\ELearning\ContentCommentController', 'store']
        );
        $r->addRoute(
            'PUT',
            '/portal/elearning/quiz',
            ['Portal\ELearning\QuizController', 'update']
        );
        $r->addRoute(
            'PUT',
            '/portal/elearning/quiz/mark',
            ['Portal\ELearning\QuizSubmissionController', 'markQuiz']
        );
        $r->addRoute(
            'PUT',
            '/portal/elearning/quiz/{content_id:\d+}/publish',
            ['Portal\ELearning\QuizSubmissionController', 'publishQuiz']
        );
        $r->addRoute(
            'PUT',
            '/portal/elearning/syllabus/{id:\d+}',
            ['Portal\ELearning\SyllabusController', 'update']
        );
        $r->addRoute(
            'PUT',
            '/portal/elearning/material/{id:\d+}',
            ['Portal\ELearning\MaterialController', 'update']
        );
        $r->addRoute(
            'PUT',
            '/portal/elearning/assignment/{id:\d+}',
            ['Portal\ELearning\AssignmentController', 'update']
        );
        $r->addRoute(
            'PUT',
            '/portal/elearning/assignment/mark',
            ['Portal\ELearning\AssignmentSubmissionController', 'markAssignment']
        );
        $r->addRoute(
            'PUT',
            '/portal/elearning/assignment/{content_id:\d+}/publish',
            ['Portal\ELearning\AssignmentSubmissionController', 'publishAssignment']
        );
        $r->addRoute(
            'PUT',
            '/portal/elearning/comments/{id:\d+}',
            ['Portal\ELearning\ContentCommentController', 'update']
        );
        $r->addRoute(
            'GET',
            '/portal/elearning/syllabus',
            ['Portal\ELearning\SyllabusController', 'get']
        );
        $r->addRoute(
            'GET',
            '/portal/elearning/syllabus/staff',
            ['Portal\ELearning\SyllabusController', 'getByStaff']
        );
        $r->addRoute(
            'GET',
            '/portal/elearning/syllabus/{syllabus_id:\d+}/contents',
            ['Portal\ELearning\ContentManagerController', 'getAllContents']
        );
        $r->addRoute(
            'GET',
            '/portal/elearning/contents/{id:\d+}',
            ['Portal\ELearning\ContentManagerController', 'getContentById']
        );
        $r->addRoute(
            'GET',
            '/portal/elearning/syllabus/{syllabus_id:\d+}/topics',
            ['Portal\ELearning\TopicController', 'get']
        );
        $r->addRoute(
            'GET',
            '/portal/elearning/{content_id:\d+}/comments',
            ['Portal\ELearning\ContentCommentController', 'getCommentsByContentId']
        );
        $r->addRoute(
            'GET',
            '/portal/elearning/{syllabus_id:\d+}/comments/streams',
            ['Portal\ELearning\ContentCommentController', 'streams']
        );
        $r->addRoute(
            'GET',
            '/portal/elearning/overview',
            ['Portal\ELearning\ContentManagerController', 'getDashboard']
        );
        $r->addRoute(
            'GET',
            '/portal/elearning/staff/{teacher_id:\d+}/overview',
            ['Portal\ELearning\ContentManagerController', 'staffDashboardSummary']
        );
        $r->addRoute(
            'GET',
            '/portal/elearning/assignment/{id:\d+}/submissions',
            ['Portal\ELearning\AssignmentSubmissionController', 'getSubmissions']
        );
        $r->addRoute(
            'GET',
            '/portal/elearning/quiz/{id:\d+}/submissions',
            ['Portal\ELearning\QuizSubmissionController', 'getSubmissions']
        );
        $r->addRoute(
            'DELETE',
            '/portal/elearning/contents/{content_id:\d+}',
            ['Portal\ELearning\ContentManagerController', 'delete']
        );
        $r->addRoute(
            'DELETE',
            '/portal/elearning/syllabus/{id:\d+}',
            ['Portal\ELearning\SyllabusController', 'delete']
        );
        $r->addRoute(
            'DELETE',
            '/portal/elearning/quiz/{content_id:\d+}/{question_id:\d+}',
            ['Portal\ELearning\QuizController', 'delete']
        );
        $r->addRoute(
            'DELETE',
            '/portal/elearning/comments/{id:\d+}',
            ['Portal\ELearning\ContentCommentController', 'delete']
        );

        // Payment
        $r->addRoute(
            'POST',
            '/portal/payments/accounts',
            ['Portal\Payments\AccountController', 'store']
        );
        $r->addRoute(
            'POST',
            '/portal/payments/fee-names',
            ['Portal\Payments\FeeTypeController', 'store']
        );
        $r->addRoute(
            'POST',
            '/portal/payments/vendors',
            ['Portal\Payments\VendorController', 'store']
        );
        $r->addRoute(
            'POST',
            '/portal/payments/invoices',
            ['Portal\Payments\NextTermFeeController', 'upsert']
        );
        $r->addRoute(
            'POST',
            '/portal/payments/expenditure',
            ['Portal\Payments\ExpenditureController', 'store']
        );
        $r->addRoute(
            'PUT',
            '/portal/payments/accounts/{id:\d+}',
            ['Portal\Payments\AccountController', 'update']
        );
        $r->addRoute(
            'PUT',
            '/portal/payments/fee-names/{id:\d+}',
            ['Portal\Payments\FeeTypeController', 'update']
        );
        $r->addRoute(
            'PUT',
            '/portal/payments/vendors/{id:\d+}',
            ['Portal\Payments\VendorController', 'update']
        );
        $r->addRoute(
            'PUT',
            '/portal/payments/expenditure/{id:\d+}',
            ['Portal\Payments\ExpenditureController', 'update']
        );
        $r->addRoute(
            'GET',
            '/portal/payments/accounts',
            ['Portal\Payments\AccountController', 'get']
        );
        $r->addRoute(
            'GET',
            '/portal/payments/fee-names',
            ['Portal\Payments\FeeTypeController', 'get']
        );
        $r->addRoute(
            'GET',
            '/portal/payments/invoices',
            ['Portal\Payments\NextTermFeeController', 'get']
        );
        $r->addRoute(
            'POST',
            '/portal/payments/income/report/generate',
            ['Portal\Payments\IncomeController', 'generateReport']
        );
        $r->addRoute(
            'POST',
            '/portal/payments/expenditure/report/generate',
            ['Portal\Payments\ExpenditureController', 'generateReport']
        );
        $r->addRoute(
            'GET',
            '/portal/payments/dashboard/summary',
            ['Portal\Payments\PaymentDashboardController', 'getDashboardSummary']
        );
        $r->addRoute(
            'GET',
            '/portal/payments/invoices/paid',
            ['Portal\Payments\PaymentDashboardController', 'getPaidInvoices']
        );
        $r->addRoute(
            'GET',
            '/portal/payments/invoices/unpaid',
            ['Portal\Payments\PaymentDashboardController', 'getUnPaidInvoices']
        );
        $r->addRoute(
            'GET',
            '/portal/payments/vendors',
            ['Portal\Payments\VendorController', 'get']
        );
        $r->addRoute(
            'GET',
            '/portal/payments/vendors/{id:\d+}/transactions/annual',
            ['Portal\Payments\VendorController', 'annualHistory']
        );
        $r->addRoute(
            'GET',
            '/portal/payments/vendors/{id:\d+}/transactions/{year:\d+}',
            ['Portal\Payments\VendorController', 'transactions']
        );
        $r->addRoute(
            'DELETE',
            '/portal/payments/accounts/{id:\d+}',
            ['Portal\Payments\AccountController', 'delete']
        );
        $r->addRoute(
            'DELETE',
            '/portal/payments/fee-names/{id:\d+}',
            ['Portal\Payments\FeeTypeController', 'delete']
        );
        $r->addRoute(
            'DELETE',
            '/portal/payments/vendors/{id:\d+}',
            ['Portal\Payments\VendorController', 'delete']
        );
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
