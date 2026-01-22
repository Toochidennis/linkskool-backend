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
        $this->seedLessonQuizzes();
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
            \V3\App\Services\Explore\LessonData\Category1Lessons::getLessons(),
            \V3\App\Services\Explore\LessonData\Category2Lessons::getLessons(),
            \V3\App\Services\Explore\LessonData\Category3Lessons::getLessons(),
            \V3\App\Services\Explore\LessonData\Category4Lessons::getLessons(),
            \V3\App\Services\Explore\LessonData\Category5Lessons::getLessons(),
            \V3\App\Services\Explore\LessonData\Category6Lessons::getLessons(),
        ];
    }

    private function seedLessonQuizzes(): void
    {
        $this->lessonQuizzes = [
            9 => \V3\App\Services\Explore\LessonData\Category6LessonQuizzes::getQuizzes(),
        ];
    }

    public function seedCourses()
    {
        $courses = [
            [
                "id" => 1,
                "course_name" => "Scratch Programming",
                "description" => "An engaging way to explore coding concepts through colorful blocks, where creativity meets logic in building games, stories, and animations—perfect for young minds taking their first steps into programming.",
                "image_url" => "https://linkschoolonline.com/assets/img/scratch.png",
                "category" => "Animation and Storytelling",
                "slogan" => "Code. Create. Imagine.",
                "icon" => "https://linkschoolonline.com/assets/img/scratch-icon.png",
                "has_content" => true
            ],
            [
                "id" => 2,
                "course_name" => "Graphics Design",
                "description" => "A creative blend of visuals and ideas that communicate messages through color, shape, and layout—turning imagination into impactful designs.",
                "image_url" => "https://linkschoolonline.com/assets/img/graphics-design.png",
                "category" => "Graphic Design and Visual Arts",
                "slogan" => "Creativity in Every Pixel!",
                "icon" => "https://linkschoolonline.com/assets/img/graphics-icon.svg",
                "has_content" => true
            ],
            [
                "id" => 3,
                "course_name" => "Web Development",
                "description" => "The craft of shaping digital experiences - bringing ideas to life on the web through structure, style, and interactivity.",
                "image_url" => "https://linkschoolonline.com/assets/img/web-dev.png",
                "category" => "Web Development",
                "slogan" => "Code Smart. Build Fast. Go Live.",
                "icon" => "https://linkschoolonline.com/assets/img/web-dev-icon.png",
                "has_content" => true
            ],
            [
                "id" => 4,
                "course_name" => "Python Programming",
                "description" => "A powerful and versatile language that's perfect for beginners and professionals alike-unlock the potential to build apps, automate tasks, and explore data science with ease.",
                "image_url" => "https://linkschoolonline.com/assets/img/python.png",
                "category" => "Programming and Software Development",
                "slogan" => "Code Simple. Build Big.",
                "icon" => "https://linkschoolonline.com/assets/img/python.png",
                "has_content" => true
            ],
            [
                "id" => 5,
                "course_name" => "Animation",
                "description" => "Bring characters, ideas, and stories to life through motion—combining art and technology to create stunning visuals and immersive storytelling.",
                "image_url" => "https://linkschoolonline.com/assets/img/anim_course.png",
                "category" => "Animation and Creative Media",
                "slogan" => "Your Ideas in Motion.",
                "icon" => "https://linkschoolonline.com/assets/img/anim_course.png",
                "has_content" => true
            ],
            [
                "id" => 6,
                "course_name" => "Robotics",
                "description" => "Dive into the exciting world of robotics - design, build, and program intelligent machines that can sense, think, and act.",
                "image_url" => "https://linkschoolonline.com/assets/img/robotics.png",
                "category" => "Engineering and Technology",
                "slogan" => "Build the Future, One Robot at a Time.",
                "icon" => "https://linkschoolonline.com/assets/img/robotics-icon.png",
                "has_content" => true
            ],
            [
                "id" => 7,
                "course_name" => "Android Development",
                "description" => "Learn to build powerful and user-friendly mobile apps for the world's most popular mobile operating system.",
                "image_url" => "https://linkschoolonline.com/assets/img/android.png",
                "category" => "Mobile App Development",
                "slogan" => "From Idea to App Store.",
                "icon" => "https://linkschoolonline.com/assets/img/android.png",
                "has_content" => true
            ],
            [
                "id" => 8,
                "course_name" => "Artificial Intelligence (AI)",
                "description" => "Learn how to design intelligent systems using data, algorithms, and machine learning to solve real-world problems.",
                "image_url" => "https://linkschoolonline.com/assets/img/ai_explorer1.png",
                "category" => "Artificial Intelligence",
                "slogan" => "Build Machines That Think.",
                "icon" => "https://linkschoolonline.com/assets/img/ai_explorer_icon.png",
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
                "name" => "Easter Kids Coding Fest",
                'description' => 'An exciting coding festival designed for kids to explore the world of programming through fun and interactive activities during the Easter break.',
                'short' => "code_fest",
                "available" => 1,
                'free' => 1,
                'courses' => [["id" => 1], ["id" => 2], ["id" => 3]],
                'limit' => 0
            ],
            [
                "id" => 1,
                "name" => "Kids Weekend CodeLab",
                'short' => "code_lab",
                'description' => 'A fun and interactive weekend coding program designed for kids to explore the world of programming through hands-on projects and collaborative learning in a supportive environment.',
                "available" => 1,
                'free' => 0,
                'courses' => [["id" => 1], ["id" => 2], ["id" => 3]],
                'limit' => 2
            ],
            [
                "id" => 3,
                "name" => "Kids Coding BootCamp",
                'short' => "boot_camp",
                'description' => 'An intensive coding bootcamp designed for kids to learn programming fundamentals and build exciting projects in a supportive and fun environment.',
                "available" => 1,
                'free' => 0,
                'courses' => [["id" => 1], ["id" => 2], ["id" => 3], ["id" => 4], ["id" => 5], ["id" => 6], ["id" => 7]],
                'limit' => 2
            ],
            [
                "id" => 4,
                "name" => "AI Explorers Bootcamp",
                'short' => "ai_explorer",
                'description' => 'An immersive bootcamp designed to introduce kids to the exciting world of Artificial Intelligence (AI) through hands-on projects and interactive learning experiences.',
                "available" => 1,
                'free' => 1,
                'courses' => [["id" => 8]],
                'limit' => 2
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
                'course_id' => $course['id'],
                'is_active' => 1,
                'display_order' => $index + 1,
            ]);
        }
    }

    public function seedCohorts(array $data)
    {
        $slug = 'testing-cohort-1';
        return $this->programCourseCohort->insert([
            'slug' => $slug,
            'course_id' => $data['course_id'],
            'course_name' => $data['course_name'],
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

    public function seedCohortLessons(array $lessons, array $params)
    {
        $lessons = $this->getLessons($params['category_id'], $params['course_id']);

        foreach ($lessons as $index => $lesson) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $lesson['title'])));

            $this->programCourseCohortLesson->insert([
                'cohort_id' => $params['cohort_id'],
                'program_id' => $params['program_id'],
                'course_id' => $params['course_id'],
                'title' => $lesson['title'],
                'description' => $lesson['content'],
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
    }
}
