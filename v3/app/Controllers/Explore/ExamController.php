<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Services\Explore\ExamService;

#[Group('/public/cbt')]
class ExamController extends ExploreBaseController
{
    private ExamService $examService;

    public function __construct()
    {
        parent::__construct();
        $this->examService = new ExamService($this->pdo);
    }
}
