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
    private array $lessonQuizzes;

    public function __construct()
    {
        $this->seedLessons();
        $this->seedLessonQuizzes();
    }

    /**
     * Return lessons for a specific category+course combination.
     */
    public function getLessons(int $categoryId, int $courseId): array
    {
        if (!isset($this->lessonsByCategoryAndCourse[$categoryId][$courseId])) {
            return [
                'lessons' => [],
                'resources' => []
            ];
        }

        $lessons = $this->lessonsByCategoryAndCourse[$categoryId][$courseId];
        $resources = [];
        $resourceId = 1;

        // Extract resources from lessons where material_url is not empty
        foreach ($lessons as $lesson) {
            if (!empty($lesson['material_url'])) {
                $resources[] = [
                    'id' => $resourceId++,
                    'lesson_id' => $lesson['id'],
                    'name' => $lesson['title'] ?? '',
                    'url' => $lesson['material_url']
                ];
            }
        }

        return [
            'lessons' => $lessons,
            'resources' => $resources
        ];
    }

    public function getLessonQuizzes(int $courseId, int $lessonId): array
    {
        if (isset($this->lessonQuizzes[$courseId][$lessonId])) {
            return $this->lessonQuizzes[$courseId][$lessonId];
        }
        return [];
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
            6 => \V3\App\Services\Explore\LessonData\Category6Lessons::getLessons(),
        ];
    }

    private function seedLessonQuizzes(): void
    {
        $this->lessonQuizzes = [
            9 => \V3\App\Services\Explore\LessonData\Category6LessonQuizzes::getQuizzes(),
        ];
    }
}
