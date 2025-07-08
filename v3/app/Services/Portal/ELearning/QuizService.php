<?php

namespace V3\App\Services\Portal\ELearning;

use PDO;
use Throwable;
use V3\App\Common\Enums\ContentType;
use V3\App\Models\Portal\ELearning\Content;
use V3\App\Models\Portal\ELearning\Quiz;

class QuizService
{
    private Content $content;
    private string $contentPath;
    private string $relativePath;
    private Quiz $quiz;
    private PDO $pdo;
    private const ALLOWED_EXTENSIONS = ['jpg', 'png', 'jpeg'];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->content = new Content($pdo);
        $this->quiz = new Quiz($pdo);

        $v3root = realpath(__DIR__ . '/../../../../');

        if (!$v3root) {
            throw new \RuntimeException("Could not resolve v3 root path.");
        }

        $publicPath = "$v3root/public";
        $dbName = explode('_', $_SESSION['_db'])[2] ?? 'default_db';
        $this->relativePath = "assets/elearning/$dbName/";
        $this->contentPath = $publicPath . DIRECTORY_SEPARATOR . $this->relativePath;

        if (
            !is_dir($this->contentPath) &&
            !mkdir($this->contentPath, 0755, true)
        ) {
            throw new \RuntimeException("Failed to create directory: {$this->contentPath}");
        }
    }

    public function addQuiz(array $assessment)
    {
        $questions = $assessment['questions'];
        $settings = $assessment['setting'];
        $questionIds = [];
        var_dump($settings);

        try {
            $this->pdo->beginTransaction();

            foreach ($questions as $index => $question) {
                $questionFiles = $question['question_files'] ?? [];
                $options = $question['options'] ?? [];

                if (!empty($questionFiles)) {
                    $questionFiles = $this->handleFiles($questionFiles);
                }

                if (!empty($options)) {
                    foreach ($options as &$option) {
                        if (!empty($option['option_files'] ?? [])) {
                            $option['option_files'] = $this->handleFiles($option['option_files']);
                        }
                    }
                }

                $payload = [
                    'title' => $question['question_text'],
                    'content' => $this->json($questionFiles),
                    'type' => $question['question_type'],
                    'parent' => $question['question_grade'],
                    'correct' => $this->json($question['correct']),
                    'course_id' => $settings['course_id'],
                    'course_name' => $settings['course_name'],
                    'term' => $settings['term'],
                    'answer' => $this->json($options)
                ];

                if ($newId = $this->quiz->insert($payload)) {
                    $questionIds[] = ['id' => $newId, 'rank' => $index];
                } else {
                    throw new \RuntimeException("Failed to insert question at $index");
                }
            }

            $contentId = $this->addSettings($settings, $questionIds);

            $this->pdo->commit();

            return $contentId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function addSettings(array $setting, array $questionIds)
    {
        var_dump($setting);
        die;
        $payload = [
            'title' => $setting['title'],
            'description' => $setting['description'],
            'type' => ContentType::QUIZ,
            'outline' => $setting['syllabus_id'],
            'course_id' => $setting['course_id'],
            'course_name' => $setting['course_name'],
            'level' => $setting['level_id'],
            'path_label' => $this->json($setting['classes']),
            'author_id' => $setting['creator_id'],
            'author_name' => $setting['creator_name'],
            'term' => $setting['term'],
            'url' => $this->json($questionIds),
            'start_date' => $setting['start_date'],
            'end_date' => $setting['end_date'],
            'body' => $setting['duration'],
            'upload_date' => date('Y-m-d H:i:s'),
        ];

        return $this->content->insert($payload);
    }

    public function updateQuiz()
    {
        // TODO
    }

    public function updateSettings()
    {
        // TODO
    }

    /**
     * Summary of handleFiles
     * @param array $files
     * @param bool $isUpdate
     * @throws \Exception
     * @return bool|string
     */
    private function handleFiles(array $files, bool $isUpdate = false): array
    {
        $processed = [];

        foreach ($files as $file) {
            if (
                $isUpdate &&
                !empty($file['file_name'] ?? '') &&
                ($file['file_name'] ?? '') !== $file['old_file_name'] &&
                empty($file['file'] ?? '')
            ) {
                throw new \Exception('Missing new file data for changed file.');
            }

            if (
                $isUpdate &&
                empty($file['file_name'] ?? '') &&
                ($file['file_name'] ?? '') !== $file['old_file_name'] &&
                !empty($file['file'] ?? '')
            ) {
                throw new \Exception(
                    "You uploaded a new file but didn't provide its name."
                );
            }

            if (
                $isUpdate &&
                !empty($file['file_name'] ?? '') &&
                ($file['file_name'] ?? '') !== $file['old_file_name']
            ) {
                // Delete old file
                $oldPath = $this->contentPath . basename($file['old_file_name']);
                if (file_exists($oldPath) && !unlink($oldPath)) {
                    throw new \Exception("Failed to delete old file: $oldPath");
                }

                // Save new file
                $processed[] = $this->processFile($file);
                continue;
            }

            $processed[] = (!$isUpdate) ? $this->processFile($file) : $file;
        }

        return $processed;
    }

    /**
     * Validate and store a single file.
     */
    private function processFile(array $file): array
    {
        $cleanName = basename($file['file_name']);
        $ext = strtolower(pathinfo($cleanName, PATHINFO_EXTENSION));

        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            throw new \Exception("File type not allowed: $ext");
        }

        // Generate a unique prefix
        $uniquePrefix = uniqid('', true);
        $newFileName = "{$uniquePrefix}_$cleanName";

        $filePath = "{$this->contentPath}{$newFileName}";
        $content = base64_decode($file['file']);
        if ($content === false) {
            throw new \Exception("Invalid base64 content.");
        }

        if (file_put_contents($filePath, $content) === false) {
            throw new \Exception("Failed to save file: $newFileName");
        }

        $file['old_file_name'] = "{$this->relativePath}{$newFileName}";
        $file['file_name'] = "{$this->relativePath}{$newFileName}";
        $file['file'] = '';

        return $file;
    }

    private function json(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
