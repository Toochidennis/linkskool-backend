<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\Video;
use V3\App\Models\Explore\Category;
use V3\App\Models\Explore\ExamType;
use V3\App\Models\Portal\Academics\Course;

class VideoService
{
    private Video $video;
    private Course $course;
    private Category $category;

    private ExamType $examType;

    public function __construct(\PDO $pdo)
    {
        $this->video = new Video($pdo);
    }

    public function getVideos()
    {
        $results = $this->video
            ->select([])
            ->get();
    }

    private function loadCourse()
    {
        return $this->course
        ->select(['id', 'course_name'])
        ->orderBy('course_name')
        ->get();
    }

    private function loadCategory()
    {
        return $this->category
        ->select(['id', 'course_id', 'categoryName as category_name'])
        ->orderBy('categoryName')
        get();
    }

    private function loadExamType()
    {
        
    }
}
