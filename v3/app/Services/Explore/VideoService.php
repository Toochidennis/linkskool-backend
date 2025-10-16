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
        $this->video     = new Video($pdo);
        $this->course    = new Course($pdo);
        $this->category  = new Category($pdo);
        $this->examType  = new ExamType($pdo);
    }

    /**
     * Fetch all videos with related metadata.
     */
    public function getVideos(): array
    {
        $videos = $this->video
            ->select(['id', 'title', 'description', 'course_id', 'category_id', 'exam_type_id', 'url'])
            ->orderBy('id', 'DESC')
            ->get();

        if (!$videos) {
            return [];
        }

        // Load reference data
        $courses   = $this->loadCourse();
        $categories = $this->loadCategory();
        $examTypes = $this->loadExamType();

        // Map data for quick lookup
        $courseMap = array_column($courses, 'course_name', 'id');
        $categoryMap = [];
        foreach ($categories as $c) {
            $categoryMap[$c['id']] = [
                'course_id' => $c['course_id'],
                'category_name' => $c['category_name']
            ];
        }

        // Attach related info to videos
        return array_map(function ($video) use ($courseMap, $categoryMap, $examTypes) {
            $video['course_name'] = $courseMap[$video['course_id']] ?? null;
            $video['category'] = $categoryMap[$video['category_id']]['category_name'] ?? null;
            $video['exam_type'] = $examTypes[$video['exam_type_id']]['title'] ?? null;
            $video['exam_type_meta'] = $examTypes[$video['exam_type_id']] ?? null;
            return $video;
        }, $videos);
    }

    /**
     * Fetch list of available courses.
     */
    private function loadCourse(): array
    {
        return $this->course
            ->select(['id', 'course_name'])
            ->orderBy('course_name')
            ->get();
    }

    /**
     * Fetch list of video categories by course.
     */
    private function loadCategory(): array
    {
        return $this->category
            ->select(['id', 'course_id', 'categoryName AS category_name'])
            ->orderBy('categoryName')
            ->get();
    }

    /**
     * Fetch exam type metadata and map by ID.
     */
    private function loadExamType(): array
    {
        $rows = $this->examType
            ->select([
                'id',
                'title',
                'description',
                'shortname',
                'picref'
            ])
            ->get();

        $meta = [];
        foreach ($rows as $row) {
            $meta[$row['id']] = [
                'title' => $row['title'],
                'desc' => $row['description'],
                'short' => $row['shortname'],
                'pic' => $this->formatRef($row['picref'] ?? '')
            ];
        }

        return $meta;
    }

    /**
     * Format absolute image path for thumbnails.
     */
    private function formatRef(string $url): string
    {
        return "http://" . $_SERVER['SERVER_NAME'] . "/$url";
    }
}
