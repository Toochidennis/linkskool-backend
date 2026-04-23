<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Services\Explore\StudyContentService;

#[Group('/public/cbt/study')]
class StudyContentController extends ExploreBaseController
{
    private StudyContentService $studyContentService;

    public function __construct()
    {
        parent::__construct();
        $this->studyContentService = new StudyContentService($this->pdo);
    }

    public function getStudyTopics(array $vars)
    {
        
    }
}
