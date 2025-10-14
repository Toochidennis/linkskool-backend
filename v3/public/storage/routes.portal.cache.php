<?php return array (
  0 => 
  array (
    'method' => 'GET',
    'path' => '/portal/dashboard/admin',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AcademicOverviewController',
    'methodName' => 'adminOverview',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  1 => 
  array (
    'method' => 'GET',
    'path' => '/portal/dashboard/staff/{teacher_id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AcademicOverviewController',
    'methodName' => 'teacherOverview',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  2 => 
  array (
    'method' => 'GET',
    'path' => '/portal/dashboard/student',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AcademicOverviewController',
    'methodName' => 'studentOverview',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:student',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  3 => 
  array (
    'method' => 'POST',
    'path' => '/portal/courses/{course_id:\\d+}/attendance',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AttendanceController',
    'methodName' => 'addAttendance',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => 'course',
    ),
  ),
  4 => 
  array (
    'method' => 'POST',
    'path' => '/portal/classes/{class_id:\\d+}/attendance',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AttendanceController',
    'methodName' => 'addAttendance',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => 'class',
    ),
  ),
  5 => 
  array (
    'method' => 'GET',
    'path' => '/portal/classes/{class_id:\\d+}/attendance/single',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AttendanceController',
    'methodName' => 'getSingleClassAttendance',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  6 => 
  array (
    'method' => 'GET',
    'path' => '/portal/classes/{class_id:\\d+}/attendance',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AttendanceController',
    'methodName' => 'getAllClassAttendance',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  7 => 
  array (
    'method' => 'GET',
    'path' => '/portal/courses/{course_id:\\d+}/attendance/single',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AttendanceController',
    'methodName' => 'getSingleCourseAttendance',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  8 => 
  array (
    'method' => 'GET',
    'path' => '/portal/courses/{course_id:\\d+}/attendance',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AttendanceController',
    'methodName' => 'getAllCourseAttendance',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  9 => 
  array (
    'method' => 'GET',
    'path' => '/portal/attendance/history',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AttendanceController',
    'methodName' => 'getAttendanceHistory',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  10 => 
  array (
    'method' => 'GET',
    'path' => '/portal/attendance/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AttendanceController',
    'methodName' => 'getAttendanceDetails',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  11 => 
  array (
    'method' => 'POST',
    'path' => '/portal/classes',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\ClassController',
    'methodName' => 'addClass',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  12 => 
  array (
    'method' => 'PUT',
    'path' => '/portal/classes/{id:\\id+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\ClassController',
    'methodName' => 'updateClass',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  13 => 
  array (
    'method' => 'GET',
    'path' => '/portal/classes',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\ClassController',
    'methodName' => 'getClasses',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  14 => 
  array (
    'method' => 'DELETE',
    'path' => '/portal/classes/{id:\\id+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\ClassController',
    'methodName' => 'deleteClass',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  15 => 
  array (
    'method' => 'POST',
    'path' => '/portal/course-assignments',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\CourseAssignmentController',
    'methodName' => 'storeCourseAssignment',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  16 => 
  array (
    'method' => 'GET',
    'path' => '/portal/course-assignments',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\CourseAssignmentController',
    'methodName' => 'getCourseAssignments',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  17 => 
  array (
    'method' => 'POST',
    'path' => '/portal/courses',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\CourseController',
    'methodName' => 'addCourse',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  18 => 
  array (
    'method' => 'PUT',
    'path' => '/portal/courses/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\CourseController',
    'methodName' => 'updateCourse',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  19 => 
  array (
    'method' => 'GET',
    'path' => '/portal/courses',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\CourseController',
    'methodName' => 'getCourses',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  20 => 
  array (
    'method' => 'DELETE',
    'path' => '/portal/courses',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\CourseController',
    'methodName' => 'deleteCourse',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  21 => 
  array (
    'method' => 'POST',
    'path' => '/portal/feeds',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\FeedController',
    'methodName' => 'addContent',
    'middleware' => 
    array (
      0 => 'auth',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  22 => 
  array (
    'method' => 'PUT',
    'path' => '/portal/feeds/{news_id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\FeedController',
    'methodName' => 'updateContent',
    'middleware' => 
    array (
      0 => 'auth',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  23 => 
  array (
    'method' => 'GET',
    'path' => '/portal/feeds',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\FeedController',
    'methodName' => 'getContents',
    'middleware' => 
    array (
      0 => 'auth',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  24 => 
  array (
    'method' => 'DELETE',
    'path' => '/portal/feeds/{news_id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\FeedController',
    'methodName' => 'deleteContent',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  25 => 
  array (
    'method' => 'POST',
    'path' => '/portal/levels',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\LevelController',
    'methodName' => 'addLevel',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  26 => 
  array (
    'method' => 'PUT',
    'path' => '/portal/levels/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\LevelController',
    'methodName' => 'updateLevel',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  27 => 
  array (
    'method' => 'GET',
    'path' => '/portal/levels',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\LevelController',
    'methodName' => 'getLevels',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  28 => 
  array (
    'method' => 'DELETE',
    'path' => '/portal/levels/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\LevelController',
    'methodName' => 'deleteLevel',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  29 => 
  array (
    'method' => 'GET',
    'path' => '/portal/schools',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\SchoolController',
    'methodName' => 'getSchools',
    'middleware' => 
    array (
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  30 => 
  array (
    'method' => 'POST',
    'path' => '/portal/skill-behavior',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\SkillBehaviorController',
    'methodName' => 'store',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  31 => 
  array (
    'method' => 'PUT',
    'path' => '/portal/skill-behavior/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\SkillBehaviorController',
    'methodName' => 'update',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  32 => 
  array (
    'method' => 'GET',
    'path' => '/portal/skill-behavior',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\SkillBehaviorController',
    'methodName' => 'get',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  33 => 
  array (
    'method' => 'DELETE',
    'path' => '/portal/skill-behavior{id\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\SkillBehaviorController',
    'methodName' => 'delete',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  34 => 
  array (
    'method' => 'POST',
    'path' => '/portal/staff',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\StaffController',
    'methodName' => 'addStaff',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  35 => 
  array (
    'method' => 'PUT',
    'path' => '/portal/staff/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\StaffController',
    'methodName' => 'updateStaff',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  36 => 
  array (
    'method' => 'GET',
    'path' => '/portal/staff',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\StaffController',
    'methodName' => 'getStaff',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  37 => 
  array (
    'method' => 'DELETE',
    'path' => '/portal/staff/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\StaffController',
    'methodName' => 'deleteStaff',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  38 => 
  array (
    'method' => 'POST',
    'path' => '/portal/students',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\StudentController',
    'methodName' => 'addStudent',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  39 => 
  array (
    'method' => 'PUT',
    'path' => '/portal/students/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\StudentController',
    'methodName' => 'updateStudentRecord',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  40 => 
  array (
    'method' => 'GET',
    'path' => '/portal/students',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\StudentController',
    'methodName' => 'getAllStudents',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  41 => 
  array (
    'method' => 'GET',
    'path' => '/portal/students/{class_id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\StudentController',
    'methodName' => 'getStudentsByClass',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  42 => 
  array (
    'method' => 'DELETE',
    'path' => '/portal/students/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\StudentController',
    'methodName' => 'deleteStudent',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  43 => 
  array (
    'method' => 'POST',
    'path' => '/portal/auth/login',
    'class' => 'V3\\App\\Controllers\\Portal\\AuthController',
    'methodName' => 'handleAuthRequest',
    'middleware' => 
    array (
      0 => 'api',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  44 => 
  array (
    'method' => 'POST',
    'path' => '/portal/elearning/assignment',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\AssignmentController',
    'methodName' => 'store',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  45 => 
  array (
    'method' => 'PUT',
    'path' => '/portal/elearning/assignment/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\AssignmentController',
    'methodName' => 'update',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  46 => 
  array (
    'method' => 'POST',
    'path' => '/portal/students/{student_id:\\d+}/assignment-submissions',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\AssignmentSubmissionController',
    'methodName' => 'submit',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:student',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  47 => 
  array (
    'method' => 'PUT',
    'path' => '/portal/elearning/assignment/mark',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\AssignmentSubmissionController',
    'methodName' => 'markAssignment',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  48 => 
  array (
    'method' => 'GET',
    'path' => '/portal/elearning/assignment/{id:\\d+}/submissions',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\AssignmentSubmissionController',
    'methodName' => 'getSubmissions',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  49 => 
  array (
    'method' => 'GET',
    'path' => '/portal/students/{student_id:\\d+}/assignment-submissions',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\AssignmentSubmissionController',
    'methodName' => 'getMarkedAssignment',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:student',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  50 => 
  array (
    'method' => 'PUT',
    'path' => '/portal/elearning/assignment/{content_id:\\d+}/publish',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\AssignmentSubmissionController',
    'methodName' => 'publishAssignment',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  51 => 
  array (
    'method' => 'POST',
    'path' => '/portal/elearning/material',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\MaterialController',
    'methodName' => 'store',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  52 => 
  array (
    'method' => 'PUT',
    'path' => '/portal/elearning/material/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\MaterialController',
    'methodName' => 'update',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  53 => 
  array (
    'method' => 'POST',
    'path' => '/portal/elearning/quiz',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\QuizController',
    'methodName' => 'store',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  54 => 
  array (
    'method' => 'PUT',
    'path' => '/portal/elearning/quiz',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\QuizController',
    'methodName' => 'update',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  55 => 
  array (
    'method' => 'DELETE',
    'path' => '/portal/elearning/quiz/{content_id:\\d+}/{question_id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\QuizController',
    'methodName' => 'delete',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  56 => 
  array (
    'method' => 'POST',
    'path' => '/portal/students/{student_id:\\d+}/quiz-submissions',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\QuizSubmissionController',
    'methodName' => 'submit',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:student',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  57 => 
  array (
    'method' => 'PUT',
    'path' => '/portal/elearning/quiz/mark',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\QuizSubmissionController',
    'methodName' => 'markQuiz',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  58 => 
  array (
    'method' => 'PUT',
    'path' => '/portal/elearning/quiz/{content_id:\\d+}/publish',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\QuizSubmissionController',
    'methodName' => 'publishQuiz',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  59 => 
  array (
    'method' => 'GET',
    'path' => '/portal/elearning/quiz/{id:\\d+}/submissions',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\QuizSubmissionController',
    'methodName' => 'getSubmissions',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  60 => 
  array (
    'method' => 'GET',
    'path' => '/portal/students/{student_id:\\d+}/quiz-submissions',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\QuizSubmissionController',
    'methodName' => 'getMarkedQuiz',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:student',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  61 => 
  array (
    'method' => 'POST',
    'path' => '/portal/elearning/syllabus',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\SyllabusController',
    'methodName' => 'store',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  62 => 
  array (
    'method' => 'PUT',
    'path' => '/portal/elearning/syllabus/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\SyllabusController',
    'methodName' => 'update',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  63 => 
  array (
    'method' => 'GET',
    'path' => '/portal/elearning/syllabus',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\SyllabusController',
    'methodName' => 'get',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  64 => 
  array (
    'method' => 'GET',
    'path' => '/portal/elearning/syllabus/staff',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\SyllabusController',
    'methodName' => 'getByStaff',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  65 => 
  array (
    'method' => 'DELETE',
    'path' => '/portal/elearning/syllabus/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\SyllabusController',
    'methodName' => 'delete',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  66 => 
  array (
    'method' => 'POST',
    'path' => '/portal/elearning/topic',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\TopicController',
    'methodName' => 'store',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  67 => 
  array (
    'method' => 'PUT',
    'path' => '/portal/elearning/topic/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\TopicController',
    'methodName' => 'update',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  68 => 
  array (
    'method' => 'GET',
    'path' => '/portal/elearning/syllabus/{syllabus_id:\\d+}/topics',
    'class' => 'V3\\App\\Controllers\\Portal\\ELearning\\TopicController',
    'methodName' => 'get',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
      2 => 'role:staff',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  69 => 
  array (
    'method' => 'POST',
    'path' => '/portal/assessments',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\AssessmentController',
    'methodName' => 'addAssessments',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  70 => 
  array (
    'method' => 'PUT',
    'path' => '/portal/assessments/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\AssessmentController',
    'methodName' => 'updateAssessment',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  71 => 
  array (
    'method' => 'GET',
    'path' => '/portal/assessments',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\AssessmentController',
    'methodName' => 'getAllAssessments',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  72 => 
  array (
    'method' => 'GET',
    'path' => '/portal/assessments/{level_id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\AssessmentController',
    'methodName' => 'getAssessmentByLevel',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  73 => 
  array (
    'method' => 'DELETE',
    'path' => '/portal/assessments/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\AssessmentController',
    'methodName' => 'deleteAssessment',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  74 => 
  array (
    'method' => 'POST',
    'path' => '/portal/grades',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\GradeController',
    'methodName' => 'addGrades',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  75 => 
  array (
    'method' => 'PUT',
    'path' => '/portal/grades/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\GradeController',
    'methodName' => 'updateGrade',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  76 => 
  array (
    'method' => 'GET',
    'path' => '/portal/grades',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\GradeController',
    'methodName' => 'getGrades',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
  77 => 
  array (
    'method' => 'DELETE',
    'path' => '/portal/grades/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Portal\\Results\\GradeController',
    'methodName' => 'deleteGrade',
    'middleware' => 
    array (
      0 => 'auth',
      1 => 'role:admin',
    ),
    'meta' => 
    array (
      'type' => '',
    ),
  ),
);