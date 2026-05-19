<?php

namespace V3\App\Services\Explore\Classroom;

use V3\App\Models\Explore\Classroom\ClassroomCourse;
use V3\App\Models\Explore\Classroom\ClassroomCourseQuiz;
use V3\App\Models\Explore\Level;
use V3\App\Models\Portal\Academics\Course as SubjectModel;
use V3\App\Services\Common\DeepSeekClient;

class ClassroomCourseQuizService
{
    protected ClassroomCourseQuiz $model;
    private ClassroomCourse $courseModel;
    private Level $levelModel;
    private SubjectModel $subjectModel;
    private DeepSeekClient $ai;

    public function __construct(\PDO $pdo)
    {
        $this->model        = new ClassroomCourseQuiz($pdo);
        $this->courseModel  = new ClassroomCourse($pdo);
        $this->levelModel   = new Level($pdo);
        $this->subjectModel = new SubjectModel($pdo);
        $this->ai           = new DeepSeekClient();
    }

    public function create(array $data)
    {
        $payload = [
            'institution_id' => $data['institution_id'],
            'course_id' => $data['course_id'],
            'lesson_id' => $data['lesson_id'] ?? null,
            'topic' => $data['topic'] ?? null,
            'question_text' => $data['question_text'],
            'options' => json_encode($data['options']),
            'correct' => json_encode($data['correct']),
            'duration' => $data['duration'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
        ];

        if (isset($data['question_id']) && !empty($data['question_id']) && $data['question_id'] > 0) {
            return $this->model
                ->where('question_id', '=', $data['question_id'])
                ->update($payload);
        }

        return $this->model->insert($payload);
    }
    public function update(array $data)
    {
        $payload = [
            'institution_id' => $data['institution_id'],
            'topic' => $data['topic'] ?? null,
            'lesson_id' => $data['lesson_id'] ?? null,
            'course_id' => $data['course_id'],
            'question_text' => $data['question_text'],
            'options' => json_encode($data['options']),
            'correct' => json_encode($data['correct']),
            'duration' => $data['duration'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
        ];

        return $this->model
            ->where('question_id', '=', $data['question_id'])
            ->update($payload);
    }

    public function getQuizByCourseId(int $courseId, ?int $lessonId = null): array
    {
        $query = $this->model
            ->select([
                'question_id',
                'question_text',
                'lesson_id',
                'topic',
                'options',
                'correct',
                'duration',
                'start_date',
                'end_date',
            ])
            ->where('course_id', $courseId);

        if ($lessonId !== null) {
            $query = $query->where('lesson_id', $lessonId);
        }

        $quizzes = $query->get();

        return array_map(fn($q) => [
            'question_id'   => $q['question_id'],
            'question_text' => $q['question_text'],
            'lesson_id'    => $q['lesson_id'] ?? null,
            'topic'   => $q['topic'],
            'options'  => json_decode($q['options'], true),
            'correct'       => json_decode($q['correct'], true),
            'duration'      => $q['duration'],
            'start_date'    => $q['start_date'],
            'end_date'      => $q['end_date'],
        ], $quizzes);
    }

    public function generateQuestions(
        int $courseId,
        int $count,
        ?int $subjectId = null,
        ?int $levelId = null
    ): array {
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
            $level     = $this->levelModel->select(['name'])->where('id', $levelId)->first();
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
            'model'           => 'deepseek-chat',
            'messages'        => [['role' => 'user', 'content' => $prompt]],
            'temperature'     => 0.4,
            'max_tokens'      => 300 * $count,
            'response_format' => ['type' => 'json_object'],
        ], 120);

        return $result['questions'] ?? [];
    }
}
