<?php

/**
 * This class helps handles student result
 *
 * PHP version 8.0+
 *
 * @category Controller
 * @package Linkskool
 * @author ToochiDennis <dennistoochukwu@gmail.com>
 * @license MIT
 * @link https://www.linkskool.net
 */

namespace V3\App\Controllers\Portal;

use V3\App\Controllers\BaseController;
use V3\App\Models\Portal\Assessment;
use V3\App\Models\Portal\Student;
use V3\App\Traits\ValidationTrait;

class StudentResultController extends BaseController
{
    use ValidationTrait;

    private Assessment $assessment;
    private Student $student;

    public function __construct()
    {
        parent::__construct();
        $this->initialize();
    }

    /**
     * Initializes the controller by extracting POST/GET
     * data and connecting to the database.
    */
    public function initialize()
    {
        $this->assessment = new Assessment($this->pdo);
        $this->student = new Student($this->pdo);
    }

    public function getStudentCourseResult(array $args)
    {
        $data = $this->validateData(
            data: $args,
            requiredFields:['id', 'course_id', 'term', 'year']
        );
    }
}
