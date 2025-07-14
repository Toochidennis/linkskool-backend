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
        $data = $this->validate(
            data: $this->post,
            rules: [
                'title' => 'required|string|filled',
                'description' => 'required|string|filled',
                'topic' => 'sometimes|string',
                'topic_id' => 'sometimes|integer',
                'syllabus_id' => 'required|integer',
                'creator_name' => 'required|string|filled',
                'creator_id' => 'required|integer',
                'classes' => 'required|array|min:1',
                'classes.*.id' => 'required|integer',
                'classes.*.name' => 'required|string|filled',
                'files' => 'sometimes|array',
                'files.*.file_name' => 'required|string|filled',
                'files.*.old_file_name' => 'sometimes|string',
                'files.*.type' => 'required|string|filled',
                'files.*.file' => 'sometimes|string',
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
                'id' => 'required|integer',
                'title' => 'required|string|filled',
                'description' => 'required|string|filled',
                'topic' => 'sometimes|string',
                'topic_id' => 'sometimes|integer',
                'classes' => 'required|array|min:1',
                'classes.*.id' => 'required|integer',
                'classes.*.name' => 'required|string|filled',
                'files' => 'sometimes|array',
                'files.*.file_name' => 'sometimes|string',
                'files.*.old_file_name' => 'required|string|filled',
                'files.*.type' => 'required|string|filled',
                'files.*.file' => 'sometimes|string',
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
        $data = $this->validate(data: $vars, rules: ['id' => 'required|integer']);

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
