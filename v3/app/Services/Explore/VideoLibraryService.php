<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Utilities\FileHandler;
use V3\App\Models\Explore\Syllabus;
use V3\App\Models\Explore\VideoLibrary;
use V3\App\Models\Portal\Academics\Course;

class VideoLibraryService
{
    private VideoLibrary $videoLibrary;
    private Course $course;
    private Syllabus $syllabus;
    private FileHandler $fileHandler;


    public function __construct(\PDO $pdo)
    {
        $this->videoLibrary = new VideoLibrary($pdo);
        $this->course = new Course($pdo);
        $this->syllabus = new Syllabus($pdo);
        $this->fileHandler = new FileHandler();
    }

    public function addVideos(array $data): int
    {
        $thumbnail = $_FILES['thumbnail'] ?? null;
        $thumbnailUrl = null;

        if ($thumbnail && $thumbnail['error'] === UPLOAD_ERR_OK && is_uploaded_file($thumbnail['tmp_name'])) {
            $tmpName = $thumbnail['tmp_name'];
            $fileName = strtolower(trim(basename($thumbnail['name'])));
            $fileContent = file_get_contents($tmpName);
            $base64 = base64_encode($fileContent);
            $imageMap = [
                [
                    'file_name' => $fileName,
                    'old_file_name' => '',
                    'type' => 'image',
                    'file' => $base64
                ]
            ];

            $processedImages = $this->fileHandler->handleFiles($imageMap);
            $thumbnailUrl = $processedImages[0]['file_name'] ?? null;
        }

        $data['thumbnail_url'] = $thumbnailUrl ?? $data['thumbnail_url'] ?? null;

        // If thumbnail_url is still null and video_url is a YouTube link, extract thumbnail
        if (empty($data['thumbnail_url']) && $this->isYouTubeUrl($data['video_url'])) {
            $data['thumbnail_url'] = $this->getYouTubeThumbnail($data['video_url']);
        }

        $payload = [
            'title' => $data['title'],
            'description' => $data['description'],
            'video_url' => $data['video_url'],
            'course_id' => $data['course_id'],
            'course_name' => $data['course_name'],
            'level_id' => $data['level_id'],
            'level_name' => $data['level_name'],
            'syllabus_id' => $data['syllabus_id'],
            'syllabus_name' => $data['syllabus_name'],
            'topic_id' => $data['topic_id'] ?? null,
            'topic_name' => $data['topic_name'] ?? null,
            'status' => $data['status'],
            'author_id' => $data['author_id'],
            'author_name' => $data['author_name'],
            'thumbnail_url' => $data['thumbnail_url'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        return $this->videoLibrary->insert($payload);
    }

    public function updateVideo(array $data): bool
    {
        $thumbnail = $_FILES['thumbnail'] ?? null;
        $thumbnailUrl = null;

        if ($thumbnail['error'] === UPLOAD_ERR_OK && is_uploaded_file($thumbnail['tmp_name'])) {
            $tmpName = $thumbnail['tmp_name'];
            $fileName = strtolower(trim(basename($thumbnail['name'])));
            $fileContent = file_get_contents($tmpName);
            $base64 = base64_encode($fileContent);
            $imageMap = [
                [
                    'file_name' => $fileName,
                    'old_file_name' => '',
                    'type' => 'image',
                    'file' => $base64
                ]
            ];

            $processedImages = $this->fileHandler->handleFiles($imageMap);
            $thumbnailUrl = $processedImages[0]['file_name'] ?? null;
        }

        if ($thumbnailUrl && !empty($data['old_thumbnail_url'])) {
            $this->fileHandler->deleteOldFile($data['old_thumbnail_url']);
        }


        $data['thumbnail_url'] = $thumbnailUrl ?? $data['thumbnail_url'] ?? null;

        // If thumbnail_url is still null and video_url is a YouTube link, extract thumbnail
        if (empty($data['thumbnail_url']) && $this->isYouTubeUrl($data['video_url'])) {
            $data['thumbnail_url'] = $this->getYouTubeThumbnail($data['video_url']);
        }

        $payload = [
            'title' => $data['title'],
            'description' => $data['description'],
            'video_url' => $data['video_url'],
            'course_id' => $data['course_id'],
            'course_name' => $data['course_name'],
            'level_id' => $data['level_id'],
            'level_name' => $data['level_name'],
            'syllabus_id' => $data['syllabus_id'],
            'syllabus_name' => $data['syllabus_name'],
            'topic_id' => $data['topic_id'] ?? null,
            'topic_name' => $data['topic_name'] ?? null,
            'status' => $data['status'],
            'author_id' => $data['author_id'],
            'author_name' => $data['author_name'],
            'thumbnail_url' => $data['thumbnail_url'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        return $this->videoLibrary
            ->where('id', '=', $data['id'])
            ->update($payload);
    }

    public function updateStatus(int $id, string $status): bool
    {
        return $this->videoLibrary
            ->where('id', '=', $id)
            ->update([
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    public function deleteVideo(int $id): bool
    {
        $oldVideo = $this->videoLibrary
            ->where('id', '=', $id)
            ->first();

        if ($oldVideo && !empty($oldVideo['thumbnail_url'])) {
            $this->fileHandler->deleteOldFile($oldVideo['thumbnail_url']);
        }

        return $this->videoLibrary
            ->where('id', '=', $id)
            ->delete();
    }

    public function getSyllabusByCourse(int $courseId): array
    {
        $result = $this->syllabus
            ->select([
                'syllabi.id',
                'syllabi.course_id',
                'syllabi.name AS syllabus_name',
                'topics.id AS topic_id',
                'topics.name AS topic_name',
            ])
            ->join('topics', 'syllabi.id = topics.syllabus_id')
            ->where('syllabi.course_id', '=', $courseId)
            ->orderBy(['syllabi.name' => 'asc', 'topics.name' => 'asc'])
            ->get();

        // Structure the data with nested topics
        $syllabi = [];
        foreach ($result as $row) {
            $syllabusId = $row['id'];

            // Create or update syllabus entry
            if (!isset($syllabi[$syllabusId])) {
                $syllabi[$syllabusId] = [
                    'id' => $row['id'],
                    'syllabus_name' => $row['syllabus_name'],
                    'course_id' => $row['course_id'],
                    'topics' => []
                ];
            }

            // Add topic to the syllabus
            if (!empty($row['topic_id'])) {
                $syllabi[$syllabusId]['topics'][] = [
                    'topic_id' => $row['topic_id'],
                    'topic_name' => $row['topic_name']
                ];
            }
        }

        return array_values($syllabi);
    }

    /**
     * Fetch all videos with related metadata.
     */
    public function getVideosByCourse(int $courseId): array
    {
        return $this->videoLibrary
            ->where('course_id', '=', $courseId)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function getPublishedVideos(int $levelId, int $courseId): array
    {
        $videos =  $this->videoLibrary
            ->select(columns: [
                'id',
                'title',
                'description',
                'video_url',
                'thumbnail_url',
                'course_id',
                'level_id',
                'course_name',
                'level_name',
                'syllabus_name',
                'syllabus_id',
                'topic_name',
                'topic_id',
                'author_name',
            ])
            ->where('status', '=', 'published')
            ->where('level_id', '=', $levelId)
            ->where('course_id', '=', $courseId)
            ->orderBy('created_at', 'DESC')
            ->get();

        $courses = [];

        foreach ($videos as $video) {
            $courseId = $video['course_id'];
            $syllabusId = $video['syllabus_id'];

            if (!isset($courses[$courseId])) {
                $courses[$courseId] = [
                    'course_id' => $courseId,
                    'course_name' => $video['course_name'],
                    'level_id' => $video['level_id'],
                    'level_name' => $video['level_name'],
                    'syllabi' => []
                ];
            }

            $syllabusKey = $syllabusId;
            if (!isset($courses[$courseId]['syllabi'][$syllabusKey])) {
                $courses[$courseId]['syllabi'][$syllabusKey] = [
                    'syllabus_id' => $syllabusId,
                    'syllabus_name' => $video['syllabus_name'],
                    'videos' => []
                ];
            }

            $courses[$courseId]['syllabi'][$syllabusKey]['videos'][] = [
                'title' => $video['title'],
                'topic_id' => $video['topic_id'] ?? null,
                'topic_name' => $video['topic_name'] ?? null,
                'description' => $video['description'],
                'video_url' => $video['video_url'],
                'thumbnail_url' => $video['thumbnail_url'] ?? '',
                'author_name' => $video['author_name'] ?? '',
            ];
        }

        // Normalize nested arrays for API friendliness
        foreach ($courses as &$course) {
            $course['syllabi'] = array_values($course['syllabi']);
        }
        unset($course);

        return array_values($courses);
    }

    /**
     * Fetch list of available courses.
     */
    public function getCourses(): array
    {
        return $this->course
            ->select(columns: [
                'course_table.id',
                'course_table.course_name',
                'course_table.description',
                'COUNT(video_libraries.id) AS video_count'
            ])
            ->join('video_libraries', 'course_table.id = video_libraries.course_id', 'LEFT')
            ->groupBy(['course_table.id', 'course_table.course_name', 'course_table.description'])
            ->orderBy('course_table.course_name')
            ->get();
    }

    public function index(int $levelId): array
    {
        $videos =  $this->videoLibrary
            ->select(columns: [
                'id',
                'title',
                'description',
                'video_url',
                'thumbnail_url',
                'course_id',
                'level_id',
                'course_name',
                'level_name',
                'syllabus_name',
                'syllabus_id',
                'topic_name',
                'topic_id',
                'author_name',
            ])
            ->where('status', '=', 'published')
            ->where('level_id', '=', $levelId)
            ->orderBy('created_at', 'DESC')
            ->get();

        $courses = $this->course
            ->select(columns: [
                'course_table.id',
                'course_table.course_name',
                'course_table.description'
            ])
            ->join('video_libraries', 'course_table.id = video_libraries.course_id')
            ->where('video_libraries.level_id', '=', $levelId)
            ->orderBy('course_table.course_name')
            ->groupBy(['course_table.id', 'course_table.course_name', 'course_table.description'])
            ->get();

        return [
            'recommended' => $videos,
            'courses' => $courses
        ];
    }

    /**
     * Check if the provided URL is a YouTube URL
     */
    private function isYouTubeUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return in_array($host, [
            'www.youtube.com',
            'youtube.com',
            'm.youtube.com',
            'youtu.be'
        ], true);
    }


    /**
     * Extract video ID from YouTube URL
     */
    private function getYouTubeVideoId(string $url): ?string
    {
        // Pattern for youtube.com/watch?v=ID
        if (preg_match('/[?&]v=([^&]+)/', $url, $matches)) {
            return $matches[1];
        }

        // Pattern for youtu.be/ID
        if (preg_match('/youtu\.be\/([^?&]+)/', $url, $matches)) {
            return $matches[1];
        }

        // Pattern for youtube.com/embed/ID
        if (preg_match('/\/embed\/([^?&]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get YouTube thumbnail URL for a given video
     */
    private function getYouTubeThumbnail(string $url): ?string
    {
        $videoId = $this->getYouTubeVideoId($url);

        return $videoId
            ? "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg"
            : null;
    }
}
