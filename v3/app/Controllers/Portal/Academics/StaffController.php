<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Academics\StaffService;

class StaffController extends BaseController
{
    private StaffService $staffService;

    public function __construct()
    {
        parent::__construct();
        $this->staffService = new StaffService($this->pdo);
    }

    /**
     * Adds a new staff.
     */
    public function addStaff()
    {
        $data = $this->validateData(
            data: $this->post,
            requiredFields: ['surname', 'first_name', 'sex']
        );
        $data['password'] = $this->staffService->generatePassword($data['surname']);

        try {
            $newId = $this->staffService->insertStaffRecord($data);

            if ($newId) {
                $this->respond([
                    'success' => true,
                    'message' => 'Staff added successfully.'
                ], HttpStatus::CREATED);
            }

            return $this->respondError('Failed to add a new staff');
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getStaff()
    {
        try {
            $this->respond(
                [
                    'success' => true,
                    'response' => $this->staffService->getStaff()
                ]
            );
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
