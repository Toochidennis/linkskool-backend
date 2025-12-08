<?php return array (
  0 => 
  array (
    'method' => 'GET',
    'path' => '/public/admissions',
    'class' => 'V3\\App\\Controllers\\Explore\\AdmissionController',
    'methodName' => 'getAdmissions',
    'middleware' => 
    array (
    ),
  ),
  1 => 
  array (
    'method' => 'GET',
    'path' => '/public/audit-logs/user/{userId:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\AuditLogController',
    'methodName' => 'getLogsByUserId',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
      3 => 'role:user',
    ),
  ),
  2 => 
  array (
    'method' => 'GET',
    'path' => '/public/books',
    'class' => 'V3\\App\\Controllers\\Explore\\BookController',
    'methodName' => 'index',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  3 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/exams',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtController',
    'methodName' => 'getAllExams',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  4 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/exams/{examTypeId:\\d+}/courses',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtController',
    'methodName' => 'getCoursesByExamType',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  5 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/exams/{exam_id:\\d+}/questions',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtController',
    'methodName' => 'getQuestions',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  6 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt/users',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUserController',
    'methodName' => 'storeUser',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  7 => 
  array (
    'method' => 'PUT',
    'path' => '/public/cbt/users/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUserController',
    'methodName' => 'updateUser',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  8 => 
  array (
    'method' => 'PUT',
    'path' => '/public/cbt/users/{id:\\d+}/payment-status',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUserController',
    'methodName' => 'updatePaymentStatus',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  9 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/users/{email}',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUserController',
    'methodName' => 'getUserByEmail',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  10 => 
  array (
    'method' => 'GET',
    'path' => '/public/dashboard',
    'class' => 'V3\\App\\Controllers\\Explore\\ContentDashboardController',
    'methodName' => 'getDashboardData',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  11 => 
  array (
    'method' => 'GET',
    'path' => '/public/activity-logs',
    'class' => 'V3\\App\\Controllers\\Explore\\ContentDashboardController',
    'methodName' => 'getActivityLogs',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  12 => 
  array (
    'method' => 'POST',
    'path' => '/public/courses',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseController',
    'methodName' => 'storeCourse',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  13 => 
  array (
    'method' => 'PUT',
    'path' => '/public/courses/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseController',
    'methodName' => 'updateCourse',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  14 => 
  array (
    'method' => 'GET',
    'path' => '/public/courses',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseController',
    'methodName' => 'getAllCourses',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  15 => 
  array (
    'method' => 'DELETE',
    'path' => '/public/courses/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseController',
    'methodName' => 'deleteCourse',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  16 => 
  array (
    'method' => 'POST',
    'path' => '/public/questions',
    'class' => 'V3\\App\\Controllers\\Explore\\ExamController',
    'methodName' => 'storeQuestions',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  17 => 
  array (
    'method' => 'GET',
    'path' => '/public/exams',
    'class' => 'V3\\App\\Controllers\\Explore\\ExamController',
    'methodName' => 'getExams',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  18 => 
  array (
    'method' => 'GET',
    'path' => '/public/exams/questions',
    'class' => 'V3\\App\\Controllers\\Explore\\ExamController',
    'methodName' => 'getQuestions',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  19 => 
  array (
    'method' => 'DELETE',
    'path' => '/public/exams',
    'class' => 'V3\\App\\Controllers\\Explore\\ExamController',
    'methodName' => 'deleteExam',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  20 => 
  array (
    'method' => 'POST',
    'path' => '/public/exam-types',
    'class' => 'V3\\App\\Controllers\\Explore\\ExamTypeController',
    'methodName' => 'storeExamType',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  21 => 
  array (
    'method' => 'PUT',
    'path' => '/public/exam-types/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\ExamTypeController',
    'methodName' => 'updateExamType',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  22 => 
  array (
    'method' => 'GET',
    'path' => '/public/exam-types/active',
    'class' => 'V3\\App\\Controllers\\Explore\\ExamTypeController',
    'methodName' => 'getActiveExamTypes',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  23 => 
  array (
    'method' => 'GET',
    'path' => '/public/exam-types',
    'class' => 'V3\\App\\Controllers\\Explore\\ExamTypeController',
    'methodName' => 'getAllExamTypes',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  24 => 
  array (
    'method' => 'DELETE',
    'path' => '/public/exam-types/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\ExamTypeController',
    'methodName' => 'deleteExamType',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  25 => 
  array (
    'method' => 'GET',
    'path' => '/public/export-questions',
    'class' => 'V3\\App\\Controllers\\Explore\\ExportQuestionsController',
    'methodName' => 'exportQuestions',
    'middleware' => 
    array (
    ),
  ),
  26 => 
  array (
    'method' => 'GET',
    'path' => '/public/for-you',
    'class' => 'V3\\App\\Controllers\\Explore\\ForYouController',
    'methodName' => 'forYou',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  27 => 
  array (
    'method' => 'GET',
    'path' => '/public/games',
    'class' => 'V3\\App\\Controllers\\Explore\\GameController',
    'methodName' => 'index',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  28 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/all',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getAllMovies',
    'middleware' => 
    array (
    ),
  ),
  29 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/category/{cat}',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getByCategory',
    'middleware' => 
    array (
    ),
  ),
  30 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/genres/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getByGenre',
    'middleware' => 
    array (
    ),
  ),
  31 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/genres',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getAllGenres',
    'middleware' => 
    array (
    ),
  ),
  32 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/shorts',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getAllShorts',
    'middleware' => 
    array (
    ),
  ),
  33 => 
  array (
    'method' => 'GET',
    'path' => '/public/news',
    'class' => 'V3\\App\\Controllers\\Explore\\NewsController',
    'methodName' => 'index',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  34 => 
  array (
    'method' => 'GET',
    'path' => '/public/key-buddy/content',
    'class' => 'V3\\App\\Controllers\\Explore\\TemporalController',
    'methodName' => 'getContent',
    'middleware' => 
    array (
    ),
  ),
  35 => 
  array (
    'method' => 'POST',
    'path' => '/public/users',
    'class' => 'V3\\App\\Controllers\\Explore\\UserController',
    'methodName' => 'storeUser',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  36 => 
  array (
    'method' => 'POST',
    'path' => '/public/users/login',
    'class' => 'V3\\App\\Controllers\\Explore\\UserController',
    'methodName' => 'login',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  37 => 
  array (
    'method' => 'PUT',
    'path' => '/public/users/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\UserController',
    'methodName' => 'updateUser',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  38 => 
  array (
    'method' => 'GET',
    'path' => '/public/users',
    'class' => 'V3\\App\\Controllers\\Explore\\UserController',
    'methodName' => 'getUsers',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  39 => 
  array (
    'method' => 'DELETE',
    'path' => '/public/users/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\UserController',
    'methodName' => 'deleteUser',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  40 => 
  array (
    'method' => 'GET',
    'path' => '/public/videos',
    'class' => 'V3\\App\\Controllers\\Explore\\VideoController',
    'methodName' => 'index',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
);