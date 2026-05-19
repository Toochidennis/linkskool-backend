<?php

namespace V3\App\Controllers\Explore\Classroom;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\Explore\ExploreBaseController;
use V3\App\Services\Explore\Classroom\ClassroomCourseService;

#[Group('/public/classroom/courses')]
class ClassroomCourseController extends ExploreBaseController
{
    private ClassroomCourseService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ClassroomCourseService($this->pdo);
    }

    #[Route('', 'POST', ['api'])]
    public function createCourse(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'name' => 'required|string',
                'institution_id' => 'required|integer',
                'created_by' => 'required|integer',
                'status' => 'required|string|in:draft,published,archived',
                'join_code' => 'required|string',
                'description' => 'nullable|string',
                'subject_id' => 'nullable|integer',
                'level_id' => 'nullable|integer',
                'duration' => 'nullable|string',
                'pricing_type'   => 'nullable|string',
                'price' => 'nullable|numeric',
                'discount_price' => 'nullable|numeric',
            ],
        );

        $created = $this->service->createCourse($validated);

        if (!$created) {
            $this->respondError(
                'Course creation failed.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'status'  => true,
                'message' => 'Course created successfully.',
            ],
            HttpStatus::CREATED
        );
    }

    #[Route('/{id}', 'GET', ['api'])]
    public function getCourseById(array $vars): void
    {
        $course = $this->service->getCourseById((int) $vars['id']);

        if (empty($course)) {
            $this->respondError('Course not found.', HttpStatus::NOT_FOUND);
        }

        $this->respond(
            [
                'status' => true,
                'data'   => $course,
            ],
            HttpStatus::OK
        );
    }

    #[Route('', 'GET', ['api'])]
    public function getCoursesByInstitution(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'institution_id' => 'required|integer',
                'level_id'  => 'nullable|integer',
                'status'  => 'nullable|string|in:draft,published,archived',
                'subject_id' => 'nullable|integer',
                'page' => 'nullable|integer',
                'limit' => 'nullable|integer',
            ],
        );

        $courses = $this->service->getCoursesByInstitution(
            (int) $validated['institution_id'],
            [
                'level_id'   => $validated['level_id'] ?? null,
                'status'     => $validated['status'] ?? null,
                'subject_id' => $validated['subject_id'] ?? null,
            ],
            (int) ($validated['page'] ?? 1),
            (int) ($validated['limit'] ?? 20),
        );

        $this->respond(
            [
                'status' => true,
                'data'   => $courses['data'],
                'meta'   => $courses['meta'],
            ],
            HttpStatus::OK
        );
    }

    #[Route('/{id}', 'PUT', ['api'])]
    public function updateCourse(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id'             => 'required|integer',
                'name'           => 'required|string',
                'status'         => 'required|string|in:draft,published,archived',
                'description'    => 'nullable|string',
                'subject_id'     => 'nullable|integer',
                'level_id'       => 'nullable|integer',
                'duration'       => 'nullable|string',
                'pricing_type'   => 'nullable|string',
                'price'          => 'nullable|numeric',
                'discount_price' => 'nullable|numeric',
                'old_image_url'  => 'nullable|string',
            ],
        );

        $updated = $this->service->updateCourse((int) $validated['id'], $validated);

        if (!$updated) {
            $this->respondError(
                'Course update failed.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'status'  => true,
                'message' => 'Course updated successfully.',
            ],
            HttpStatus::OK
        );
    }

    #[Route('/{id}/status', 'PATCH', ['api'])]
    public function updateStatus(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer',
                'status' => 'required|string|in:draft,published,archived',
            ]
        );

        $updated = $this->service->updateCourseStatus(
            (int) $validated['id'],
            $validated['status']
        );

        if (!$updated) {
            $this->respondError('Failed to update course status.', HttpStatus::BAD_REQUEST);
        }

        $this->respond(
            [
                'status'  => true,
                'message' => 'Course status updated successfully.',
            ],
            HttpStatus::OK
        );
    }
}
