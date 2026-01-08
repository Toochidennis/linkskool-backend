<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Utilities\FileHandler;
use V3\App\Models\Explore\VideoLibrary;
use V3\App\Models\Explore\Category;
use V3\App\Models\Portal\Academics\Course;
use V3\App\Models\Portal\ELearning\SyllabusModel;

class VideoLibraryService
{
    private VideoLibrary $videoLibrary;
    private Course $course;
    private Category $category;
    private SyllabusModel $syllabus;
    private FileHandler $fileHandler;


    public function __construct(\PDO $pdo)
    {
        $this->videoLibrary = new VideoLibrary($pdo);
        $this->course = new Course($pdo);
    }

    public function addVideos(array $data): int
    {
        $thumbnail = $data['thumbnail'] ?? null;
        $thumbnailUrl = null;

        if ($thumbnail?->isValid() && is_uploaded_file($thumbnail->getRealPath())) {
            $tmpName = $thumbnail->getRealPath();
            $fileName = strtolower(trim(basename($thumbnail->getClientFilename())));
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

        $data['thumbnail_url'] = $thumbnailUrl ?? $data['thumbnail_url'];

        return $this->videoLibrary->insert($data);
    }

    public function updateVideo(int $id, array $data): bool
    {
        $thumbnail = $data['thumbnail'] ?? null;
        $thumbnailUrl = null;

        if ($thumbnail?->isValid() && is_uploaded_file($thumbnail->getRealPath())) {
            $tmpName = $thumbnail->getRealPath();
            $fileName = strtolower(trim(basename($thumbnail->getClientFilename())));
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

        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->videoLibrary
            ->where('id', '=', $id)
            ->update($data);
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
    public function getAllVideos(): array
    {
        return $this->videoLibrary
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function getPublishedVideos(int $levelId): array
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

        $courses = [];

        foreach ($videos as $video) {
            $courseId = $video['course_id'];
            $syllabusId = $video['syllabus_id'];

            if (!isset($courses[$courseId])) {
                $courses[$courseId] = [
                    'course_id' => $courseId,
                    'course_name' => $video['course_name'],
                    'syllabi' => []
                ];
            }

            $syllabusKey = $syllabusId;
            if (!isset($courses[$courseId]['syllabi'][$syllabusKey])) {
                $courses[$courseId]['syllabi'][$syllabusKey] = [
                    'syllabus_id' => $syllabusId,
                    'syllabus_name' => $video['syllabus_name'],
                    'contents' => []
                ];
            }

            $courses[$courseId]['syllabi'][$syllabusKey]['contents'][] = [
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
                'courses.id',
                'courses.course_name',
                'courses.description',
                'COUNT(video_libraries.id) AS video_count'
            ])
            ->join('video_libraries', 'courses.id = video_libraries.course_id', 'LEFT')
            ->groupBy(['courses.id', 'courses.course_name', 'courses.description'])
            ->orderBy('courses.course_name')
            ->get();
    }
}
