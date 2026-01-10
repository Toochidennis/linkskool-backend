<?php

namespace V3\App\Services\Explore;

class LearningPathService
{
    /** @var array<int, array<string, mixed>> */
    private array $courses;

    /** @var array<int, array<string, mixed>> */
    private array $categories;

    private CourseContentService $contentService;

    public function __construct(\PDO $pdo)
    {
        // Initialize any models or dependencies here if needed
        $this->seedCourses();
        $this->seedCategories();
        $this->contentService = new CourseContentService();
    }

    /**
     * Return categories with their associated course data.
     */
    public function getCategoriesAndCourses(): array
    {
        return array_values(array_map(function (array $category): array {
            $courseIds = $category['course_ids'] ?? [];
            $category['courses'] = array_values(array_intersect_key($this->courses, array_flip($courseIds)));
            unset($category['course_ids']);

            return $category;
        }, $this->categories));
    }

    /**
     * Fetch lessons scoped by category and course.
     */
    private function getLessonsByCategoryAndCourse(int $categoryId, int $courseId): array
    {
        return $this->contentService->getLessons($categoryId, $courseId);
    }

    /**
     * Backwards-compatible alias for lessons lookup.
     */
    public function courseLessons(int $categoryId, int $courseId): array
    {
        return $this->getLessonsByCategoryAndCourse($categoryId, $courseId);
    }

    public function getLessonQuizzes(int $courseId, int $lessonId): array
    {
        return $this->contentService->getLessonQuizzes($courseId, $lessonId);
    }

    private function seedCourses(): void
    {
        $this->courses = [
            1 => [
                "id" => 1,
                "course_name" => "Scratch Programming",
                "description" => "An engaging way to explore coding concepts through colorful blocks, where creativity meets logic in building games, stories, and animations—perfect for young minds taking their first steps into programming.",
                "image_url" => "https://linkschoolonline.com/assets/img/scratch.png",
                "category" => "Animation and Storytelling",
                "slogan" => "Code. Create. Imagine.",
                "icon" => "https://linkschoolonline.com/assets/img/scratch-icon.png",
                "email" => "communication@digitaldreamsng.com",
                "has_content" => true
            ],
            2 => [
                "id" => 2,
                "course_name" => "Graphics Design",
                "description" => "A creative blend of visuals and ideas that communicate messages through color, shape, and layout—turning imagination into impactful designs.",
                "image_url" => "https://linkschoolonline.com/assets/img/graphics-design.png",
                "category" => "Graphic Design and Visual Arts",
                "slogan" => "Creativity in Every Pixel!",
                "icon" => "https://linkschoolonline.com/assets/img/graphics-icon.svg",
                "email" => "communication@digitaldreamsng.com",
                "has_content" => true
            ],
            3 => [
                "id" => 3,
                "course_name" => "Web Development",
                "description" => "The craft of shaping digital experiences - bringing ideas to life on the web through structure, style, and interactivity.",
                "image_url" => "https://linkschoolonline.com/assets/img/web-dev.png",
                "category" => "Web Development",
                "slogan" => "Code Smart. Build Fast. Go Live.",
                "icon" => "https://linkschoolonline.com/assets/img/web-dev-icon.png",
                "email" => "communication@digitaldreamsng.com",
                "has_content" => true
            ],
            4 => [
                "id" => 4,
                "course_name" => "Python Programming",
                "description" => "A powerful and versatile language that's perfect for beginners and professionals alike-unlock the potential to build apps, automate tasks, and explore data science with ease.",
                "image_url" => "https://linkschoolonline.com/assets/img/python.png",
                "category" => "Programming and Software Development",
                "slogan" => "Code Simple. Build Big.",
                "icon" => "https://linkschoolonline.com/assets/img/python.png",
                "email" => "communication@digitaldreamsng.com",
                "has_content" => true
            ],
            5 => [
                "id" => 5,
                "course_name" => "Animation",
                "description" => "Bring characters, ideas, and stories to life through motion—combining art and technology to create stunning visuals and immersive storytelling.",
                "image_url" => "https://linkschoolonline.com/assets/img/anim_course.png",
                "category" => "Animation and Creative Media",
                "slogan" => "Your Ideas in Motion.",
                "icon" => "https://linkschoolonline.com/assets/img/anim_course.png",
                "email" => "communication@digitaldreamsng.com",
                "has_content" => true
            ],
            6 => [
                "id" => 6,
                "course_name" => "Robotics",
                "description" => "Dive into the exciting world of robotics - design, build, and program intelligent machines that can sense, think, and act.",
                "image_url" => "https://linkschoolonline.com/assets/img/robotics.png",
                "category" => "Engineering and Technology",
                "slogan" => "Build the Future, One Robot at a Time.",
                "icon" => "https://linkschoolonline.com/assets/img/robotics-icon.png",
                "email" => "communication@digitaldreamsng.com",
                "has_content" => true
            ],
            7 => [
                "id" => 7,
                "course_name" => "Android Development",
                "description" => "Learn to build powerful and user-friendly mobile apps for the world's most popular mobile operating system.",
                "image_url" => "https://linkschoolonline.com/assets/img/android.png",
                "category" => "Mobile App Development",
                "slogan" => "From Idea to App Store.",
                "icon" => "https://linkschoolonline.com/assets/img/android.png",
                "email" => "communication@digitaldreamsng.com",
                "has_content" => true
            ],
            8 => [
                "id" => 8,
                "course_name" => "Artificial Intelligence (AI)",
                "description" => "Learn how to design intelligent systems using data, algorithms, and machine learning to solve real-world problems.",
                "image_url" => "https://linkschoolonline.com/assets/img/ai_explorer1.png",
                "category" => "Artificial Intelligence",
                "slogan" => "Turn ideas into scripts. Faster. Smarter",
                "icon" => "https://linkschoolonline.com/assets/img/ai_explorer_icon.png",
                "email" => "communication@digitaldreamsng.com",
                "has_content" => true
            ],
            9 => [
                "id" => 9,
                "course_name" => "Selection Exercise",
                "description" => "This course serves as the official selection process for the TVET Creative Media Program. Participants will complete a series of creative tasks designed to assess originality, storytelling ability, visual thinking, and basic technical awareness. This is not about perfection. It is about potential, effort, and creative thinking \n Only participants who successfully complete the required tasks will qualify for the full program.",
                "image_url" => "https://linkschoolonline.com/assets/img/tvet.jpeg",
                "category" => "Creative Media Foundation",
                "slogan" => "Think. Create. Express.",
                "icon" => "https://linkschoolonline.com/assets/img/tvet_icon.png",
                "email" => "communication@digitaldreamsng.com",
                "has_content" => true,
            ]
        ];
    }

    private function seedCategories(): void
    {
        $this->categories = [
            1 => [
                "id" => 1,
                "name" => "AI Storytelling Bootcamp",
                "short" => "ai_storytelling",
                "available" => 1,
                "is_free" => 1,
                "limit" => 2,
                "course_ids" => [8],
                'start_date' => '2026-01-06',
                'end_date' => '2026-01-10'
            ],
            2 => [
                "id" => 2,
                "name" => "AI Explorers Bootcamp",
                "short" => "ai_explorer",
                "available" => 1,
                "is_free" => 1,
                "limit" => 2,
                "course_ids" => [8],
                'start_date' => '2025-12-15',
                'end_date' => '2025-12-20'
            ],
            3 => [
                "id" => 3,
                "name" => "Kids Coding Bootcamp",
                "short" => "boot_camp",
                "available" => 1,
                "is_free" => 0,
                "limit" => 2,
                "course_ids" => [1, 2, 3, 4, 5, 6, 7],
                'start_date' => '2025-08-01',
                'end_date' => '2025-09-02'
            ],
            4 => [
                "id" => 4,
                "name" => "Kids Weekend CodeLab",
                "short" => "code_lab",
                "available" => 1,
                "is_free" => 0,
                "limit" => 2,
                "course_ids" => [1, 2, 3],
                'start_date' => '2025-09-06',
                'end_date' => '2025-10-26'
            ],
            5 => [
                "id" => 5,
                "name" => "Easter Kids Coding Fest",
                "short" => "code_fest",
                "available" => 1,
                "is_free" => 1,
                "limit" => 0,
                "course_ids" => [1, 2, 3],
                'start_date' => '2025-04-15',
                'end_date' => '2025-04-20'
            ],
            6 => [
                "id" => 6,
                "name" => "TVET Creative Media Program",
                "short" => "tvet_program",
                "available" => 1,
                "is_free" => 1,
                'purpose' => 'Skill-based creative training across media disciplines (photography, cinematography, design, storytelling, etc.)',
                "limit" => 0,
                "course_ids" => [9],
                'start_date' => '2026-01-12',
                'end_date' => '2026-06-30'
            ]
        ];
    }
}
