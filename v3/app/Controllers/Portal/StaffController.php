<?php

namespace V3\App\Controllers\Portal;

use V3\App\Models\Portal\Staff;
use V3\App\Utilities\Permission;

use V3\App\Utilities\DataExtractor;
use V3\App\Utilities\ResponseHandler;
use V3\App\Utilities\DatabaseConnector;
use V3\App\Services\Portal\AuthService;
use V3\App\Services\Portal\StaffService;
use V3\App\Models\Portal\SchoolSettings;
use V3\App\Models\Portal\RegistrationTracker;


/**
 * Class StaffController
 *
 * Handles staff-related operations.
 */

class StaffController
{
    private array $post;
    private Staff $staff;
    private SchoolSettings $schoolSettings;
    private RegistrationTracker $regTracker;
    private StaffService $staffService;
    private array $response = ['success' => false, 'message' => ''];

    public function __construct()
    {
        AuthService::verifyAPIKey();
        AuthService::verifyJWT();

        $this->initialize();
    }

    private function initialize()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->post = DataExtractor::extractPostData();
            $dbname = $this->post['_db'] ?? '';
        } else {
            $dbname = $_GET['_db'] ?? '';
        }

        if (!empty($dbname)) {
            $db = DatabaseConnector::connect(dbname: $dbname);
            $this->staff = new Staff(pdo: $db);
            $this->schoolSettings = new SchoolSettings($db);
            $this->regTracker = new RegistrationTracker($db);
        } else {
            $this->response['message'] = '_db is required.';

            ResponseHandler::sendJsonResponse($this->response);
        }

        // Instantiate the service with the necessary Models\Portal
        $this->staffService = new staffService(
            $this->staff,
            $this->schoolSettings,
            $this->regTracker
        );
    }


    /**
     * Adds a new staff.
     */
    public function addStaff()
    {
        try {
            $data = $this->staffService->validateAndGetData(post: $this->post);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            $this->response['message'] = $e->getMessage();
            ResponseHandler::sendJsonResponse($this->response);
        }

        try {
            $staffId = $this->staff->insertStaff($data);
            if ($staffId) {
                $success = $this->staffService->generateRegistrationNumber($staffId);
                if ($success) {
                    $this->response = [
                        'success' => true,
                        'message' => 'Staff added successfully.',
                        'staff_id' => $staffId
                    ];
                } else {
                    throw new \Exception('Failed to generate staff number.');
                }
            }
        } catch (\PDOException $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        } catch (\Exception $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getStaff()
    {
        try {
            $results = $this->staff->getStaff(columns: [
                'id',
                'picture_ref',
                'surname',
                'first_name',
                'middle',
                'staff_no'
            ]);

            if ($results) {
                $staffDetails = array_map(function ($row) {
                    return [
                        'id' => $row['id'],
                        'profile_url' => $row['picture_ref'],
                        'surname' => $row['surname'],
                        'first_name' => $row['first_name'],
                        'middle' => $row['middle'],
                        'staff_no' => $row['staff_no'],
                    ];
                }, $results);

                $this->response = ['success' => true, 'staff' => $staffDetails];
            }else{
                $this->response = ['success' => true, 'staff' => []];
            }
        } catch (\PDOException $e) {
            http_response_code(500);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getStaffById(array $params) {}

    public function updateStaff() {}

    public function deleteStaff($params) {}

   
}
