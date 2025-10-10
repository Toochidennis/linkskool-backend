<?php return array (
  0 => 
  array (
    'method' => 'GET',
    'path' => '/portal/courses/{course_id:\\d+}/attendance',
    'class' => 'V3\\App\\Controllers\\Portal\\Academics\\AttendanceController',
    'methodName' => 'getAllCourseAttendance',
    'middleware' => 
    array (
      0 => 'auth',
    ),
  ),
);