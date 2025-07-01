<?php

namespace V3\App\Services\Portal\ELearning;

use V3\App\Models\Portal\ELearning\Content;
use V3\App\Models\Portal\ELearning\Quiz;

class QuizService
{
    private Content $content;
    private Quiz $quiz;

    public function __construct(\PDO $pdo)
    {
        $this->content = new Content($pdo);
        $this->quiz = new Quiz($pdo);
    }

    public function addQuiz(array $data)
    {
        // TODO
    }

    private function addContent(array $contentData)
    {
        //TODO
    }
}
