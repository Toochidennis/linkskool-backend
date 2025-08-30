<?php

namespace V3\App\Services\Portal\Academics;

use PDO;
use V3\App\Models\Portal\Academics\Course;

class CourseService
{
    private Course $course;

    public function __construct(PDO $pdo)
    {
        $this->course = new Course($pdo);
    }

    public function insertCourse(array $data)
    {
        return $this->course->insert($data);
    }

    public function fetchCourses()
    {
        return $this->course->get();
    }
}
