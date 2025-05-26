<?php

namespace V3\App\Controllers\Portal\ELearning;

use Exception;
use V3\App\Utilities\HttpStatus;
use V3\App\Traits\ValidationTrait;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\ELearning\SyllabusService;

class SyllabusController extends BaseController
{
    use ValidationTrait;

    private SyllabusService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new SyllabusService($this->pdo);
    }

    public function store()
    {
        $data = $this->validateData(
            data: $this->post,
            requiredFields: [
                'title',
                'description',
                'course_id',
                'level_id',
                'class_ids',
                'creator_id',
                'creator_role',
                'term',
                'year'
            ]
        );

        try {
            $inserted = $this->service->create($data);

            if ($inserted) {
                return $this->respond(
                    ['success' => true, 'message' => 'Syllabus created successfully!'],
                    HttpStatus::CREATED
                );
            }

            return $this->respondError('Failed to create syllabus', HttpStatus::BAD_REQUEST);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function update(array $vars)
    {
        // TODO
    }
}
