<?php

namespace V3\App\Services\Portal;

use V3\App\Models\Portal\Result;

class ResultService
{
    private Result $result;

    private $grades = [];

    public function __construct(Result $result)
    {
        $this->result = $result;
    }

    public function record($student_scores, $term, $year, $classId, $courseId)
    {

        foreach ($student_scores as $student_score) {
            $studentId = $student_score['student_id'];
            $scores = $student_score['scores'];
        }
    }
}
