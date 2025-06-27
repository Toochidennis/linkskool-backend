<?php

namespace V3\App\Controllers\Portal\ELearning;

use Exception;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\ELearning\MaterialService;

class MaterialController extends BaseController
{
    private MaterialService $materialService;

    public function __construct()
    {
        parent::__construct();
        $this->materialService = new MaterialService($this->pdo);
    }

    public function store()
    {
        $data = $this->validateData(
            data: $this->post,
            requiredFields: [
                'title',
                'description',
                'topic',
                'topic_id',
                'syllabus_id',
                'creator_name',
                'creator_id',
                'classes.*.id',
                'classes.*.name',
                'files.*.file_name',
                'files.*.type',
                'files.*.file',
            ]
        );

        try {
            $id = $this->materialService->addMaterial($data);

            if ($id > 0) {
                return $this->respond(
                    data: [
                        'success' => true,
                        'message' => 'Material added successfully.',
                        'id' => $id
                    ],
                    statusCode: HttpStatus::CREATED
                );
            }

            return $this->respondError('Failed to add material');
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function update(array $vars)
    {
        $data = $this->validateData(
            data: $this->post + $vars,
            requiredFields: [
                'id',
                'title',
                'description',
                'topic',
                'topic_id',
                'classes.*.id',
                'classes.*.name',
                'files.*.old_file_name',
                'files.*.type',
            ]
        );

        try {
            $id = $this->materialService->updateMaterial($data);

            if ($id > 0) {
                return $this->respond(
                    data: [
                        'success' => true,
                        'message' => 'Material updated successfully.',
                        'materialId' => $id
                    ]
                );
            }

            return $this->respondError('Failed to update material');
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function delete(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['id']);

        try {
            if ($this->materialService->deleteMaterial($data['id'])) {
                return $this->respond(
                    data: [
                        'success' => true,
                        'message' => 'Material deleted successfully.'
                    ]
                );
            }

            return $this->respondError('Material not found or already deleted.');
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
