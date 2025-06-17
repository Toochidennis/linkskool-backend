<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Models\Portal\Staff;
use V3\App\Services\Portal\Academics\StaffService;
use V3\App\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;

/**
 * Class StaffController
 *
 * Handles staff-related operations.
 */

class StaffController extends BaseController
{
    private Staff $staff;
    private StaffService $staffService;

    public function __construct()
    {
        parent::__construct();
        $this->initialize();
    }

    private function initialize()
    {
        $this->staff = new Staff(pdo: $this->pdo);
        $this->staffService = new StaffService($this->pdo);
    }

    /**
     * Adds a new staff.
     */
    public function addStaff()
    {
        $requiredFields = ['surname', 'first_name','sex'];
        $data = $this->validateData(data: $this->post, requiredFields: $requiredFields);
        $data['password'] = $this->staffService->generatePassword($data['surname']);

        try {
            $staffId = $this->staff->insert($data);
            if ($staffId) {
                $success = $this->staffService->generateRegistrationNumber($staffId);
                if ($success) {
                    $this->response = [
                        'success' => true,
                        'message' => 'Staff added successfully.',
                        'staff_id' => $staffId
                    ];
                } else {
                    throw new Exception('Failed to generate staff number.');
                }
            } else {
                $this->response = [
                    'success' => false,
                    'message' => 'Failed to register staff.'
                ];
            }
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getStaff()
    {
        try {
            $results = $this->staff->select(
                columns: [
                'id',
                'picture_ref',
                'surname',
                'first_name',
                'middle',
                'staff_no'
                ]
            )->get();

            $staffDetails = array_map(
                fn($row) => [
                'id' => $row['id'],
                'profile_url' => $row['picture_ref'],
                'surname' => $row['surname'],
                'first_name' => $row['first_name'],
                'middle' => $row['middle'],
                'staff_no' => $row['staff_no'],
                ],
                $results
            );

            $this->response = ['success' => true, 'staff' => $staffDetails];
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
