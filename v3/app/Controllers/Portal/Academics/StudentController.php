<?php

namespace V3\App\Controllers\Portal\Academics;

use Exception;
use V3\App\Controllers\BaseController;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Portal\Academics\StudentService;

class StudentController extends BaseController
{
    private StudentService $studentService;

    public function __construct()
    {
        parent::__construct();
        $this->studentService = new StudentService($this->pdo);
    }

    /**
     * Adds a new student.
     */
    public function addStudent()
    {
        $data = $this->validate(
            data: $this->post,
            rules: [
                'photo'            => 'nullable|array',
                'photo.file'           => 'sometimes|string',
                'photo.file_name'      => 'sometimes|string',
                'photo.old_file_name'  => 'sometimes|string',
                'surname'                => 'required|string|filled',
                'first_name'             => 'required|string|filled',
                'middle'                 => 'nullable|string',
                'gender'                 => 'required|string|in:male,female',
                'birth_date'             => 'nullable|date',
                'address'                => 'nullable|string',
                'city'                   => 'nullable|integer',
                'state'                  => 'nullable|integer',
                'country'                => 'nullable|integer',
                'email'                  => 'nullable|email',
                'religion'               => 'nullable|string',
                'guardian_name'          => 'nullable|string',
                'guardian_address'       => 'nullable|string',
                'guardian_email'         => 'nullable|email',
                'guardian_phone_no'      => 'nullable|string',
                'lga_origin'             => 'nullable|string',
                'state_origin'           => 'nullable|string',
                'nationality'            => 'nullable|string',
                'health_status'          => 'nullable|string',
                'status'                 => 'nullable|string',
                'past_record'            => 'nullable|string',
                'result'                 => 'nullable|string',
                'level_id'               => 'required|integer|filled',
                'class_id'               => 'required|integer|filled'
            ]
        );


        try {
            $newId = $this->studentService->insertStudentRecord($data);

            if ($newId) {
                $this->respond([
                    'success' => true,
                    'message' => 'Student added successfully.',
                ], HttpStatus::CREATED);
            }

            $this->respondError('Failed to add a new student', HttpStatus::BAD_REQUEST);

            return $this->respondError('Failed to add a new student');
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function updateStudentRecord(array $vars)
    {
        $data = $this->validate(
            data: array_merge($this->post, $vars),
            rules: [
                'id'                     => 'required|integer|filled',
                'photo'            => 'nullable|array',
                'photo.file'           => 'sometimes|string',
                'photo.file_name'      => 'sometimes|string',
                'photo.old_file_name'  => 'sometimes|string',
                'surname'                => 'required|string|filled',
                'first_name'             => 'required|string|filled',
                'middle'                 => 'nullable|string',
                'gender'                 => 'required|string|in:male,female',
                'birth_date'             => 'nullable|date',
                'address'                => 'nullable|string',
                'city'                   => 'nullable|integer',
                'state'                  => 'nullable|integer',
                'country'                => 'nullable|integer',
                'email'                  => 'nullable|email',
                'religion'               => 'nullable|string',
                'guardian_name'          => 'nullable|string',
                'guardian_address'       => 'nullable|string',
                'guardian_email'         => 'nullable|email',
                'guardian_phone_no'      => 'nullable|string',
                'lga_origin'             => 'nullable|string',
                'state_origin'           => 'nullable|string',
                'nationality'            => 'nullable|string',
                'health_status'          => 'nullable|string',
                'status'                 => 'nullable|string',
                'past_record'            => 'nullable|string',
                'result'                 => 'nullable|string',
                'level_id'               => 'required|integer|filled',
                'class_id'               => 'required|integer|filled'
            ]
        );

        try {
            $updated = $this->studentService->updateStudentRecord($data);

            if ($updated) {
                $this->respond([
                    'success' => true,
                    'message' => 'Student record updated successfully.'
                ]);
            }

            $this->respondError(
                'Failed to update student record',
                HttpStatus::BAD_REQUEST
            );

            return $this->respondError('Failed to update student record');
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    /**
     * Get students record.
     */
    public function getAllStudents()
    {
        try {
            $this->respond([
                'success' => true,
                'response' => $this->studentService->getAllStudents()
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getStudentsByClass(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'class_id' => 'required|integer|filled'
            ]
        );

        try {
            $this->respond([
                'success' => true,
                'students' => $this->studentService->getStudentsByClass($data['class_id'])
            ]);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
