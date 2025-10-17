<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\Video;
use V3\App\Models\Explore\Category;
use V3\App\Models\Portal\Academics\Course;

class VideoService
{
    private Video $video;
    private Course $course;
    private Category $category;

    public function __construct(\PDO $pdo)
    {
        $this->video     = new Video($pdo);
        $this->course    = new Course($pdo);
        $this->category  = new Category($pdo);
    }

    /**
     * Fetch all videos with related metadata.
     */
    public function getVideos(): array
    {
        $videos = $this->video
            ->select([
                'videosTable.id',
                'videosTable.videoTitle as title',
                'videosTable.thumbnail',
                'videosTable.course_id',
                'videosTable.categoryId',
                'videosTable.videoUrl as url',
                'level.name as level_name'
            ])
            ->join('level', 'level.id = videosTable.level')
            ->where('videoUrl', '<>', '')
            ->get();

        if (!$videos) {
            return [];
        }

        // Load reference data
        $courses   = $this->loadCourse();
        $categories = $this->loadCategory();

        // Map data for quick lookup
        $courseMap = [];
        foreach ($courses as $c) {
            $courseMap[$c['id']] = $c['course_name'];
        }

        $categoryMap = [];
        foreach ($categories as $c) {
            $categoryMap[$c['id']] = [
                'course_id' => $c['course_id'],
                'category_name' => $c['category_name'],
            ];
        }

        $arr = [];
        $vtr = [];

        // Rebuild array structure
        foreach ($videos as $row) {
            $courseId  = $row['course_id'];
            $categoryId = $row['categoryId'];

            // Set up course block
            if (!isset($arr[$courseId])) {
                $arr[$courseId] = [
                    'id' => $courseId,
                    'name' => $courseMap[$courseId] ?? 'Unknown Course',
                    'category' => [],
                ];
            }

            // Set up category block
            if (!isset($vtr[$courseId][$categoryId])) {
                $vtr[$courseId][$categoryId] = [
                    'id' => $categoryId,
                    'level' => $row['level'],
                    'level_name' => $row['level_name'],
                    'name' => $categoryMap[$categoryId]['category_name'] ?? 'Unknown Category',
                    'videos' => [],
                ];
            }

            // Format YouTube embed URL
            $videoUrl = "https://www.youtube.com/embed/" . $row['videoUrl'] . "?modestbranding=0&showinfo=0";

            // Append video to category
            $vtr[$courseId][$categoryId]['videos'][] = [
                'id' => $row['id'],
                'title' => $row['videoTitle'],
                'url' => $videoUrl,
                'thumbnail' => $row['thumbnail'],
            ];

            // Attach to course
            $arr[$courseId]['category'] = $vtr[$courseId];
        }

        // Sort alphabetically by course name and category name
        usort($arr, fn($a, $b) => strcmp($a['name'], $b['name']));
        foreach ($arr as &$course) {
            $categories = array_values($course['category']);
            usort($categories, fn($a, $b) => strcmp($a['name'], $b['name']));
            $course['category'] = $categories;
        }

        return array_values($arr);
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
     * Format absolute image path for thumbnails.
     */
    private function formatRef(string $url): string
    {
        return "http://" . $_SERVER['SERVER_NAME'] . "/$url";
    }
}
