<?php

namespace V3\App\Services\Explore\Classroom;

use V3\App\Models\Explore\Classroom\ClassroomCourseEnrollment;

class ClassroomCourseEnrollmentService
{
    protected ClassroomCourseEnrollment $model;

    public function __construct(\PDO $pdo)
    {
        $this->model = new ClassroomCourseEnrollment($pdo);
    }

}
