<?php

namespace  V3\App\Controllers\Portal\Academics;

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
        $data = $this->validate(
            data: $this->post,
            rules: [
                'photo.file'           => 'sometimes|string',
                'photo.file_name'      => 'sometimes|string',
                'photo.old_file_name'  => 'sometimes|string',
                'surname'              => 'required|string|filled',
                'first_name'             => 'required|string|filled',
                'middle_name'            => 'nullable|string',
                'gender'                 => 'required|string|in:male,female',
                'birth_date'             => 'nullable|date',
                'address'                => 'nullable|string',
                'city'                   => 'nullable|string',
                'state'                  => 'nullable|integer',
                'country'                => 'nullable|string',
                'phone_number'           => 'nullable|string',
                'email_address'          => 'nullable|email',
                'religion'               => 'nullable|string',
                'marital_status'         => 'nullable|string|in:single,married,divorced,widowed',
                'lga_origin'             => 'nullable|string',
                'state_origin'           => 'nullable|string',
                'nationality'            => 'nullable|string',
                'home_town'              => 'nullable|string',
                'health_status'          => 'nullable|string',
                'past_record'            => 'nullable|string',
                'past_record_extra'      => 'nullable|string',
                'personal_record'        => 'nullable|string',
                'employment_history'     => 'nullable|string',
                'referees'               => 'nullable|string',
                'extra_note'             => 'nullable|string',
                'next_of_kin_name'       => 'nullable|string',
                'next_of_kin_address'    => 'nullable|string',
                'next_of_kin_email'      => 'nullable|email',
                'next_of_kin_phone'      => 'nullable|string',
                'employment_date'        => 'nullable|date',
                'employment_status'      => 'nullable|string',
                'health_appraisal'       => 'nullable|string',
                'general_appraisal'      => 'nullable|string',
                'grade'               => 'nullable|integer',
                'department'           => 'nullable|integer',
                'section'             => 'nullable|integer',
                'designation'         => 'nullable|integer',
                'access_level'         => 'required|string|in:staff,admin',
            ]
        );

        try {
            $newId = $this->staffService->insertStaffRecord($data);

            if ($newId > 0) {
                $this->respond([
                    'success' => true,
                    'message' => 'Staff added successfully.'
                ], HttpStatus::CREATED);
            }

            return $this->respondError(
                'Failed to add a new staff',
                HttpStatus::BAD_REQUEST
            );
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function updateStaff(array $vars)
    {
        $data = $this->validate(
            data: array_merge($this->post, $vars),
            rules: [
                'id'                     => 'required|integer|filled',
                'photo'                  => 'nullable|array',
                'photo.file'           => 'sometimes|string',
                'photo.file_name'      => 'sometimes|string',
                'photo.old_file_name'  => 'sometimes|string',
                'surname'              => 'required|string|filled',
                'first_name'             => 'required|string|filled',
                'middle_name'            => 'nullable|string',
                'gender'                 => 'required|string|in:male,female',
                'birth_date'             => 'nullable|date',
                'address'                => 'nullable|string',
                'city'                   => 'nullable|string',
                'state'                  => 'nullable|string',
                'country'                => 'nullable|string',
                'phone_number'           => 'nullable|string',
                'email_address'          => 'nullable|email',
                'religion'               => 'nullable|string',
                'marital_status'         => 'nullable|string|in:single,married,divorced,widowed',
                'lga_origin'             => 'nullable|string',
                'state_origin'           => 'nullable|string',
                'nationality'            => 'nullable|string',
                'home_town'              => 'nullable|string',
                'health_status'          => 'nullable|string',
                'past_record'            => 'nullable|string',
                'past_record_extra'      => 'nullable|string',
                'personal_record'        => 'nullable|string',
                'employment_history'     => 'nullable|string',
                'referees'               => 'nullable|string',
                'extra_note'             => 'nullable|string',
                'next_of_kin_name'       => 'nullable|string',
                'next_of_kin_address'    => 'nullable|string',
                'next_of_kin_email'      => 'nullable|email',
                'next_of_kin_phone'      => 'nullable|string',
                'employment_date'        => 'nullable|date',
                'employment_status'      => 'nullable|string',
                'health_appraisal'       => 'nullable|string',
                'general_appraisal'      => 'nullable|string',
                'grade'                  => 'nullable|integer',
                'department'             => 'nullable|string',
                'section'                => 'nullable|integer',
                'designation'            => 'nullable|integer',
                'access_level'          => 'required|string|in:staff,admin',
            ]
        );

        try {
            $updated = $this->staffService->updateStaffRecord($data);

            if ($updated) {
                $this->respond([
                    'success' => true,
                    'message' => 'Staff record updated successfully.'
                ], HttpStatus::OK);
            }

            return $this->respondError(
                'Failed to update staff record',
                HttpStatus::BAD_REQUEST
            );
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

    public function deleteStaff(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'id' => 'required|integer|filled'
            ]
        );

        try {
            $deleted = $this->staffService->deleteStaff($data['id']);

            if ($deleted) {
                $this->respond([
                    'success' => true,
                    'message' => 'Staff record deleted successfully.'
                ], HttpStatus::OK);
            }

            return $this->respondError(
                'Failed to delete staff record',
                HttpStatus::BAD_REQUEST
            );
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
