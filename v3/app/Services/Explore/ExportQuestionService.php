<?php

namespace V3\App\Services\Explore;

use PDO;
use V3\App\Models\Portal\ELearning\Quiz;

class ExportQuestionService
{
    private Quiz $quiz;

    public function __construct(private PDO $pdo)
    {
        $this->quiz = new Quiz($this->pdo);
    }

    public function getQuestionsByQuizId(int $quizId): array
    {
        $query = "SELECT * FROM question_table WHERE quiz_id = :quiz_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['quiz_id' => $quizId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getQuestions()
    {
        $result = $this->quiz->get();
    }
}
