<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\CohortLessonQuiz;
use V3\App\Models\Explore\LearningCourse;
use V3\App\Models\Explore\Program;
use V3\App\Models\Explore\ProgramCourse;
use V3\App\Models\Explore\ProgramCourseCohort;
use V3\App\Models\Explore\ProgramCourseCohortLesson;

class CourseContentService
{
    private LearningCourse $learningCourse;
    private Program $program;
    private ProgramCourse $programCourse;
    private ProgramCourseCohort $programCourseCohort;
    private ProgramCourseCohortLesson $programCourseCohortLesson;
    private CohortLessonQuiz $cohortLessonQuiz;

    private array $lessonsByCategoryAndCourse;
    private array $lessonQuizzes;

    public function __construct(\PDO $pdo)
    {
        $this->learningCourse = new LearningCourse($pdo);
        $this->program = new Program($pdo);
        $this->programCourse = new ProgramCourse($pdo);
        $this->programCourseCohort = new ProgramCourseCohort($pdo);
        $this->programCourseCohortLesson = new ProgramCourseCohortLesson($pdo);
        $this->cohortLessonQuiz = new CohortLessonQuiz($pdo);

        $this->seedLessons();
    }

    /**
     * Return lessons for a specific category+course combination.
     */
    public function getLessons(int $categoryId, int $courseId): array
    {
        return $this->lessonsByCategoryAndCourse[$categoryId][$courseId];
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
            0 => \V3\App\Services\Explore\LessonData\Category1Lessons::getLessons(),
            1 => \V3\App\Services\Explore\LessonData\Category2Lessons::getLessons(),
            2 => \V3\App\Services\Explore\LessonData\Category3Lessons::getLessons(),
            3 => \V3\App\Services\Explore\LessonData\Category4Lessons::getLessons(),
            4 => \V3\App\Services\Explore\LessonData\Category5Lessons::getLessons(),
            5 => \V3\App\Services\Explore\LessonData\Category6Lessons::getLessons(),
        ];
    }

    public function seedCourses()
    {
        $courses = [
            [
                "id" => 1,
                "course_name" => "Cinematography",
                "description" => "Learn the art of capturing stunning visuals and telling compelling stories through the lens of a camera.",
                "image_url" => "https://linkschoolonline.com/assets/img/tvet.jpeg",
                "category" => "TVET",
                "slogan" => "Bring your stories to life with creative media!",
                "icon" => "https://linkschoolonline.com/assets/img/scratch-icon.png",
                "has_content" => true
            ]
        ];

        foreach ($courses as $course) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $course['course_name'])));

            $payload = [
                'title' => $course['course_name'],
                'slug' => $slug,
                'description' => $course['description'],
                'image_url' => $course['image_url'],
                'slogan' => $course['slogan'],
                'author_id' => 1,
                'author_name' => 'admin',
            ];
            $this->learningCourse->insert($payload);
        }

        return true;
    }

    public function seedPrograms()
    {
        $programs = [
            [
                "id" => 2,
                "name" => "TVET Creative Media",
                'description' => 'A comprehensive program designed to equip students with practical skills in various aspects of creative media production, including video production, graphic design, and digital storytelling.',
                'short' => "TVET",
                "available" => 1,
                'free' => 1,
                'courses' => [9],
                'limit' => 0
            ],
        ];

        foreach ($programs as $program) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $program['name'])));
            $payload = [
                'slug' => $slug,
                'name' => $program['name'],
                'description' => $program['description'],
                'image_url' => $program['image_url'] ?? null,
                'shortname' => $program['short'],
                'status' => 'published',
                'sponsor' => 'Digital Dreams Limited',
            ];

            $id = $this->program->insert($payload);
            $this->upsertProgramCourses($program['courses'], (int)$id);
        }

        return true;
    }

    private function upsertProgramCourses(array $courses, int $programId): void
    {
        foreach ($courses as $index => $course) {
            $this->programCourse->insert([
                'program_id' => $programId,
                'course_id' => $course,
                'is_active' => 1,
                'display_order' => $index + 1,
            ]);
        }
    }

    public function seedCohorts(array $data)
    {
        foreach ($data['courses'] as $course) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $course['course_name'])));
            $this->programCourseCohort->insert([
                'slug' => $slug,
                'course_id' => $course['course_id'],
                'course_name' => $course['course_name'],
                'program_id' => $data['program_id'],
                'title' => 'Cohort 1',
                'description' => 'First cohort for testing',
                'benefits' => 'Access to all course materials and quizzes',
                'code' => null,
                'start_date' => '2024-09-01',
                'end_date' => '2024-12-15',
                'status' => 'ongoing',
                'image_url' => null,
                'capacity' => null,
                'delivery_mode' => null,
                'zoom_link' => null,
                'is_free' => 1,
                'trial_type' => null,
                'trial_value' => null,
                'cost' => null,
            ]);
        }

        return true;
    }

    public function seedCohortLessons(array $params)
    {
        $lessonsByCategoryAndCourse = [
            0 => \V3\App\Services\Explore\LessonData\Category1Lessons::getLessons(),
            1 => \V3\App\Services\Explore\LessonData\Category2Lessons::getLessons(),
            2 => \V3\App\Services\Explore\LessonData\Category3Lessons::getLessons(),
            3 => \V3\App\Services\Explore\LessonData\Category4Lessons::getLessons(),
            4 => \V3\App\Services\Explore\LessonData\Category5Lessons::getLessons(),
            5 => \V3\App\Services\Explore\LessonData\Category6Lessons::getLessons(),
        ];

        $lessons = $lessonsByCategoryAndCourse[$params['category_id']][$params['course_id']];

        foreach ($lessons as $index => $lesson) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $lesson['title'])));

            $this->programCourseCohortLesson->insert([
                'cohort_id' => $params['cohort_id'],
                'program_id' => $params['program_id'],
                'course_id' => $params['course_id'],
                'title' => $lesson['title'],
                'description' => $lesson['description'],
                'video_url' => $lesson['video_url'] ?? null,
                'display_order' => $index + 1,
                'goals' => $lesson['goal'] ?? null,
                'objectives' => $lesson['objectives'] ?? null,
                'slug' => $slug,
                'author_id' => 1,
                'author_name' => 'admin',
                'lesson_date' => '2024-09-01',
                'assignment_instructions' => $lesson['assignment_description'] ?? null,
                'recorded_video_url' => $lesson['recorded_url'] ?? null,
                'material_url' => $lesson['material_url'] ?? null,
                'assignment_url' => $lesson['assignment_url'] ?? null,
                'assignment_due_date' => null,
                'is_final_lesson' => $lesson['is_final'] ?? false,
            ]);
        }
        return true;
    }

    public function seedQuizzes(array $params)
    {
        $quizzesByCourseAndLesson = [
            0 => \V3\App\Services\Explore\LessonData\WebQuizzes::getQuizzes(),
            1 => \V3\App\Services\Explore\LessonData\ScratchQuizzes::getQuizzes(),
            2 => \V3\App\Services\Explore\LessonData\GraphicQuizzes::getQuizzes(),
        ];

        $quizzes = $quizzesByCourseAndLesson[$params['quiz_index']][$params['lesson_index']];

        foreach ($quizzes as $quiz) {
            $payload = $this->formatQuestionData($quiz, $params);
            $this->cohortLessonQuiz->insert($payload);
        }

        return true;
    }

    private function formatQuestionData(array $question, array $params): array
    {
        $payload = [
            'lesson_id' => $params['lesson_id'],
            'cohort_id' => $params['cohort_id'],
            'course_id' => $params['course_id'],
            'program_id' => $params['program_id'],
            'title' => $question['question_text'],
            'type' => 'qo',
        ];

        $options = [];
        for ($i = 1; $i <= 4; $i++) {
            $options[] = [
                'text' => trim($question["option_{$i}_text"] ?? ''),
                'option_files' => [],
            ];
        }

        if (!isset($question['answer']) || !is_numeric($question['answer'])) {
            throw new \Exception('Answer must be a numeric 1-based index');
        }

        $oneBasedIndex  = (int) $question['answer'];
        $zeroBasedIndex = $oneBasedIndex - 1;

        if ($zeroBasedIndex < 0 || $zeroBasedIndex >= count($options)) {
            throw new \Exception(
                "Invalid correct option index: {$oneBasedIndex}"
            );
        }

        $correctText = $options[$zeroBasedIndex]['text'];

        $payload['answer'] = json_encode($options);
        $payload['correct'] = json_encode([
            'text'  => $correctText,
            'order' => $zeroBasedIndex,
        ]);

        return $payload;
    }
}
