<?php

namespace V3\App\Controllers\Explore\Classroom;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\Explore\ExploreBaseController;
use V3\App\Services\Explore\Classroom\ClassroomCourseLessonService;

#[Group('/public/classroom/courses')]
class ClassroomCourseLessonController extends ExploreBaseController
{
    private ClassroomCourseLessonService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ClassroomCourseLessonService($this->pdo);
    }

    #[Route('/{course_id}/lessons', 'POST', ['api'])]
    public function addLesson(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'course_id'                  => 'required|integer',
                'institution_id'             => 'required|integer',
                'title'                      => 'required|string|max:255',
                'description'                => 'nullable|string',
                'goals'                      => 'nullable|string',
                'objectives'                 => 'nullable|string',
                'video_url'                  => 'nullable|string',
                'recorded_video_url'         => 'nullable|string',
                'display_order'              => 'required|integer',
                'write_up_content'           => 'nullable|string',
                'assignment_instructions'    => 'nullable|string',
                'assignment_due_date'        => 'nullable|date',
                'assignment_submission_type' => 'required_with:assignment_due_date|string|in:file,text,link,mixed',
                'is_final_lesson'            => 'required|boolean',
                'author_name'                => 'required|string|max:255',
                'author_id'                  => 'required|integer',
                'lesson_date'                => 'required|date',
                'status'                     => 'required|string|in:draft,published,archived',
                'zoom_info'                  => 'nullable|array',
                'zoom_info.url'              => 'nullable|string',
                'zoom_info.meeting_id'       => 'nullable|string',
                'zoom_info.passcode'         => 'nullable|string',
                'zoom_info.start_time'       => 'required_with:zoom_info.url|date',
                'zoom_info.end_time'         => 'required_with:zoom_info.url|date',

                'material'          => 'nullable|array',
                'material.name'     => 'required_with:material|string',
                'material.tmp_name' => 'required_with:material|string',
                'material.error'    => 'required_with:material|integer',

                'assignment'           => 'nullable|array',
                'assignment.name'      => 'required_with:assignment|string',
                'assignment.tmp_name'  => 'required_with:assignment|string',
                'assignment.error'     => 'required_with:assignment|integer',

                'certificate'          => 'nullable|array',
                'certificate.name'     => 'required_with:certificate|string',
                'certificate.tmp_name' => 'required_with:certificate|string',
                'certificate.error'    => 'required_with:certificate|integer',
            ]
        );

        $lessonId = $this->service->addLesson($validated);

        if (!$lessonId) {
            $this->respondError('Failed to add lesson.', HttpStatus::BAD_REQUEST);
        }

        $this->respond(
            [
                'status'  => true,
                'message' => 'Lesson added successfully.',
                'data'    => ['lesson_id' => $lessonId],
            ],
            HttpStatus::CREATED
        );
    }

    #[Route('/{course_id}/lessons/{lesson_id}', 'POST', ['api'])]
    public function updateLesson(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'lesson_id'                  => 'required|integer',
                'course_id'                  => 'required|integer',
                'institution_id'             => 'required|integer',
                'title'                      => 'required|string|max:255',
                'description'                => 'nullable|string',
                'goals'                      => 'nullable|string',
                'objectives'                 => 'nullable|string',
                'video_url'                  => 'nullable|string',
                'recorded_video_url'         => 'nullable|string',
                'display_order'              => 'required|integer',
                'write_up_content'           => 'nullable|string',
                'assignment_instructions'    => 'nullable|string',
                'assignment_due_date'        => 'nullable|date',
                'assignment_submission_type' => 'required_with:assignment_due_date|string|in:file,text,link,mixed',
                'is_final_lesson'            => 'required|boolean',
                'author_name'                => 'required|string|max:255',
                'author_id'                  => 'required|integer',
                'lesson_date'                => 'required|date',
                'status'                     => 'required|string|in:draft,published,archived',
                'zoom_info'                  => 'nullable|array',
                'zoom_info.url'              => 'nullable|string',
                'zoom_info.meeting_id'       => 'nullable|string',
                'zoom_info.passcode'         => 'nullable|string',
                'zoom_info.start_time'       => 'required_with:zoom_info.url|date',
                'zoom_info.end_time'         => 'required_with:zoom_info.url|date',

                'material'          => 'nullable|array',
                'material.name'     => 'required_with:material|string',
                'material.tmp_name' => 'required_with:material|string',
                'material.error'    => 'required_with:material|integer',

                'assignment'           => 'nullable|array',
                'assignment.name'      => 'required_with:assignment|string',
                'assignment.tmp_name'  => 'required_with:assignment|string',
                'assignment.error'     => 'required_with:assignment|integer',

                'certificate'          => 'nullable|array',
                'certificate.name'     => 'required_with:certificate|string',
                'certificate.tmp_name' => 'required_with:certificate|string',
                'certificate.error'    => 'required_with:certificate|integer',
            ]
        );

        $updated = $this->service->updateLesson($validated);

        if (!$updated) {
            $this->respondError('Failed to update lesson.', HttpStatus::BAD_REQUEST);
        }

        $this->respond(
            [
                'status'  => true,
                'message' => 'Lesson updated successfully.',
            ],
            HttpStatus::OK
        );
    }

    #[Route('/lessons/{lesson_id}/status', 'PUT', ['api'])]
    public function updateStatus(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'lesson_id' => 'required|integer',
                'status'    => 'required|string|in:draft,published,archived',
            ]
        );

        $updated = $this->service->updateStatus(
            (int) $validated['lesson_id'],
            $validated['status']
        );

        if (!$updated) {
            $this->respondError('Failed to update lesson status.', HttpStatus::BAD_REQUEST);
        }

        $this->respond(
            [
                'status'  => true,
                'message' => 'Lesson status updated successfully.',
            ],
            HttpStatus::OK
        );
    }

    #[Route('/{course_id}/lessons', 'GET', ['api'])]
    public function getLessons(array $vars): void
    {
        $validated = $this->validate(
            $vars,
            ['course_id' => 'required|integer']
        );

        $lessons = $this->service->getLessonsByCourseId((int) $validated['course_id']);

        $this->respond(
            [
                'status' => true,
                'data'   => $lessons,
            ],
            HttpStatus::OK
        );
    }

    #[Route('/lessons/{lesson_id}', 'DELETE', ['api'])]
    public function deleteLesson(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            ['lesson_id' => 'required|integer']
        );

        $deleted = $this->service->deleteLesson((int) $validated['lesson_id']);

        if (!$deleted) {
            $this->respondError('Failed to delete lesson.', HttpStatus::BAD_REQUEST);
        }

        $this->respond(
            [
                'status'  => true,
                'message' => 'Lesson deleted successfully.',
            ],
            HttpStatus::OK
        );
    }
}
