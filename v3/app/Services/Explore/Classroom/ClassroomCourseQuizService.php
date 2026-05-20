<?php

namespace V3\App\Services\Explore\Classroom;

use V3\App\Models\Explore\Classroom\ClassroomCourse;
use V3\App\Models\Explore\Classroom\ClassroomCourseQuiz;
use V3\App\Models\Explore\Classroom\ClassroomCourseQuizSetting;
use V3\App\Models\Explore\Level;
use V3\App\Models\Portal\Academics\Course as SubjectModel;
use V3\App\Services\Common\DeepSeekClient;

class ClassroomCourseQuizService
{
    protected ClassroomCourseQuiz $model;
    private ClassroomCourseQuizSetting $settingModel;
    private ClassroomCourse $courseModel;
    private Level $levelModel;
    private SubjectModel $subjectModel;
    private DeepSeekClient $ai;

    public function __construct(\PDO $pdo)
    {
        $this->model          = new ClassroomCourseQuiz($pdo);
        $this->settingModel   = new ClassroomCourseQuizSetting($pdo);
        $this->courseModel    = new ClassroomCourse($pdo);
        $this->levelModel     = new Level($pdo);
        $this->subjectModel   = new SubjectModel($pdo);
        $this->ai = new DeepSeekClient();
    }

    public function create(array $data)
    {
        $payload = [
            'quiz_settings_id' => $data['quiz_settings_id'],
            'course_id'  => $data['course_id'],
            'question_text'  => $data['question_text'],
            'options'  => json_encode($data['options']),
            'correct'  => json_encode($data['correct']),
        ];

        if (isset($data['question_id']) && !empty($data['question_id']) && $data['question_id'] > 0) {
            return $this->model
                ->where('question_id', '=', $data['question_id'])
                ->update($payload);
        }

        return $this->model->insert($payload);
    }

    public function createMany(int $quizSettingsId, int $courseId, array $questions): int
    {
        $rows = array_map(fn($q) => [
            'quiz_settings_id' => $quizSettingsId,
            'course_id'        => $courseId,
            'question_text'    => $q['question_text'],
            'options'          => json_encode($q['options']),
            'correct'          => json_encode($q['correct']),
        ], $questions);

        return $this->model->insertMany($rows);
    }

    public function update(array $data)
    {
        $payload = [
            'quiz_settings_id' => $data['quiz_settings_id'],
            'question_text' => $data['question_text'],
            'course_id' => $data['course_id'],
            'options'  => json_encode($data['options']),
            'correct' => json_encode($data['correct']),
        ];

        return $this->model
            ->where('question_id', '=', $data['question_id'])
            ->update($payload);
    }

    public function saveSettings(array $data): array|false
    {
        $courseId = $data['course_id'];
        $lessonId = $data['lesson_id'] ?? null;

        $payload = [
            'institution_id' => $data['institution_id'],
            'course_id'      => $courseId,
            'lesson_id'      => $lessonId,
            'topic'          => $data['topic'] ?? null,
            'duration'       => $data['duration'] ?? null,
            'start_date'     => $data['start_date'] ?? null,
            'end_date'       => $data['end_date'] ?? null,
        ];

        $query = $this->settingModel->where('course_id', $courseId);
        if ($lessonId !== null) {
            $query->where('lesson_id', $lessonId);
        } else {
            $query->whereNull('lesson_id');
        }

        $existing = $query->first();

        if ($existing) {
            $updated = $this->settingModel
                ->where('id', $existing['id'])
                ->update([...$payload, 'updated_at' => date('Y-m-d H:i:s')]);

            if (!$updated) {
                return false;
            }

            $settingsId = (int) $existing['id'];
        } else {
            $settingsId = $this->settingModel->insert($payload);
            if (!$settingsId) {
                return false;
            }
        }

        return $this->settingModel->where('id', $settingsId)->first();
    }

    public function getSettings(int $courseId, ?int $lessonId = null): ?array
    {
        $query = $this->settingModel->where('course_id', $courseId);

        if ($lessonId !== null) {
            $query->where('lesson_id', $lessonId);
        } else {
            $query->whereNull('lesson_id');
        }

        return $query->first() ?: null;
    }

    public function getQuestions(int $quizSettingsId): array
    {
        $rows = $this->model
            ->where('quiz_settings_id', $quizSettingsId)
            ->get();

        return array_map(fn($row) => [
            ...$row,
            'options' => json_decode($row['options'], true),
            'correct' => json_decode($row['correct'], true),
        ], $rows);
    }

    public function generateQuestions(array $data): array
    {
        $courseId = $data['course_id'];
        $count = $data['count'];
        $subjectId = $data['subject_id'] ?? null;
        $levelId = $data['level_id'] ?? null;

        $course = $this->courseModel
            ->select(['name', 'topic'])
            ->where('id', $courseId)
            ->first();

        if (empty($course['name'])) {
            throw new \InvalidArgumentException("Course with ID {$courseId} not found.");
        }

        $courseName = $course['name'];
        $topic  = $course['topic'] ?? null;

        $levelName = null;
        if ($levelId) {
            $level = $this->levelModel->select(['name'])->where('id', $levelId)->first();
            $levelName = $level['name'] ?? null;
        }

        $subjectName = null;
        if ($subjectId) {
            $subject   = $this->subjectModel->select(['course_name'])->where('id', $subjectId)->first();
            $subjectName = $subject['course_name'] ?? null;
        }

        $meta = array_filter([
            'course'  => $courseName,
            'topic'   => $topic,
            'subject' => $subjectName,
            'level'   => $levelName,
        ]);

        $metaJson = json_encode($meta, JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
        You are a quiz question generator for an educational platform.

        CONTEXT:
        {$metaJson}

        TASK:
        Generate exactly {$count} multiple-choice questions based on the context above.

        RULES:
        - Each question must have exactly 4 options
        - The correct answer must reference the exact text of one option and its zero-based order (0–3)
        - Questions must be relevant to the course/subject/level provided
        - Language should be clear and appropriate for the target level
        - For ALL mathematical or scientific notation — including equations, expressions, formulas, fractions, exponents, roots, matrices, vectors, integrals, summations, chemical formulas, Greek letters, and any symbolic content — you MUST use KaTeX/MathJax dollar-sign syntax
        - Wrap inline math with single dollar signs, e.g. \$x^{2} + y^{2} = z^{2}\$
        - Wrap block/display math with double dollar signs, e.g. \$\$\int_{0}^{\infty} e^{-x}\,dx = 1\$\$
        - This applies to question_text and every option text — never use plain-text math like "x^2" or "sqrt(x)" outside of dollar-sign delimiters
        - This rule applies to all subjects: Mathematics, Physics, Chemistry, Biology, Economics, Statistics, Computer Science, Logic, etc.
        - Use Markdown for all other formatting needs: **bold** for emphasis, `code` for variable names or code snippets, and numbered or bulleted lists where clarity requires it

        Return ONLY valid JSON matching this schema exactly:
        {
          "questions": [
            {
              "question_text": "...",
              "options": [
                {"text": "..."},
                {"text": "..."},
                {"text": "..."},
                {"text": "..."}
              ],
              "correct": {
                "text": "...",
                "order": 0
              }
            }
          ]
        }

        Return JSON only. No markdown. No commentary. No trailing commas.
        PROMPT;

        $result = $this->ai->call([
            'model' => 'deepseek-chat',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.4,
            'max_tokens' => 300 * $count,
            'response_format' => ['type' => 'json_object'],
        ], 120);

        return $result['questions'] ?? [];
    }
}
