<?php

namespace V3\App\Controllers\Portal\ELearning;

use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\ELearning\QuizService;

class QuizController extends BaseController
{
    private QuizService $quizService;

    public function __construct()
    {
        parent::__construct();
    }

    public function store()
    {
        // TODO
    }
}
