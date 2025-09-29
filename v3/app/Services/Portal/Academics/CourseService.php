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

    public function insertCourse(array $data): bool|int
    {
        return $this->course->insert($data);
    }

    public function updateCourse(array $data): bool|int
    {
        $payload = [
            'course_name' => $data['course_name'],
            'course_code' => $data['course_code']
        ];

        return $this->course
            ->where('id', $data['id'])
            ->update($payload);
    }

    public function fetchCourses()
    {
        return $this->course->orderBy('course_name')->get();
    }

    public function deleteCourse(int $id): bool|int
    {
        return $this->course
            ->where('id', $id)
            ->delete();
    }
}
