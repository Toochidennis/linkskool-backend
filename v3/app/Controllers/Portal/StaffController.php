<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Controllers\BaseController;
use V3\App\Models\Portal\Staff;
use V3\App\Utilities\Permission;
use V3\App\Utilities\ResponseHandler;
use V3\App\Services\Portal\StaffService;
use V3\App\Utilities\HttpStatus;

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
        $data = $this->staffService->validateAndGetData(post: $this->post);

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
            }else{
                $this->response = [
                    'success' => false,
                    'message' => 'Failed to register staff.'
                ];
            }
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getStaff()
    {
        try {
            $results = $this->staff->select(columns: [
                'id',
                'picture_ref',
                'surname',
                'first_name',
                'middle',
                'staff_no'
            ])->get();

            $staffDetails = array_map(fn($row) => [
                'id' => $row['id'],
                'profile_url' => $row['picture_ref'],
                'surname' => $row['surname'],
                'first_name' => $row['first_name'],
                'middle' => $row['middle'],
                'staff_no' => $row['staff_no'],
            ], $results);

            $this->response = ['success' => true, 'staff' => $staffDetails];
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getStaffById(array $params) {}

    public function updateStaff() {}

    public function deleteStaff($params) {}
}
