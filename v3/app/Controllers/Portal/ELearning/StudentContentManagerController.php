<?php

namespace V3\App\Controllers\Portal\ELearning;

use Exception;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\ELearning\StudentContentManagerService;

class StudentContentManagerController extends BaseController
{
    private StudentContentManagerService $studentContent;

    public function __construct()
    {
        parent::__construct();
        $this->studentContent = new StudentContentManagerService($this->pdo);
    }

    public function getContents(array $vars)
    {

    }
}
