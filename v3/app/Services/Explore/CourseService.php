<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Portal\Academics\Course;

class CourseService
{
    private Course $course;
    public function __construct(\PDO $pdo)
    {
        $this->course = new Course($pdo);
    }

    public function createCourse(array $data): int
    {
        return $this->course->insert($data);
    }

    public function updateCourse(int $id, array $data): bool
    {
        unset($data['id']);
        return $this->course
            ->where('id', '=', $id)
            ->update($data);
    }

    public function getAllCourses(): array
    {
        return $this->course->get();
    }

    public function deleteCourse(int $id): bool
    {
        return $this->course
            ->where('id', '=', $id)
            ->delete();
    }
}
