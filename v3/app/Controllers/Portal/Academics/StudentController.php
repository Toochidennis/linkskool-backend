<?php

namespace V3\App\Controllers\Portal\Academics;

use V3\App\Controllers\BaseController;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\Academics\StudentService;

#[Group('/portal')]
class StudentController extends BaseController
{
    private StudentService $studentService;

    public function __construct()
    {
        parent::__construct();
        $this->studentService = new StudentService($this->pdo);
    }

    #[Route('/students', 'POST', ['auth', 'role:admin'])]
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

        $newId = $this->studentService->insertStudentRecord($data);

        if ($newId) {
            $this->respond([
                'success' => true,
                'message' => 'Student added successfully.',
            ], HttpStatus::CREATED);
        }

        $this->respondError('Failed to add a new student', HttpStatus::BAD_REQUEST);

        return $this->respondError('Failed to add a new student');
    }

    #[Route('/students/{id:\d+}', 'PUT', ['auth', 'role:admin'])]
    public function updateStudentRecord(array $vars)
    {
        $data = $this->validate(
            data: array_merge($this->post, $vars),
            rules: [
                'id' => 'required|integer|filled',
                'photo' => 'nullable|array',
                'photo.file' => 'sometimes|string',
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
    }

    #[Route('/students', 'GET', ['auth', 'role:admin'])]
    public function getStudentsByLevel(array $vars)
    {
        $filteredData = $this->validate(
            data: $vars,
            rules: [
                'level_id' => 'nullable|integer|required_without:class_id',
                'class_id' => 'nullable|integer|required_without:level_id',
                'page' => 'nullable|integer',
                'limit' => 'nullable|integer'
            ]
        );

        $this->respond([
            'success' => true,
            'response' => $this->studentService->fetchStudentsByLevel($filteredData)
        ]);
    }

    #[Route(
        '/students/metrics',
        'GET',
        ['auth', 'role:admin']
    )]
    public function getStudentsMetrics()
    {
        $this->respond([
            'success' => true,
            'response' => $this->studentService->fetchStudentsMetrics()
        ]);
    }

    #[Route(
        '/classes/{class_id:\d+}/students',
        'GET',
        ['auth', 'role:admin', 'role:staff']
    )]
    public function getStudentsByClass(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'class_id' => 'required|integer|filled'
            ]
        );

        $this->respond([
            'success' => true,
            'students' => $this->studentService->getStudentsByClass($data['class_id'])
        ]);
    }

    #[Route('/students/{id:\d+}', 'DELETE', ['auth', 'role:admin'])]
    public function deleteStudent(array $vars)
    {
        $data = $this->validate(
            data: $vars,
            rules: [
                'id' => 'required|integer|filled'
            ]
        );

        $deleted = $this->studentService->deleteStudent($data['id']);

        if ($deleted) {
            return $this->respond([
                'success' => true,
                'message' => 'Student deleted successfully.'
            ], HttpStatus::OK);
        }

        return $this->respondError(
            'Failed to delete student',
            HttpStatus::BAD_REQUEST
        );
    }
}
