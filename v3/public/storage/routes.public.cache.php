<?php return array (
  0 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt/billing/verify',
    'class' => 'V3\\App\\Controllers\\Explore\\BillingController',
    'methodName' => 'verify',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  1 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt/billing/initiate',
    'class' => 'V3\\App\\Controllers\\Explore\\BillingController',
    'methodName' => 'initiate',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  2 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/billing/{reference}/status',
    'class' => 'V3\\App\\Controllers\\Explore\\BillingController',
    'methodName' => 'paymentStatus',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  3 => 
  array (
    'method' => 'GET',
    'path' => '/public/programs',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramQueryController',
    'methodName' => 'getPrograms',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  4 => 
  array (
    'method' => 'GET',
    'path' => '/public/programs/{slug}/courses',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramQueryController',
    'methodName' => 'getProgramCourseCohorts',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  5 => 
  array (
    'method' => 'GET',
    'path' => '/public/programs/cohorts/{slug}',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramQueryController',
    'methodName' => 'getCohortDetails',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  6 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/all',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getAllMovies',
    'middleware' => 
    array (
    ),
  ),
  7 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/category/{cat}',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getByCategory',
    'middleware' => 
    array (
    ),
  ),
  8 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/genres/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getByGenre',
    'middleware' => 
    array (
    ),
  ),
  9 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/genres',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getAllGenres',
    'middleware' => 
    array (
    ),
  ),
  10 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/shorts',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getAllShorts',
    'middleware' => 
    array (
    ),
  ),
  11 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt/study/topics',
    'class' => 'V3\\App\\Controllers\\Explore\\StudyTopicController',
    'methodName' => 'storeTopic',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  12 => 
  array (
    'method' => 'PUT',
    'path' => '/public/cbt/study/topics/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\StudyTopicController',
    'methodName' => 'updateTopic',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  13 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/study/topics',
    'class' => 'V3\\App\\Controllers\\Explore\\StudyTopicController',
    'methodName' => 'getAllTopics',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  14 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/study/courses/{course_id:\\d+}/topics',
    'class' => 'V3\\App\\Controllers\\Explore\\StudyTopicController',
    'methodName' => 'getTopicsByCourseId',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  15 => 
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
  16 => 
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
  17 => 
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
  18 => 
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
  19 => 
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
    'methodName' => 'getActiveExamTypesWithCourses',
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
    'methodName' => 'getExamTypesWithCourses',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  24 => 
  array (
    'method' => 'GET',
    'path' => '/public/exam-types/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\ExamTypeController',
    'methodName' => 'getExamTypeById',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  25 => 
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
  26 => 
  array (
    'method' => 'POST',
    'path' => '/public/learning/profiles',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramProfileController',
    'methodName' => 'createProfile',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  27 => 
  array (
    'method' => 'PUT',
    'path' => '/public/learning/profiles/{id}',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramProfileController',
    'methodName' => 'updateProfile',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  28 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/profiles',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramProfileController',
    'methodName' => 'getProfilesByUserId',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  29 => 
  array (
    'method' => 'DELETE',
    'path' => '/public/learning/profiles/{id}',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramProfileController',
    'methodName' => 'deleteProfile',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  30 => 
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
  31 => 
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
  32 => 
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
  33 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt/gamify/leaderboard',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtGamifyController',
    'methodName' => 'storeLeaderboardData',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  34 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/gamify/leaderboard/summary',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtGamifyController',
    'methodName' => 'getLeaderboardSummary',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  35 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/gamify/leaderboard',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtGamifyController',
    'methodName' => 'getFullLeaderboard',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  36 => 
  array (
    'method' => 'POST',
    'path' => '/public/learn/courses',
    'class' => 'V3\\App\\Controllers\\Explore\\LearningCourseController',
    'methodName' => 'create',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  37 => 
  array (
    'method' => 'POST',
    'path' => '/public/learn/courses/{id}',
    'class' => 'V3\\App\\Controllers\\Explore\\LearningCourseController',
    'methodName' => 'update',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  38 => 
  array (
    'method' => 'GET',
    'path' => '/public/learn/courses',
    'class' => 'V3\\App\\Controllers\\Explore\\LearningCourseController',
    'methodName' => 'getCourses',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  39 => 
  array (
    'method' => 'GET',
    'path' => '/public/learn/courses/program/{id}',
    'class' => 'V3\\App\\Controllers\\Explore\\LearningCourseController',
    'methodName' => 'getCoursesByProgramId',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  40 => 
  array (
    'method' => 'DELETE',
    'path' => '/public/learn/courses/{id}',
    'class' => 'V3\\App\\Controllers\\Explore\\LearningCourseController',
    'methodName' => 'deleteCourse',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  41 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/study/exam-types/{exam_type_id:\\d+}/courses/{course_id:\\d+}/topics',
    'class' => 'V3\\App\\Controllers\\Explore\\StudyContentController',
    'methodName' => 'getStudyTopics',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  42 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/study/topics/{topic_id:\\d+}/content',
    'class' => 'V3\\App\\Controllers\\Explore\\StudyContentController',
    'methodName' => 'getStudyContent',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  43 => 
  array (
    'method' => 'POST',
    'path' => '/public/learning/cohorts/{cohort_id}/enrollments',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortEnrollmentController',
    'methodName' => 'enrollUser',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  44 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/cohorts/{cohort_id}/enrollments/is-enrolled',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortEnrollmentController',
    'methodName' => 'isUserEnrolled',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  45 => 
  array (
    'method' => 'PUT',
    'path' => '/public/learning/cohorts/{cohort_id}/enrollments/status',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortEnrollmentController',
    'methodName' => 'updateStatus',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  46 => 
  array (
    'method' => 'POST',
    'path' => '/public/learning/cohorts/{cohort_id}/enrollments/payment',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortEnrollmentController',
    'methodName' => 'verifyPayment',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  47 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/cohorts/{cohort_id}/enrollments/payment-status',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortEnrollmentController',
    'methodName' => 'getPaymentStatus',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  48 => 
  array (
    'method' => 'PUT',
    'path' => '/public/learning/cohorts/{cohort_id}/enrollments/lessons-taken',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortEnrollmentController',
    'methodName' => 'updateLessonsTaken',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  49 => 
  array (
    'method' => 'POST',
    'path' => '/public/learning/cohorts/enrollments/checkout',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortEnrollmentController',
    'methodName' => 'checkout',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  50 => 
  array (
    'method' => 'POST',
    'path' => '/public/learning/cohorts/enrollments/checkout/offline',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortEnrollmentController',
    'methodName' => 'offlineCheckout',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  51 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/cohorts/enrollments/checkout/{reference}/status',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortEnrollmentController',
    'methodName' => 'checkPaymentStatus',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  52 => 
  array (
    'method' => 'POST',
    'path' => '/public/learning/cohorts/enrollments/reserve',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortEnrollmentController',
    'methodName' => 'reserve',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  53 => 
  array (
    'method' => 'POST',
    'path' => '/public/learning/cohorts/enrollments/free',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortEnrollmentController',
    'methodName' => 'freeEnroll',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  54 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt/challenges',
    'class' => 'V3\\App\\Controllers\\Explore\\ChallengeController',
    'methodName' => 'storeChallenge',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  55 => 
  array (
    'method' => 'PUT',
    'path' => '/public/cbt/challenges/{challenge_id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\ChallengeController',
    'methodName' => 'updateChallenge',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  56 => 
  array (
    'method' => 'PUT',
    'path' => '/public/cbt/challenges/status/{challenge_id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\ChallengeController',
    'methodName' => 'updateChallengeStatus',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  57 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/challenges',
    'class' => 'V3\\App\\Controllers\\Explore\\ChallengeController',
    'methodName' => 'getChallenges',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  58 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/challenges/questions',
    'class' => 'V3\\App\\Controllers\\Explore\\ChallengeController',
    'methodName' => 'getChallengeQuestions',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  59 => 
  array (
    'method' => 'DELETE',
    'path' => '/public/cbt/challenges/{challenge_id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\ChallengeController',
    'methodName' => 'deleteChallenge',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  60 => 
  array (
    'method' => 'GET',
    'path' => '/public/notification-campaigns/options',
    'class' => 'V3\\App\\Controllers\\Explore\\NotificationCampaignController',
    'methodName' => 'getOptions',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  61 => 
  array (
    'method' => 'POST',
    'path' => '/public/notification-campaigns/send',
    'class' => 'V3\\App\\Controllers\\Explore\\NotificationCampaignController',
    'methodName' => 'send',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  62 => 
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
  63 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt/license/activate/desktop',
    'class' => 'V3\\App\\Controllers\\Explore\\LicenseController',
    'methodName' => 'activateDesktop',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  64 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt/license/activate/mobile',
    'class' => 'V3\\App\\Controllers\\Explore\\LicenseController',
    'methodName' => 'activateMobile',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  65 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/license/status/desktop',
    'class' => 'V3\\App\\Controllers\\Explore\\LicenseController',
    'methodName' => 'desktopStatus',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  66 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/license/status/mobile',
    'class' => 'V3\\App\\Controllers\\Explore\\LicenseController',
    'methodName' => 'mobileStatus',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  67 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/license/plans/desktop',
    'class' => 'V3\\App\\Controllers\\Explore\\LicenseController',
    'methodName' => 'desktopPlans',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  68 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/license/plans/mobile',
    'class' => 'V3\\App\\Controllers\\Explore\\LicenseController',
    'methodName' => 'mobilePlans',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  69 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt/license/trial/start',
    'class' => 'V3\\App\\Controllers\\Explore\\LicenseController',
    'methodName' => 'startTrial',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  70 => 
  array (
    'method' => 'POST',
    'path' => '/public/learning/lessons/{lesson_id}/attendance',
    'class' => 'V3\\App\\Controllers\\Explore\\LessonAttendanceController',
    'methodName' => 'takeLessonAttendance',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  71 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/lessons/{lesson_id}/attendance',
    'class' => 'V3\\App\\Controllers\\Explore\\LessonAttendanceController',
    'methodName' => 'getLessonAttendance',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  72 => 
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
  73 => 
  array (
    'method' => 'GET',
    'path' => '/public/learn/lessons/{lesson_id}/submissions',
    'class' => 'V3\\App\\Controllers\\Explore\\GradeLessonAssignmentController',
    'methodName' => 'getLessonSubmissions',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  74 => 
  array (
    'method' => 'POST',
    'path' => '/public/learn/lessons/submissions/auto-grade',
    'class' => 'V3\\App\\Controllers\\Explore\\GradeLessonAssignmentController',
    'methodName' => 'autoGradeSubmissions',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  75 => 
  array (
    'method' => 'POST',
    'path' => '/public/learn/lessons/submissions/grade',
    'class' => 'V3\\App\\Controllers\\Explore\\GradeLessonAssignmentController',
    'methodName' => 'gradeSubmission',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  76 => 
  array (
    'method' => 'POST',
    'path' => '/public/learn/lessons/submissions/notify',
    'class' => 'V3\\App\\Controllers\\Explore\\GradeLessonAssignmentController',
    'methodName' => 'notifyStudent',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  77 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt/study/exam-types/{exam_type_id:\\d+}/topics',
    'class' => 'V3\\App\\Controllers\\Explore\\StudyExamTypeTopicController',
    'methodName' => 'linkTopicsToExamType',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  78 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/study/exam-types/{exam_type_id:\\d+}/topics',
    'class' => 'V3\\App\\Controllers\\Explore\\StudyExamTypeTopicController',
    'methodName' => 'getTopicsByExamType',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  79 => 
  array (
    'method' => 'POST',
    'path' => '/public/learning/lessons/{lesson_id}/assignments',
    'class' => 'V3\\App\\Controllers\\Explore\\CohortTasksSubmissionController',
    'methodName' => 'submitProject',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  80 => 
  array (
    'method' => 'POST',
    'path' => '/public/learn/programs/courses/cohorts',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramCourseCohortController',
    'methodName' => 'create',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  81 => 
  array (
    'method' => 'POST',
    'path' => '/public/learn/programs/courses/cohorts/{id}',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramCourseCohortController',
    'methodName' => 'update',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  82 => 
  array (
    'method' => 'PUT',
    'path' => '/public/learn/programs/courses/cohorts/{id}/status',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramCourseCohortController',
    'methodName' => 'updateStatus',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  83 => 
  array (
    'method' => 'GET',
    'path' => '/public/learn/programs/{program_id}/courses/{course_id}/cohorts',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramCourseCohortController',
    'methodName' => 'getAllCohortsByCourseId',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  84 => 
  array (
    'method' => 'GET',
    'path' => '/public/learn/programs/{program_id}/cohorts',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramCourseCohortController',
    'methodName' => 'getProgramCohorts',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  85 => 
  array (
    'method' => 'DELETE',
    'path' => '/public/learn/programs/courses/cohorts/{id}',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramCourseCohortController',
    'methodName' => 'delete',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  86 => 
  array (
    'method' => 'POST',
    'path' => '/public/levels',
    'class' => 'V3\\App\\Controllers\\Explore\\LevelController',
    'methodName' => 'addLevel',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  87 => 
  array (
    'method' => 'PUT',
    'path' => '/public/levels/{id}',
    'class' => 'V3\\App\\Controllers\\Explore\\LevelController',
    'methodName' => 'updateLevel',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  88 => 
  array (
    'method' => 'GET',
    'path' => '/public/levels',
    'class' => 'V3\\App\\Controllers\\Explore\\LevelController',
    'methodName' => 'getLevels',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  89 => 
  array (
    'method' => 'DELETE',
    'path' => '/public/levels/{id}',
    'class' => 'V3\\App\\Controllers\\Explore\\LevelController',
    'methodName' => 'deleteLevel',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  90 => 
  array (
    'method' => 'POST',
    'path' => '/public/learn/programs/lessons/{lesson_id}/quizzes',
    'class' => 'V3\\App\\Controllers\\Explore\\CohortLessonQuizController',
    'methodName' => 'create',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  91 => 
  array (
    'method' => 'PUT',
    'path' => '/public/learn/programs/lessons/{lesson_id}/quizzes/{question_id}',
    'class' => 'V3\\App\\Controllers\\Explore\\CohortLessonQuizController',
    'methodName' => 'update',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  92 => 
  array (
    'method' => 'GET',
    'path' => '/public/learn/programs/lessons/{lesson_id}/quizzes',
    'class' => 'V3\\App\\Controllers\\Explore\\CohortLessonQuizController',
    'methodName' => 'getByQuizLessonId',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  93 => 
  array (
    'method' => 'DELETE',
    'path' => '/public/learn/programs/lessons/quizzes/{question_id}',
    'class' => 'V3\\App\\Controllers\\Explore\\CohortLessonQuizController',
    'methodName' => 'delete',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  94 => 
  array (
    'method' => 'GET',
    'path' => '/public/learn/programs/{program_id}/enrollment-analysis/profiles',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramEnrollmentAnalysisController',
    'methodName' => 'getProgramProfilesAnalysis',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  95 => 
  array (
    'method' => 'GET',
    'path' => '/public/learn/programs/{program_id}/enrollment-analysis/profiles/{profile_id}/enrollments',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramEnrollmentAnalysisController',
    'methodName' => 'getProfileEnrollments',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  96 => 
  array (
    'method' => 'POST',
    'path' => '/public/learning/cohorts/{cohort_id}/lessons/{lesson_id}/progress',
    'class' => 'V3\\App\\Controllers\\Explore\\CohortLessonProgressController',
    'methodName' => 'updateProgress',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  97 => 
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
  98 => 
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
  99 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/exams-courses',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtController',
    'methodName' => 'getExamsWithCourses',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  100 => 
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
  101 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/exams/questions/by-topic',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtController',
    'methodName' => 'getQuestionsByTopicId',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  102 => 
  array (
    'method' => 'GET',
    'path' => '/public/export-questions',
    'class' => 'V3\\App\\Controllers\\Explore\\ExportQuestionsController',
    'methodName' => 'exportQuestions',
    'middleware' => 
    array (
    ),
  ),
  103 => 
  array (
    'method' => 'POST',
    'path' => '/public/questions',
    'class' => 'V3\\App\\Controllers\\Explore\\ExamController',
    'methodName' => 'storeQuestions',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  104 => 
  array (
    'method' => 'PUT',
    'path' => '/public/questions',
    'class' => 'V3\\App\\Controllers\\Explore\\ExamController',
    'methodName' => 'updateQuestions',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  105 => 
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
  106 => 
  array (
    'method' => 'GET',
    'path' => '/public/exams/{exam_id:\\d+}/questions',
    'class' => 'V3\\App\\Controllers\\Explore\\ExamController',
    'methodName' => 'getQuestions',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  107 => 
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
  108 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/exams/{exam_type}/download',
    'class' => 'V3\\App\\Controllers\\Explore\\DownloadExamsController',
    'methodName' => 'downloadExamType',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  109 => 
  array (
    'method' => 'POST',
    'path' => '/public/webhooks/paystack',
    'class' => 'V3\\App\\Controllers\\Explore\\WebhookController',
    'methodName' => 'paystackWebhook',
    'middleware' => 
    array (
    ),
  ),
  110 => 
  array (
    'method' => 'POST',
    'path' => '/public/learn/programs',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramController',
    'methodName' => 'create',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  111 => 
  array (
    'method' => 'POST',
    'path' => '/public/learn/programs/{id}',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramController',
    'methodName' => 'update',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  112 => 
  array (
    'method' => 'PUT',
    'path' => '/public/learn/programs/{id}/status',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramController',
    'methodName' => 'updateStatus',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  113 => 
  array (
    'method' => 'GET',
    'path' => '/public/learn/programs',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramController',
    'methodName' => 'getAllPrograms',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  114 => 
  array (
    'method' => 'DELETE',
    'path' => '/public/learn/programs/{id}',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramController',
    'methodName' => 'deleteProgram',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  115 => 
  array (
    'method' => 'POST',
    'path' => '/public/video-library/videos',
    'class' => 'V3\\App\\Controllers\\Explore\\VideoLibraryController',
    'methodName' => 'addVideo',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  116 => 
  array (
    'method' => 'GET',
    'path' => '/public/video-library/videos/{course_id}',
    'class' => 'V3\\App\\Controllers\\Explore\\VideoLibraryController',
    'methodName' => 'getVideosByCourse',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  117 => 
  array (
    'method' => 'GET',
    'path' => '/public/video-library/courses',
    'class' => 'V3\\App\\Controllers\\Explore\\VideoLibraryController',
    'methodName' => 'getCourses',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  118 => 
  array (
    'method' => 'GET',
    'path' => '/public/video-library/syllabi/{course_id}',
    'class' => 'V3\\App\\Controllers\\Explore\\VideoLibraryController',
    'methodName' => 'getSyllabiByCourse',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  119 => 
  array (
    'method' => 'GET',
    'path' => '/public/video-library/videos/published/{level_id}/{course_id}',
    'class' => 'V3\\App\\Controllers\\Explore\\VideoLibraryController',
    'methodName' => 'getPublishedVideos',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  120 => 
  array (
    'method' => 'GET',
    'path' => '/public/video-library/courses/by-level',
    'class' => 'V3\\App\\Controllers\\Explore\\VideoLibraryController',
    'methodName' => 'index',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  121 => 
  array (
    'method' => 'POST',
    'path' => '/public/video-library/videos/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\VideoLibraryController',
    'methodName' => 'updateVideo',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  122 => 
  array (
    'method' => 'PUT',
    'path' => '/public/video-library/videos/{id}/status',
    'class' => 'V3\\App\\Controllers\\Explore\\VideoLibraryController',
    'methodName' => 'updateVideoStatus',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  123 => 
  array (
    'method' => 'DELETE',
    'path' => '/public/video-library/videos/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\VideoLibraryController',
    'methodName' => 'deleteVideo',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  124 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/study/generate-content',
    'class' => 'V3\\App\\Controllers\\Explore\\StudyContentGenerationController',
    'methodName' => 'generate',
    'middleware' => 
    array (
    ),
  ),
  125 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/study/seed',
    'class' => 'V3\\App\\Controllers\\Explore\\StudyContentGenerationController',
    'methodName' => 'seed',
    'middleware' => 
    array (
    ),
  ),
  126 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/study/contents',
    'class' => 'V3\\App\\Controllers\\Explore\\StudyContentGenerationController',
    'methodName' => 'get',
    'middleware' => 
    array (
    ),
  ),
  127 => 
  array (
    'method' => 'POST',
    'path' => '/public/news',
    'class' => 'V3\\App\\Controllers\\Explore\\NewsController',
    'methodName' => 'addNews',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  128 => 
  array (
    'method' => 'POST',
    'path' => '/public/news/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\NewsController',
    'methodName' => 'updateNews',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  129 => 
  array (
    'method' => 'PUT',
    'path' => '/public/news/{id}/notify',
    'class' => 'V3\\App\\Controllers\\Explore\\NewsController',
    'methodName' => 'notifyNews',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  130 => 
  array (
    'method' => 'PUT',
    'path' => '/public/news/{id}/status',
    'class' => 'V3\\App\\Controllers\\Explore\\NewsController',
    'methodName' => 'updateNewsStatus',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  131 => 
  array (
    'method' => 'GET',
    'path' => '/public/news/admin',
    'class' => 'V3\\App\\Controllers\\Explore\\NewsController',
    'methodName' => 'getNewsAdmin',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  132 => 
  array (
    'method' => 'GET',
    'path' => '/public/news',
    'class' => 'V3\\App\\Controllers\\Explore\\NewsController',
    'methodName' => 'getNews',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  133 => 
  array (
    'method' => 'DELETE',
    'path' => '/public/news/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\NewsController',
    'methodName' => 'deleteNews',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  134 => 
  array (
    'method' => 'POST',
    'path' => '/public/seed/courses',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramCourseController',
    'methodName' => 'create',
    'middleware' => 
    array (
    ),
  ),
  135 => 
  array (
    'method' => 'POST',
    'path' => '/public/seed/programs',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramCourseController',
    'methodName' => 'createPrograms',
    'middleware' => 
    array (
    ),
  ),
  136 => 
  array (
    'method' => 'POST',
    'path' => '/public/seed/cohorts',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramCourseController',
    'methodName' => 'createCohort',
    'middleware' => 
    array (
    ),
  ),
  137 => 
  array (
    'method' => 'POST',
    'path' => '/public/seed/cohort/lessons',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramCourseController',
    'methodName' => 'createCohortLessons',
    'middleware' => 
    array (
    ),
  ),
  138 => 
  array (
    'method' => 'POST',
    'path' => '/public/seed/cohort/lesson/quizzes',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramCourseController',
    'methodName' => 'seedQuiz',
    'middleware' => 
    array (
    ),
  ),
  139 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/settings',
    'class' => 'V3\\App\\Controllers\\Explore\\SettingsController',
    'methodName' => 'getSettings',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  140 => 
  array (
    'method' => 'GET',
    'path' => '/public/key-buddy/content',
    'class' => 'V3\\App\\Controllers\\Explore\\TemporalController',
    'methodName' => 'getContent',
    'middleware' => 
    array (
    ),
  ),
  141 => 
  array (
    'method' => 'POST',
    'path' => '/public/advertisements',
    'class' => 'V3\\App\\Controllers\\Explore\\AdvertisementController',
    'methodName' => 'createAdvertisement',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  142 => 
  array (
    'method' => 'POST',
    'path' => '/public/advertisements/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\AdvertisementController',
    'methodName' => 'updateAdvertisement',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  143 => 
  array (
    'method' => 'GET',
    'path' => '/public/advertisements',
    'class' => 'V3\\App\\Controllers\\Explore\\AdvertisementController',
    'methodName' => 'getAllAdvertisements',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  144 => 
  array (
    'method' => 'GET',
    'path' => '/public/advertisements/published',
    'class' => 'V3\\App\\Controllers\\Explore\\AdvertisementController',
    'methodName' => 'getPublishedAdvertisements',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  145 => 
  array (
    'method' => 'PUT',
    'path' => '/public/advertisements/{id}/status',
    'class' => 'V3\\App\\Controllers\\Explore\\AdvertisementController',
    'methodName' => 'updateAdvertisementStatus',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  146 => 
  array (
    'method' => 'DELETE',
    'path' => '/public/advertisements/{id}',
    'class' => 'V3\\App\\Controllers\\Explore\\AdvertisementController',
    'methodName' => 'deleteAdvertisement',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  147 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt-updates',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUpdateController',
    'methodName' => 'storeUpdate',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  148 => 
  array (
    'method' => 'PUT',
    'path' => '/public/cbt-updates/{id:\\d+}/notify',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUpdateController',
    'methodName' => 'notifyUpdate',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  149 => 
  array (
    'method' => 'PUT',
    'path' => '/public/cbt-updates/{id:\\d+}/status',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUpdateController',
    'methodName' => 'updateStatus',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  150 => 
  array (
    'method' => 'PUT',
    'path' => '/public/cbt-updates/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUpdateController',
    'methodName' => 'updateUpdate',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  151 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt-updates',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUpdateController',
    'methodName' => 'listUpdates',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  152 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt-updates/{id:\\d+}/comments',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUpdateController',
    'methodName' => 'addComment',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  153 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt-updates/{id:\\d+}/comments',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUpdateController',
    'methodName' => 'getComments',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  154 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt-updates/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUpdateController',
    'methodName' => 'getUpdateById',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  155 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt-updates/all',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUpdateController',
    'methodName' => 'listAllUpdates',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  156 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/topics/generate/{limit:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\TopicController',
    'methodName' => 'generateTopics',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  157 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/topics',
    'class' => 'V3\\App\\Controllers\\Explore\\TopicController',
    'methodName' => 'fetchSyllabusAndTopics',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  158 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt/study/categories',
    'class' => 'V3\\App\\Controllers\\Explore\\StudyCategoryController',
    'methodName' => 'storeCategory',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  159 => 
  array (
    'method' => 'PUT',
    'path' => '/public/cbt/study/categories/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\StudyCategoryController',
    'methodName' => 'updateCategory',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  160 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/study/categories',
    'class' => 'V3\\App\\Controllers\\Explore\\StudyCategoryController',
    'methodName' => 'getAllCategories',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  161 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/study/courses/{course_id:\\d+}/categories',
    'class' => 'V3\\App\\Controllers\\Explore\\StudyCategoryController',
    'methodName' => 'getCategoriesByCourseId',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  162 => 
  array (
    'method' => 'DELETE',
    'path' => '/public/cbt/study/categories/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\StudyCategoryController',
    'methodName' => 'deleteCategory',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
      2 => 'role:admin',
    ),
  ),
  163 => 
  array (
    'method' => 'POST',
    'path' => '/public/learning/cohorts/{cohort_id}/discussions',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortDiscussionController',
    'methodName' => 'createDiscussion',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  164 => 
  array (
    'method' => 'POST',
    'path' => '/public/learning/cohorts/{cohort_id}/discussions/{discussion_id}/posts',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortDiscussionController',
    'methodName' => 'createPost',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  165 => 
  array (
    'method' => 'PUT',
    'path' => '/public/learning/cohorts/{cohort_id}/discussions/{discussion_id}',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortDiscussionController',
    'methodName' => 'updateDiscussion',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  166 => 
  array (
    'method' => 'PUT',
    'path' => '/public/learning/cohorts/{cohort_id}/discussions/{discussion_id}/posts/{post_id}',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortDiscussionController',
    'methodName' => 'updatePost',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  167 => 
  array (
    'method' => 'POST',
    'path' => '/public/learning/cohorts/{cohort_id}/posts/{post_id}/like',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortDiscussionController',
    'methodName' => 'likePost',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  168 => 
  array (
    'method' => 'POST',
    'path' => '/public/learning/cohorts/{cohort_id}/posts/{post_id}/unlike',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortDiscussionController',
    'methodName' => 'unlikePost',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  169 => 
  array (
    'method' => 'POST',
    'path' => '/public/learning/cohorts/{cohort_id}/discussions/{discussion_id}/like',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortDiscussionController',
    'methodName' => 'likeDiscussion',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  170 => 
  array (
    'method' => 'POST',
    'path' => '/public/learning/cohorts/{cohort_id}/discussions/{discussion_id}/unlike',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortDiscussionController',
    'methodName' => 'unlikeDiscussion',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  171 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/cohorts/{cohort_id}/discussions',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortDiscussionController',
    'methodName' => 'getDiscussions',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  172 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/cohorts/discussions/{discussion_id}',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortDiscussionController',
    'methodName' => 'getDiscussionById',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  173 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/discussions/{discussion_id}/posts',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortDiscussionController',
    'methodName' => 'getPosts',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  174 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/posts/{post_id}/replies',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortDiscussionController',
    'methodName' => 'getPostReplies',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  175 => 
  array (
    'method' => 'DELETE',
    'path' => '/public/learning/cohorts/{cohort_id}/discussions/{discussion_id}',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortDiscussionController',
    'methodName' => 'deleteDiscussion',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  176 => 
  array (
    'method' => 'DELETE',
    'path' => '/public/learning/cohorts/{cohort_id}/posts/{post_id}',
    'class' => 'V3\\App\\Controllers\\Explore\\CourseCohortDiscussionController',
    'methodName' => 'deletePost',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  177 => 
  array (
    'method' => 'POST',
    'path' => '/public/news/categories',
    'class' => 'V3\\App\\Controllers\\Explore\\NewsCategoryController',
    'methodName' => 'addCategory',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  178 => 
  array (
    'method' => 'GET',
    'path' => '/public/news/categories',
    'class' => 'V3\\App\\Controllers\\Explore\\NewsCategoryController',
    'methodName' => 'getCategories',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  179 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/programs',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramContentController',
    'methodName' => 'getProgramsWithCourses',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  180 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/programs/content',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramContentController',
    'methodName' => 'getProgramsWithCoursesContent',
    'middleware' => 
    array (
    ),
  ),
  181 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/cohorts/{cohort_id}',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramContentController',
    'methodName' => 'getActiveCohortByCourse',
    'middleware' => 
    array (
    ),
  ),
  182 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/cohorts/{cohort_id}/lessons',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramContentController',
    'methodName' => 'cohortLessons',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  183 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/cohorts/{cohort_id}/lessons/v2',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramContentController',
    'methodName' => 'cohortLessonsV2',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  184 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/cohorts/{cohort_id}/profiles/{profile_id}/lessons',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramContentController',
    'methodName' => 'getCohortLessonsWithSubmission',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  185 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/lessons/{lesson_id}',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramContentController',
    'methodName' => 'getCohortLessonById',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  186 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/lessons/{lesson_id}/quizzes',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramContentController',
    'methodName' => 'courseLessonQuizzes',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  187 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/profiles/{profile_id}/stats',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramContentController',
    'methodName' => 'getUserLearningStats',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  188 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/cohorts/{cohort_id}/profiles/{profile_id}/lesson-performance',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramContentController',
    'methodName' => 'getLessonPerformanceByCohortAndProfile',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  189 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/cohorts/{cohort_id}/profiles/{profile_id}/leaderboard',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramContentController',
    'methodName' => 'getLeaderboardByCohortAndProfile',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  190 => 
  array (
    'method' => 'GET',
    'path' => '/public/learning/profiles/{profile_id}/upcoming-cohorts/{slug}',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramContentController',
    'methodName' => 'upcomingCohorts',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  191 => 
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
  192 => 
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
  193 => 
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
  194 => 
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
  195 => 
  array (
    'method' => 'GET',
    'path' => '/public/admissions',
    'class' => 'V3\\App\\Controllers\\Explore\\AdmissionController',
    'methodName' => 'getAdmissions',
    'middleware' => 
    array (
    ),
  ),
  196 => 
  array (
    'method' => 'POST',
    'path' => '/public/faqs',
    'class' => 'V3\\App\\Controllers\\Explore\\FaqsController',
    'methodName' => 'addFaq',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  197 => 
  array (
    'method' => 'PUT',
    'path' => '/public/faqs/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\FaqsController',
    'methodName' => 'updateFaq',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  198 => 
  array (
    'method' => 'GET',
    'path' => '/public/faqs',
    'class' => 'V3\\App\\Controllers\\Explore\\FaqsController',
    'methodName' => 'getAllFaqs',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  199 => 
  array (
    'method' => 'DELETE',
    'path' => '/public/faqs/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\FaqsController',
    'methodName' => 'deleteFaq',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  200 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt/challenges/leaderboard',
    'class' => 'V3\\App\\Controllers\\Explore\\LeaderboardController',
    'methodName' => 'storeLeaderboardData',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  201 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/challenges/leaderboard/{challenge_id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\LeaderboardController',
    'methodName' => 'getLeaderboardByChallenge',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  202 => 
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
  203 => 
  array (
    'method' => 'POST',
    'path' => '/public/learn/programs/cohorts/{cohort_id}/lessons',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramCourseCohortLessonController',
    'methodName' => 'addLesson',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  204 => 
  array (
    'method' => 'POST',
    'path' => '/public/learn/programs/cohorts/{cohort_id}/lessons/{lesson_id}',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramCourseCohortLessonController',
    'methodName' => 'updateLesson',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  205 => 
  array (
    'method' => 'PUT',
    'path' => '/public/learn/programs/cohorts/lessons/{lesson_id}/status',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramCourseCohortLessonController',
    'methodName' => 'updateStatus',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  206 => 
  array (
    'method' => 'GET',
    'path' => '/public/learn/programs/cohorts/{cohort_id}/lessons',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramCourseCohortLessonController',
    'methodName' => 'getLessons',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  207 => 
  array (
    'method' => 'GET',
    'path' => '/public/learn/programs/cohorts/lessons/{lesson_id}/quiz',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramCourseCohortLessonController',
    'methodName' => 'getLessonQuiz',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  208 => 
  array (
    'method' => 'DELETE',
    'path' => '/public/learn/programs/cohorts/lessons/{lesson_id}',
    'class' => 'V3\\App\\Controllers\\Explore\\ProgramCourseCohortLessonController',
    'methodName' => 'deleteLesson',
    'middleware' => 
    array (
      0 => 'api',
      1 => 'auth',
    ),
  ),
  209 => 
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
  210 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt/users/google',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUserController',
    'methodName' => 'bootstrapWithGoogleToken',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  211 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt/users/signup',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUserController',
    'methodName' => 'signupWithEmail',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  212 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt/users/login',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUserController',
    'methodName' => 'login',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  213 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt/users/{id:\\d+}/fcm-token',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUserController',
    'methodName' => 'upsertFcmToken',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  214 => 
  array (
    'method' => 'PUT',
    'path' => '/public/cbt/users/{id:\\d+}/phone',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUserController',
    'methodName' => 'updatePhone',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  215 => 
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
  216 => 
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
  217 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/users/{user_id}/profiles',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUserController',
    'methodName' => 'getProfilesByUserId',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  218 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt/users/{user_id}/profiles',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUserController',
    'methodName' => 'createProfile',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  219 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt/users/forgot-password',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUserController',
    'methodName' => 'forgotPassword',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  220 => 
  array (
    'method' => 'POST',
    'path' => '/public/cbt/users/reset-password',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtUserController',
    'methodName' => 'resetPassword',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
);