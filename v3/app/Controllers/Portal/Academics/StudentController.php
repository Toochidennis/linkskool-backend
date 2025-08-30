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
        $data = $this->validateData(
            data: $this->post,
            requiredFields: [
                'surname',
                'first_name',
                'gender',
                'class_id',
                'level_id'
            ]
        );

        try {
            $newId = $this->studentService->insertStudentRecord($data);

            if ($newId) {
                $this->respond([
                    'success' => true,
                    'message' => 'Student added successfully.'
                ], HttpStatus::CREATED);
            }

            return $this->respondError('Failed to add a new student');
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
        $data = $this->validateData(data: $vars, requiredFields: ['class_id']);

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
