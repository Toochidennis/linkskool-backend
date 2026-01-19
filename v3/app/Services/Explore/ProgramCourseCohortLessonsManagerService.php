<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\CohortLessonQuiz;
use V3\App\Models\Explore\Program;
use V3\App\Models\Explore\ProgramCourse;
use V3\App\Models\Explore\ProgramCourseCohort;
use V3\App\Models\Explore\ProgramCourseCohortLesson;

class ProgramCourseCohortLessonsManagerService
{
    private Program $programModel;
    private ProgramCourse $programCourseModel;
    private ProgramCourseCohort $programCourseCohortModel;
    private ProgramCourseCohortLesson $programCourseCohortLessonModel;
    private CohortLessonQuiz $cohortLessonQuizModel;

    public function __construct(\PDO $pdo)
    {
        $this->programModel = new Program($pdo);
        $this->programCourseModel = new ProgramCourse($pdo);
        $this->programCourseCohortModel = new ProgramCourseCohort($pdo);
        $this->programCourseCohortLessonModel = new ProgramCourseCohortLesson($pdo);
        $this->cohortLessonQuizModel = new CohortLessonQuiz($pdo);
    }

    public function getCohorts(array $filters)
    {
        
    }
}
