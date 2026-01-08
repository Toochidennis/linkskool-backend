<?php

namespace V3\App\Services\Explore;

class CourseContentService
{
    /**
     * Lesson data grouped first by category, then by course.
     *
     * @var array<int, array<int, array<int, array<string, mixed>>>>
     */
    private array $lessonsByCategoryAndCourse;

    public function __construct()
    {
        $this->seedLessons();
    }

    /**
     * Return lessons for a specific category+course combination.
     */
    public function getLessons(int $categoryId, int $courseId): array
    {
        if (!isset($this->lessonsByCategoryAndCourse[$categoryId][$courseId])) {
            return [];
        }

        return $this->lessonsByCategoryAndCourse[$categoryId][$courseId];
    }

    private function seedLessons(): void
    {
        // Register lessons: category => course => lessons
        $this->lessonsByCategoryAndCourse = [
            1 => \V3\App\Services\Explore\LessonData\Category1Lessons::getLessons(),
            2 => \V3\App\Services\Explore\LessonData\Category2Lessons::getLessons(),
            3 => \V3\App\Services\Explore\LessonData\Category3Lessons::getLessons(),
            4 => \V3\App\Services\Explore\LessonData\Category4Lessons::getLessons(),
            5 => \V3\App\Services\Explore\LessonData\Category5Lessons::getLessons(),
        ];
    }
}
