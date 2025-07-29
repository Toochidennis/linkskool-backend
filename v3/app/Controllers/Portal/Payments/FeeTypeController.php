<?php

namespace V3\App\Controllers\Portal\Payments;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Payments\FeeTypeService;

class FeeTypeController extends BaseController
{
    private FeeTypeService $feeTypeService;

    public function __construct()
    {
        parent::__construct();
        $this->feeTypeService = new FeeTypeService($this->pdo);
    }

    public function store()
    {
        $cleanedData = $this->validate(
            data: $this->post,
            rules: [
                'fee_name' => 'required|string|filled',
                'is_mandatory' => 'required|integer',
            ]
        );

        try {
            $newId = $this->feeTypeService->addFeeName($cleanedData);

            if ($newId > 0) {
                $this->respond(
                    [
                        'success' => true,
                        'message' => 'Fee name added successfully'
                    ],
                    HttpStatus::CREATED
                );
            }

            $this->respondError('Failed to add fee name. Maybe it already exist', HttpStatus::BAD_REQUEST);
        } catch (\Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function update(array $vars)
    {
        $cleanedData = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'id' => 'required|integer',
                'fee_name' => 'required|string|filled',
                'is_mandatory' => 'required|integer',
            ]
        );

        try {
            $newId = $this->feeTypeService->updateFeeName($cleanedData);

            if ($newId > 0) {
                $this->respond(
                    [
                        'success' => true,
                        'message' => 'Fee name updated successfully'
                    ],
                );
            }

            $this->respondError(
                'Failed to update fee name. Maybe it already exist',
                HttpStatus::BAD_REQUEST
            );
        } catch (\Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function get()
    {
        try {
            $this->respond([
                'success' => true,
                'response' => $this->feeTypeService->getFeeNames(),
            ]);
        } catch (\Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function delete(array $vars)
    {
        $cleanedData = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'id' => 'required|integer',
                'term' => 'required|integer',
                'year' => 'required|integer',
            ]
        );

        try {
            $newId = $this->feeTypeService->deleteFeeName($cleanedData);

            if ($newId > 0) {
                $this->respond(
                    [
                        'success' => true,
                        'message' => 'Fee name deleted successfully'
                    ],
                );
            }

            $this->respondError(
                'Failed to delete fee name. Maybe it doesn\'t exist',
                HttpStatus::BAD_REQUEST
            );
        } catch (\Exception $e) {
            $this->respondError($e->getMessage());
        }
    }
}
