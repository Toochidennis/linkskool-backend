<?php

namespace V3\App\Controllers\Portal\ELearning;

use V3\App\Controllers\BaseController;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\ELearning\SyllabusService;

#[Group('/portal')]
class SyllabusController extends BaseController
{
    private SyllabusService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new SyllabusService($this->pdo);
    }

    #[Route('/elearning/syllabus', 'POST', ['auth', 'role:admin', 'role:staff'])]
    public function store()
    {
        $data = $this->validate(
            data: $this->post,
            rules: [
                'title' => 'required|string|filled',
                'description' => 'required|string|filled',
                'course_id' => 'required|integer|min:1',
                'course_name' => 'required|string|filled',
                'level_id' => 'required|integer|min:1',
                'classes' => 'required|array|min:1',
                'classes.*.id' => 'required|integer|min:1',
                'classes.*.name' => 'required|string|filled',
                'creator_id' => 'required|integer|min:1',
                'creator_name' => 'required|string|filled',
                'term' => 'required|integer|in:1,2,3'
            ]
        );

        $newId = $this->service->create($data);

        if ($newId > 0) {
            $this->respond(
                data: [
                    'success' => true,
                    'message' => 'Syllabus created successfully.'
                ],
                statusCode: HttpStatus::CREATED
            );
        }

        $this->respondError('Failed to create syllabus', HttpStatus::BAD_REQUEST);
    }

    #[Route('/elearning/syllabus/{id:\d+}', 'PUT', ['auth', 'role:admin', 'role:staff'])]
    public function update(array $vars)
    {
        $data = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'id' => 'required|integer',
                'title' => 'required|string|filled',
                'description' => 'required|string|filled',
                'classes' => 'required|array|min:1',
                'classes.*.id' => 'required|integer',
                'classes.*.name' => 'required|string|filled'
            ]
        );

        $id = $this->service->updateSyllabus($data);

        if ($id > 0) {
            $this->respond(
                data: [
                    'success' => true,
                    'message' => 'Syllabus updated successfully.'
                ]
            );
        }

        return $this->respondError('No changes', HttpStatus::INFO);
    }

    #[Route('/elearning/syllabus', 'GET', ['auth', 'role:admin'])]
    public function get(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'term' => 'required|integer|in:1,2,3',
                'level_id' => 'required|integer',
                'course_id' => 'required|integer'
            ]
        );

        $this->respond(
            data: [
                'success' => true,
                'response' => $this->service->getSyllabus(filters: $data)
            ]
        );
    }

    #[Route('/elearning/syllabus/staff', 'GET', ['auth', 'role:staff'])]
    public function getByStaff(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'term' => 'required|integer|in:1,2,3',
                'level_id' => 'required|integer',
                'course_id' => 'required|integer',
                'class_id' => 'required|integer|min:1'
            ]
        );

        return $this->respond(
            data: [
                'success' => true,
                'response' => $this->service->getSyllabusByStaff(filters: $data)
            ]
        );
    }

    #[Route('/elearning/syllabus/{id:\d+}', 'DELETE', ['auth', 'role:admin', 'role:staff'])]
    public function delete(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'id' => 'required|integer|min:1'
            ]
        );

        $deleted = $this->service->deleteSyllabus($data['id']);

        if ($deleted) {
            return $this->respond(
                data: [
                    'success' => true,
                    'message' => 'Syllabus deleted successfully.'
                ]
            );
        }

        return $this->respondError(
            'Failed to delete syllabus',
            HttpStatus::BAD_REQUEST
        );
    }
}
