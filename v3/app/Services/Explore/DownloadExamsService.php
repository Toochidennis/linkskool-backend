<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Enums\QuestionType;
use V3\App\Models\Explore\Exam;
use V3\App\Models\Portal\ELearning\Quiz;

class DownloadExamsService
{
    private Exam $exam;
    private Quiz $quiz;

    public function __construct(\PDO $pdo)
    {
        $this->exam = new Exam($pdo);
        $this->quiz = new Quiz($pdo);
    }

    public function buildZip(int $examType): string
    {
        $data = $this->formatExamsAndQuestions($examType);

        $questions = $data['questions'];
        $images = $this->normalizeQuestionsAndExtractImages($questions);

        $tmpDir = sys_get_temp_dir() . '/exam_' . uniqid();
        mkdir("$tmpDir/images", 0777, true);

        file_put_contents("$tmpDir/exams.json", json_encode($data['exams'], JSON_PRETTY_PRINT));
        file_put_contents("$tmpDir/questions.json", json_encode($questions, JSON_PRETTY_PRINT));

        // Copy images
        foreach ($images as $imagePath) {
            $source = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($imagePath, '/');
            if (is_file($source)) {
                copy($source, "$tmpDir/images/" . basename($imagePath));
            }
        }

        $zipPath = "$tmpDir.zip";
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE);

        $zip->addFile("$tmpDir/exams.json", 'exams.json');
        $zip->addFile("$tmpDir/questions.json", 'questions.json');

        foreach (glob("$tmpDir/images/*") as $img) {
            $zip->addFile($img, 'images/' . basename($img));
        }

        $zip->close();

        $this->cleanup($tmpDir);

        return $zipPath;
    }

    public function cleanup(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (glob($dir . '/*') as $file) {
            is_dir($file) ? $this->cleanup($file) : unlink($file);
        }

        rmdir($dir);
    }

    private function formatExamsAndQuestions(int $examType): array
    {
        $exams = $this->fetchExams($examType);
        $allQuestionIds = [];

        foreach ($exams as $exam) {
            $questionIds = json_decode($exam['url'], true) ?? [];
            $allQuestionIds = array_merge($allQuestionIds, $questionIds);
            $exam['question_ids'] = $questionIds;
            unset($exam['url']);
        }

        $allQuestionIds = array_unique($allQuestionIds);
        $questions = $this->fetchQuestionsByIds($allQuestionIds);

        return [
            'exams' => $exams,
            'questions' => $questions
        ];
    }

    private function fetchExams(int $examType): array
    {
        $exams = $this->exam
            ->select([
                'id',
                'description',
                'course_name',
                'course_id',
                'year',
                'exam_type',
                'url AS question_ids',
                'url'
            ])
            ->where('exam_type', '=', $examType)
            ->get();

        return $exams;
    }

    private function fetchQuestionsByIds(array $questionIds): array
    {
        $questions = $this->quiz
            ->select([
                'question_id',
                'title AS question_text',
                'content AS question_files',
                'topic',
                'topic_id',
                'passage',
                'passage_id',
                'instruction',
                'instruction_id',
                'explanation',
                'explanation_id',
                'type as question_type',
                'answer as options',
                'correct',
                'year'
            ])
            ->in('question_id', $questionIds)
            ->get();

        return array_map([$this, 'formatQuestion'], $questions);
    }

    private function formatQuestion(array $question): array
    {
        $question['question_type'] = QuestionType::tryFrom($question['question_type'])?->label() ?? 'Unknown';
        $question['question_files'] = $this->decode($question['question_files']);
        $question['options'] = $this->decode($question['options']);
        $question['correct'] = $this->decode($question['correct']);

        return $question;
    }

    private function normalizeQuestionsAndExtractImages(array &$questions): array
    {
        $images = [];

        foreach ($questions as &$question) {

            // Question files
            if (!empty($question['question_files'])) {
                foreach ($question['question_files'] as &$file) {
                    if (!empty($file['file_name'])) {
                        $images[] = $file['file_name'];

                        $file['file_name'] = 'images/' . basename($file['file_name']);
                        // old_file_name stays untouched
                    }
                }
            }

            // Option files
            if (!empty($question['options'])) {
                foreach ($question['options'] as &$option) {
                    if (!empty($option['option_files'])) {
                        foreach ($option['option_files'] as &$file) {
                            if (!empty($file['file_name'])) {
                                $images[] = $file['file_name'];

                                $file['file_name'] = 'images/' . basename($file['file_name']);
                            }
                        }
                    }
                }
            }
        }

        return array_unique($images);
    }

    private function decode(?string $data): array
    {
        if (empty($data) || $data === null) {
            return [];
        }

        $decoded = json_decode($data, true);
        return \is_array($decoded) ? $decoded : [];
    }
}
