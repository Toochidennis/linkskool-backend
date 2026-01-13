<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Utilities\FileHandler;
use V3\App\Models\Explore\ProgramCourse;

class ProgramCourseService
{
    protected ProgramCourse $programCourseModel;
    private FileHandler $fileHandler;

    public function __construct(\PDO $pdo)
    {
        $this->programCourseModel = new ProgramCourse($pdo);
        $this->fileHandler = new FileHandler();
    }

    public function addCourseToProgram(array $data)
    {

        return $this->programCourseModel->insert($payload);
    }

}