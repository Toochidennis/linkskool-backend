<?php

return array(
  0 =>
  array(
    'method' => 'GET',
    'path' => '/portal/dashboard/admin',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AcademicOverviewController',
    'methodName' => 'adminOverview',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  1 =>
  array(
    'method' => 'GET',
    'path' => '/portal/dashboard/staff/{teacher_id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AcademicOverviewController',
    'methodName' => 'teacherOverview',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:staff',
    ),
  ),
  2 =>
  array(
    'method' => 'GET',
    'path' => '/portal/dashboard/student',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AcademicOverviewController',
    'methodName' => 'studentOverview',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:student',
    ),
  ),
  3 =>
  array(
    'method' => 'POST',
    'path' => '/portal/courses/{course_id:\\d+}/attendance',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AttendanceController',
    'methodName' => 'addCourseAttendance',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  4 =>
  array(
    'method' => 'POST',
    'path' => '/portal/classes/{class_id:\\d+}/attendance',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AttendanceController',
    'methodName' => 'addClassAttendance',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  5 =>
  array(
    'method' => 'GET',
    'path' => '/portal/classes/{class_id:\\d+}/attendance/single',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AttendanceController',
    'methodName' => 'getSingleClassAttendance',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  6 =>
  array(
    'method' => 'GET',
    'path' => '/portal/classes/{class_id:\\d+}/attendance',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AttendanceController',
    'methodName' => 'getAllClassAttendance',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  7 =>
  array(
    'method' => 'GET',
    'path' => '/portal/courses/{course_id:\\d+}/attendance/single',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AttendanceController',
    'methodName' => 'getSingleCourseAttendance',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  8 =>
  array(
    'method' => 'GET',
    'path' => '/portal/courses/{course_id:\\d+}/attendance',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AttendanceController',
    'methodName' => 'getAllCourseAttendance',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  9 =>
  array(
    'method' => 'GET',
    'path' => '/portal/attendance/history',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AttendanceController',
    'methodName' => 'getAttendanceHistory',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  10 =>
  array(
    'method' => 'GET',
    'path' => '/portal/attendance/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AttendanceController',
    'methodName' => 'getAttendanceDetails',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  11 =>
  array(
    'method' => 'POST',
    'path' => '/portal/classes',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\ClassController',
    'methodName' => 'addClass',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  12 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/classes/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\ClassController',
    'methodName' => 'updateClass',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  13 =>
  array(
    'method' => 'GET',
    'path' => '/portal/classes',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\ClassController',
    'methodName' => 'getClasses',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  14 =>
  array(
    'method' => 'DELETE',
    'path' => '/portal/classes/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\ClassController',
    'methodName' => 'deleteClass',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  15 =>
  array(
    'method' => 'POST',
    'path' => '/portal/course-assignments',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\CourseAssignmentController',
    'methodName' => 'storeCourseAssignment',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  16 =>
  array(
    'method' => 'GET',
    'path' => '/portal/course-assignments',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\CourseAssignmentController',
    'methodName' => 'getCourseAssignments',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  17 =>
  array(
    'method' => 'POST',
    'path' => '/portal/courses',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\CourseController',
    'methodName' => 'addCourse',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  18 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/courses/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\CourseController',
    'methodName' => 'updateCourse',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  19 =>
  array(
    'method' => 'GET',
    'path' => '/portal/courses',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\CourseController',
    'methodName' => 'getCourses',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  20 =>
  array(
    'method' => 'DELETE',
    'path' => '/portal/courses',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\CourseController',
    'methodName' => 'deleteCourse',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  21 =>
  array(
    'method' => 'POST',
    'path' => '/portal/feeds',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\FeedController',
    'methodName' => 'addContent',
    'middleware' =>
    array(
      0 => 'auth',
    ),
  ),
  22 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/feeds/{news_id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\FeedController',
    'methodName' => 'updateContent',
    'middleware' =>
    array(
      0 => 'auth',
    ),
  ),
  23 =>
  array(
    'method' => 'GET',
    'path' => '/portal/feeds',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\FeedController',
    'methodName' => 'getContents',
    'middleware' =>
    array(
      0 => 'auth',
    ),
  ),
  24 =>
  array(
    'method' => 'DELETE',
    'path' => '/portal/feeds/{news_id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\FeedController',
    'methodName' => 'deleteContent',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  25 =>
  array(
    'method' => 'POST',
    'path' => '/portal/levels',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\LevelController',
    'methodName' => 'addLevel',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  26 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/levels/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\LevelController',
    'methodName' => 'updateLevel',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  27 =>
  array(
    'method' => 'GET',
    'path' => '/portal/levels',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\LevelController',
    'methodName' => 'getLevels',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  28 =>
  array(
    'method' => 'DELETE',
    'path' => '/portal/levels/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\LevelController',
    'methodName' => 'deleteLevel',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  29 =>
  array(
    'method' => 'GET',
    'path' => '/portal/schools',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\SchoolController',
    'methodName' => 'getSchools',
    'middleware' =>
    array(),
  ),
  30 =>
  array(
    'method' => 'POST',
    'path' => '/portal/skill-behavior',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\SkillBehaviorController',
    'methodName' => 'store',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  31 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/skill-behavior/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\SkillBehaviorController',
    'methodName' => 'update',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  32 =>
  array(
    'method' => 'GET',
    'path' => '/portal/skill-behavior',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\SkillBehaviorController',
    'methodName' => 'get',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  33 =>
  array(
    'method' => 'DELETE',
    'path' => '/portal/skill-behavior{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\SkillBehaviorController',
    'methodName' => 'delete',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  34 =>
  array(
    'method' => 'POST',
    'path' => '/portal/staff',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\StaffController',
    'methodName' => 'addStaff',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  35 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/staff/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\StaffController',
    'methodName' => 'updateStaff',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  36 =>
  array(
    'method' => 'GET',
    'path' => '/portal/staff',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\StaffController',
    'methodName' => 'getStaff',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  37 =>
  array(
    'method' => 'DELETE',
    'path' => '/portal/staff/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\StaffController',
    'methodName' => 'deleteStaff',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  38 =>
  array(
    'method' => 'POST',
    'path' => '/portal/students',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\StudentController',
    'methodName' => 'addStudent',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  39 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/students/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\StudentController',
    'methodName' => 'updateStudentRecord',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  40 =>
  array(
    'method' => 'GET',
    'path' => '/portal/students',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\StudentController',
    'methodName' => 'getAllStudents',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  41 =>
  array(
    'method' => 'GET',
    'path' => '/portal/students/{class_id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\StudentController',
    'methodName' => 'getStudentsByClass',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  42 =>
  array(
    'method' => 'DELETE',
    'path' => '/portal/students/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\StudentController',
    'methodName' => 'deleteStudent',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  43 =>
  array(
    'method' => 'POST',
    'path' => '/portal/auth/login',
    'class' => 'V3\\App\\Controllers\\Portal\\AuthController',
    'methodName' => 'handleAuthRequest',
    'middleware' =>
    array(
      0 => 'api',
    ),
  ),
  44 =>
  array(
    'method' => 'POST',
    'path' => '/portal/elearning/assignment',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\AssignmentController',
    'methodName' => 'store',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  45 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/elearning/assignment/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\AssignmentController',
    'methodName' => 'update',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  46 =>
  array(
    'method' => 'POST',
    'path' => '/portal/students/{student_id:\\d+}/assignment-submissions',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\AssignmentSubmissionController',
    'methodName' => 'submit',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:student',
    ),
  ),
  47 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/elearning/assignment/mark',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\AssignmentSubmissionController',
    'methodName' => 'markAssignment',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  48 =>
  array(
    'method' => 'GET',
    'path' => '/portal/elearning/assignment/{id:\\d+}/submissions',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\AssignmentSubmissionController',
    'methodName' => 'getSubmissions',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  49 =>
  array(
    'method' => 'GET',
    'path' => '/portal/students/{student_id:\\d+}/assignment-submissions',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\AssignmentSubmissionController',
    'methodName' => 'getMarkedAssignment',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:student',
    ),
  ),
  50 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/elearning/assignment/{content_id:\\d+}/publish',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\AssignmentSubmissionController',
    'methodName' => 'publishAssignment',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  51 =>
  array(
    'method' => 'POST',
    'path' => '/portal/elearning/{content_id:\\d+}/comments',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\ContentCommentController',
    'methodName' => 'store',
    'middleware' =>
    array(
      0 => 'auth',
    ),
  ),
  52 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/elearning/comments/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\ContentCommentController',
    'methodName' => 'update',
    'middleware' =>
    array(
      0 => 'auth',
    ),
  ),
  53 =>
  array(
    'method' => 'GET',
    'path' => '/portal/elearning/{content_id:\\d+}/comments',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\ContentCommentController',
    'methodName' => 'getCommentsByContentId',
    'middleware' =>
    array(
      0 => 'auth',
    ),
  ),
  54 =>
  array(
    'method' => 'GET',
    'path' => '/portal/elearning/{syllabus_id:\\d+}/comments/streams',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\ContentCommentController',
    'methodName' => 'streams',
    'middleware' =>
    array(
      0 => 'auth',
    ),
  ),
  55 =>
  array(
    'method' => 'DELETE',
    'path' => '/portal/elearning/comments/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\ContentCommentController',
    'methodName' => 'delete',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  56 =>
  array(
    'method' => 'GET',
    'path' => '/portal/elearning/overview',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\ContentManagerController',
    'methodName' => 'getDashboard',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  57 =>
  array(
    'method' => 'GET',
    'path' => '/portal/elearning/staff/{teacher_id:\\d+}/overview',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\ContentManagerController',
    'methodName' => 'staffDashboardSummary',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:staff',
    ),
  ),
  58 =>
  array(
    'method' => 'GET',
    'path' => '/portal/elearning/syllabus/{syllabus_id:\\d+}/contents',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\ContentManagerController',
    'methodName' => 'getAllContents',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  59 =>
  array(
    'method' => 'GET',
    'path' => '/portal/elearning/contents/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\ContentManagerController',
    'methodName' => 'getContentById',
    'middleware' =>
    array(
      0 => 'auth',
    ),
  ),
  60 =>
  array(
    'method' => 'DELETE',
    'path' => '/portal/elearning/contents/{content_id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\ContentManagerController',
    'methodName' => 'delete',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  61 =>
  array(
    'method' => 'POST',
    'path' => '/portal/elearning/material',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\MaterialController',
    'methodName' => 'store',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  62 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/elearning/material/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\MaterialController',
    'methodName' => 'update',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  63 =>
  array(
    'method' => 'POST',
    'path' => '/portal/elearning/quiz',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\QuizController',
    'methodName' => 'store',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  64 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/elearning/quiz',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\QuizController',
    'methodName' => 'update',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  65 =>
  array(
    'method' => 'DELETE',
    'path' => '/portal/elearning/quiz/{content_id:\\d+}/{question_id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\QuizController',
    'methodName' => 'delete',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  66 =>
  array(
    'method' => 'POST',
    'path' => '/portal/students/{student_id:\\d+}/quiz-submissions',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\QuizSubmissionController',
    'methodName' => 'submit',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:student',
    ),
  ),
  67 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/elearning/quiz/mark',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\QuizSubmissionController',
    'methodName' => 'markQuiz',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  68 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/elearning/quiz/{content_id:\\d+}/publish',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\QuizSubmissionController',
    'methodName' => 'publishQuiz',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  69 =>
  array(
    'method' => 'GET',
    'path' => '/portal/elearning/quiz/{id:\\d+}/submissions',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\QuizSubmissionController',
    'methodName' => 'getSubmissions',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  70 =>
  array(
    'method' => 'GET',
    'path' => '/portal/students/{student_id:\\d+}/quiz-submissions',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\QuizSubmissionController',
    'methodName' => 'getMarkedQuiz',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:student',
    ),
  ),
  71 =>
  array(
    'method' => 'GET',
    'path' => '/portal/students/{id:\\d+}/elearning/dashboard',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\StudentContentManagerController',
    'methodName' => 'dashboard',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:student',
    ),
  ),
  72 =>
  array(
    'method' => 'POST',
    'path' => '/portal/elearning/syllabus',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\SyllabusController',
    'methodName' => 'store',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  73 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/elearning/syllabus/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\SyllabusController',
    'methodName' => 'update',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  74 =>
  array(
    'method' => 'GET',
    'path' => '/portal/elearning/syllabus',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\SyllabusController',
    'methodName' => 'get',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  75 =>
  array(
    'method' => 'GET',
    'path' => '/portal/elearning/syllabus/staff',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\SyllabusController',
    'methodName' => 'getByStaff',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:staff',
    ),
  ),
  76 =>
  array(
    'method' => 'DELETE',
    'path' => '/portal/elearning/syllabus/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\SyllabusController',
    'methodName' => 'delete',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  77 =>
  array(
    'method' => 'POST',
    'path' => '/portal/elearning/topic',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\TopicController',
    'methodName' => 'store',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  78 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/elearning/topic/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\TopicController',
    'methodName' => 'update',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  79 =>
  array(
    'method' => 'GET',
    'path' => '/portal/elearning/syllabus/{syllabus_id:\\d+}/topics',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\TopicController',
    'methodName' => 'get',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  80 =>
  array(
    'method' => 'POST',
    'path' => '/portal/payments/accounts',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\AccountController',
    'methodName' => 'store',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  81 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/payments/accounts/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\AccountController',
    'methodName' => 'update',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  82 =>
  array(
    'method' => 'GET',
    'path' => '/portal/payments/accounts',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\AccountController',
    'methodName' => 'get',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  83 =>
  array(
    'method' => 'DELETE',
    'path' => '/portal/payments/accounts/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\AccountController',
    'methodName' => 'delete',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  84 =>
  array(
    'method' => 'POST',
    'path' => '/portal/payments/expenditure',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\ExpenditureController',
    'methodName' => 'store',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  85 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/payments/expenditure/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\ExpenditureController',
    'methodName' => 'update',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  86 =>
  array(
    'method' => 'GET',
    'path' => '/portal/payments/expenditure/report/generate',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\ExpenditureController',
    'methodName' => 'generateReport',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  87 =>
  array(
    'method' => 'POST',
    'path' => '/portal/payments/fee-names',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\FeeTypeController',
    'methodName' => 'store',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  88 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/payments/fee-names/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\FeeTypeController',
    'methodName' => 'update',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  89 =>
  array(
    'method' => 'GET',
    'path' => '/portal/payments/fee-names',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\FeeTypeController',
    'methodName' => 'get',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  90 =>
  array(
    'method' => 'DELETE',
    'path' => '/portal/payments/fee-names/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\FeeTypeController',
    'methodName' => 'delete',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  91 =>
  array(
    'method' => 'GET',
    'path' => '/portal/payments/income/report/generate',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\IncomeController',
    'methodName' => 'generateReport',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  92 =>
  array(
    'method' => 'POST',
    'path' => '/portal/payments/invoices',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\NextTermFeeController',
    'methodName' => 'upsert',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  93 =>
  array(
    'method' => 'GET',
    'path' => '/portal/payments/invoices',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\NextTermFeeController',
    'methodName' => 'get',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  94 =>
  array(
    'method' => 'GET',
    'path' => '/portal/payments/dashboard/summary',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\PaymentDashboardController',
    'methodName' => 'getDashboardSummary',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  95 =>
  array(
    'method' => 'GET',
    'path' => '/portal/payments/invoices/paid',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\PaymentDashboardController',
    'methodName' => 'getPaidInvoices',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  96 =>
  array(
    'method' => 'GET',
    'path' => '/portal/payments/invoices/unpaid',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\PaymentDashboardController',
    'methodName' => 'getUnpaidInvoices',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  97 =>
  array(
    'method' => 'POST',
    'path' => '/portal/students/{student_id:\\d+}/make-payment',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\StudentPaymentController',
    'methodName' => 'makePayment',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:student',
    ),
  ),
  98 =>
  array(
    'method' => 'GET',
    'path' => '/portal/students/{student_id:\\d+}/financial-records',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\StudentPaymentController',
    'methodName' => 'getFinancialRecords',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:student',
    ),
  ),
  99 =>
  array(
    'method' => 'POST',
    'path' => '/portal/payments/vendors',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\VendorController',
    'methodName' => 'store',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  100 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/payments/vendors/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\VendorController',
    'methodName' => 'update',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  101 =>
  array(
    'method' => 'GET',
    'path' => '/portal/payments/vendors',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\VendorController',
    'methodName' => 'get',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  102 =>
  array(
    'method' => 'DELETE',
    'path' => '/portal/payments/vendors/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\VendorController',
    'methodName' => 'delete',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  103 =>
  array(
    'method' => 'GET',
    'path' => '/portal/payments/vendors/{id:\\d+}/transactions/{year:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\VendorController',
    'methodName' => 'transactions',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  104 =>
  array(
    'method' => 'GET',
    'path' => '/portal/payments/vendors/{id:\\d+}/transactions/annual',
    'class' => 'V3\\App\\Controllers\\Portal\\Payments\\VendorController',
    'methodName' => 'annualHistory',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  105 =>
  array(
    'method' => 'POST',
    'path' => '/portal/assessments',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\AssessmentController',
    'methodName' => 'addAssessments',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  106 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/assessments/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\AssessmentController',
    'methodName' => 'updateAssessment',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  107 =>
  array(
    'method' => 'GET',
    'path' => '/portal/assessments',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\AssessmentController',
    'methodName' => 'getAllAssessments',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  108 =>
  array(
    'method' => 'GET',
    'path' => '/portal/assessments/{level_id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\AssessmentController',
    'methodName' => 'getAssessmentByLevel',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  109 =>
  array(
    'method' => 'DELETE',
    'path' => '/portal/assessments/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\AssessmentController',
    'methodName' => 'deleteAssessment',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  110 =>
  array(
    'method' => 'GET',
    'path' => '/portal/classes/{class_id:\\d+}/courses/{course_id}/results',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\ClassCourseResultController',
    'methodName' => 'getCourseResultsForClass',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  111 =>
  array(
    'method' => 'GET',
    'path' => '/portal/classes/{class_id:\\d+}/students-result',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\ClassCourseResultController',
    'methodName' => 'getStudentsResultForClass',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  112 =>
  array(
    'method' => 'GET',
    'path' => '/portal/classes/{class_id:\\d+}/composite-result',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\ClassCourseResultController',
    'methodName' => 'getClassCompositeResult',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  113 =>
  array(
    'method' => 'GET',
    'path' => '/portal/levels/result/performance',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\ClassCourseResultController',
    'methodName' => 'getAllLevelsPerformance',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  114 =>
  array(
    'method' => 'POST',
    'path' => '/portal/students/{student_id:\\d+}/course-registration',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\CourseRegistrationController',
    'methodName' => 'registerStudentCourses',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  115 =>
  array(
    'method' => 'POST',
    'path' => '/portal/classes/{class_id:\\d+}/course-registrations',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\CourseRegistrationController',
    'methodName' => 'registerClassCourses',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  116 =>
  array(
    'method' => 'POST',
    'path' => '/portal/classes/{class_id:\\d+}/course-registrations/duplicate',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\CourseRegistrationController',
    'methodName' => 'duplicateLastTermRegistrations',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  117 =>
  array(
    'method' => 'GET',
    'path' => '/portal/course-registrations/terms',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\CourseRegistrationController',
    'methodName' => 'getClassRegistrationTerms',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  118 =>
  array(
    'method' => 'GET',
    'path' => '/portal/classes/{class_id:\\d+}/course-registrations/history',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\CourseRegistrationController',
    'methodName' => 'getClassRegistrationHistory',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  119 =>
  array(
    'method' => 'GET',
    'path' => '/portal/classes/{class_id:\\d+}/registered-courses',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\CourseRegistrationController',
    'methodName' => 'getRegisteredCoursesForClass',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  120 =>
  array(
    'method' => 'GET',
    'path' => '/portal/classes/{class_id:\\d+}/course-registrations/average-scores',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\CourseRegistrationController',
    'methodName' => 'getRegisteredCoursesWithAvgScores',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  121 =>
  array(
    'method' => 'GET',
    'path' => '/portal/courses/{course_id:\\d+}/students',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\CourseRegistrationController',
    'methodName' => 'getStudentsForCourseInClass',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  122 =>
  array(
    'method' => 'GET',
    'path' => '/portal/students/{student_id:\\d+}/registered-courses',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\CourseRegistrationController',
    'methodName' => 'getCoursesRegisteredByStudent',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  123 =>
  array(
    'method' => 'GET',
    'path' => '/portal/classes/{class_id:\\d+}/registered-students',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\CourseRegistrationController',
    'methodName' => 'getStudentRegistrationStatusInClass',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  124 =>
  array(
    'method' => 'POST',
    'path' => '/portal/grades',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\GradeController',
    'methodName' => 'addGrades',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  125 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/grades/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\GradeController',
    'methodName' => 'updateGrade',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  126 =>
  array(
    'method' => 'GET',
    'path' => '/portal/grades',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\GradeController',
    'methodName' => 'getGrades',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  127 =>
  array(
    'method' => 'DELETE',
    'path' => '/portal/grades/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\GradeController',
    'methodName' => 'deleteGrade',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
    ),
  ),
  128 =>
  array(
    'method' => 'POST',
    'path' => '/portal/students/result/comment',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\ResultCommentController',
    'methodName' => 'store',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  129 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/students/result/comment/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\ResultCommentController',
    'methodName' => 'update',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  130 =>
  array(
    'method' => 'PUT',
    'path' => '/portal/result/class-result',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\ResultController',
    'methodName' => 'updateResult',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  131 =>
  array(
    'method' => 'GET',
    'path' => '/portal/students/{student_id:\\d+}/result/{term:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\StudentResultController',
    'methodName' => 'getStudentTermResult',
    'middleware' =>
    array(
      0 => 'auth',
    ),
  ),
  132 =>
  array(
    'method' => 'GET',
    'path' => '/portal/students/{student_id:\\d+}/result/annual',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\StudentResultController',
    'methodName' => 'getStudentAnnualResult',
    'middleware' =>
    array(
      0 => 'auth',
    ),
  ),
  133 =>
  array(
    'method' => 'GET',
    'path' => '/portal/students/{id:\\d+}/result-terms',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\StudentResultController',
    'methodName' => 'getResultTerms',
    'middleware' =>
    array(
      0 => 'auth',
    ),
  ),
  134 =>
  array(
    'method' => 'POST',
    'path' => '/portal/students/skill-behavior',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\StudentSkillBehaviorController',
    'methodName' => 'upsertSkills',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
  135 =>
  array(
    'method' => 'GET',
    'path' => '/portal/classes/{class_id:\\d+}/skill-behavior',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\StudentSkillBehaviorController',
    'methodName' => 'getStudentsSkillBehavior',
    'middleware' =>
    array(
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
  ),
);
