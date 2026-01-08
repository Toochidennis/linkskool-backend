<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Common\Utilities\ResponseHandler;
use V3\App\Database\DatabaseConnector;
use V3\App\Services\Explore\VideoLibraryService;

#[Group("/public/video-library")]
class VideoLibraryController extends ExploreBaseController
{
    private VideoLibraryService $videoLibraryService;

    public function __construct()
    {
        parent::__construct();
        $this->videoLibraryService = new VideoLibraryService($this->pdo);
    }

    #[Route('/videos', 'POST', ['api', 'auth'])]
    public function addVideo()
    {
        $validated = $this->validate(
            $this->getRequestData(),
            [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'video_url' => 'required|url',
                'course_id' => 'required|integer',
                'course_name' => 'required|string|max:255',
                'level_id' => 'required|integer',
                'level_name' => 'required|string|max:255',
                'syllabus_id' => 'required|integer',
                'syllabus_name' => 'required|string|max:255',
                'topic_id' => 'nullable|integer',
                'topic_name' => 'nullable|string|max:255',
                'status' => 'required|in:draft,published,archived',
                'author_id' => 'required|integer',
                'author_name' => 'required|string|max:100',

                'thumbnail_url' => 'nullable|url',
                'thumbnail' => 'nullable|array',
                'thumbnail.name' => 'nullable|string',
                'thumbnail.tmp_name' => 'nullable|string',
                'thumbnail.error' => 'nullable|integer',
                'thumbnail.size' => 'nullable|integer'
            ]
        );

        $videoId = $this->videoLibraryService->addVideos($validated);

        if (!$videoId) {
            $this->respondError(
                'Failed to add video.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Video added successfully.',
                'id' => $videoId
            ],
            HttpStatus::CREATED
        );
    }

    #[Route('/videos', 'GET', ['api', 'auth'])]
    public function getAllVideos()
    {
        $videos = $this->videoLibraryService->getAllVideos();
        $this->respond(
            [
                'success' => true,
                'data' => $videos
            ],
            HttpStatus::OK
        );
    }

    #[Route('/courses', 'GET', ['api', 'auth'])]
    public function getCourses()
    {
        $courses = $this->videoLibraryService->getCourses();
        $this->respond(
            [
                'success' => true,
                'data' => $courses
            ],
            HttpStatus::OK
        );
    }

    #[Route('/syllabi/{course_id}', 'GET', ['api'])]
    public function getSyllabiByCourse(array $vars)
    {
        $validated = $this->validate(
            $vars,
            [
                'course_id' => 'required|integer'
            ]
        );

        $syllabi = $this->videoLibraryService->getSyllabusByCourse($validated['course_id']);

        $this->respond(
            [
                'success' => true,
                'data' => $syllabi
            ],
            HttpStatus::OK
        );
    }

    #[Route('/published/{level_id}', 'GET', ['api'])]
    public function getPublishedVideosByLevel(array $vars)
    {
        $validated = $this->validate(
            $vars,
            [
                'level_id' => 'required|integer'
            ]
        );

        $videos = $this->videoLibraryService->getPublishedVideos($validated['level_id']);

        $this->respond(
            [
                'success' => true,
                'data' => $videos
            ],
            HttpStatus::OK
        );
    }

    #[Route('/{id}/status', 'PUT', ['api', 'auth'])]
    public function updateVideoStatus(array $vars)
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer',
                'status' => 'required|in:draft,published,archived'
            ]
        );

        $updated = $this->videoLibraryService->updateStatus(
            $validated['id'],
            $validated['status']
        );

        if (!$updated) {
            $this->respondError(
                'Failed to update video status.',
                HttpStatus::BAD_REQUEST
            );
            return;
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Video status updated successfully.'
            ],
            HttpStatus::OK
        );
    }

    #[Route('/{id}', 'DELETE', ['api', 'auth'])]
    public function deleteVideo(array $vars)
    {
        $validated = $this->validate(
            $vars,
            [
                'id' => 'required|integer'
            ]
        );

        $deleted = $this->videoLibraryService->deleteVideo(
            $validated['id']
        );

        if (!$deleted) {
            $this->respondError(
                'Failed to delete video.',
                HttpStatus::BAD_REQUEST
            );
            return;
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Video deleted successfully.'
            ],
            HttpStatus::OK
        );
    }
}
