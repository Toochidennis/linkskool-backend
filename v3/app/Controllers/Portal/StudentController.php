<?php

namespace V3\App\Controllers\Portal;

use Exception;
use V3\App\Utilities\HttpStatus;
use V3\App\Utilities\Permission;
use V3\App\Models\Portal\Student;
use V3\App\Traits\ValidationTrait;
use V3\App\Utilities\ResponseHandler;
use V3\App\Controllers\BaseController;
use V3\App\Models\Portal\SchoolSettings;
use V3\App\Services\Portal\StudentService;
use V3\App\Models\Portal\RegistrationTracker;

/**
 * Class StudentController
 *
 * Handles student-related operations.
 */

class StudentController
extends BaseController
{
    use ValidationTrait;
    private Student $student;
    private SchoolSettings $schoolSettings;
    private RegistrationTracker $regTracker;
    private StudentService $studentService;


    public function __construct()
    {
        parent::__construct();
        $this->initialize();
    }

    /**
     * Initializes the database connection and service instances.
     *
     * Expects the request to contain a valid '_db' parameter.
     *
     * @param string $dbname The database name extracted from the request.
     * @return void
     */
    private function initialize()
    {
        $this->student = new Student(pdo: $this->pdo);
        $this->schoolSettings = new SchoolSettings(pdo: $this->pdo);
        $this->regTracker = new RegistrationTracker(pdo: $this->pdo);
        $this->studentService = new StudentService($this->pdo);
    }

    /**
     * Adds a new student.
     */
    public function addStudent()
    {
        $requiredFields = [
            'surname',
            'first_name',
            'sex',
            'student_class',
            'student_level'
        ];
        $data = $this->validateData(data: $this->post, requiredFields: $requiredFields);
        $data['password'] = $this->studentService->generatePassword($data['surname']);

        try {
            $studentId = $this->student->insert($data);
            if ($studentId) {
                $success = $this->studentService->generateRegistrationNumber(studentId: $studentId);

                $this->response = $success ? [
                    'success' => true,
                    'message' => 'Student added successfully.',
                    'student_id' => $studentId
                ] :
                    [
                        'success' => false,
                        'message' => 'Failed to generate registration number.'
                    ];
            } else {
                $this->response = [
                    'success' => false,
                    'message' => 'Failed to add student'
                ];
            }
        } catch (Exception $e) {
            http_response_code(response_code: HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse(response: $this->response);
    }

    /**
     * Get students record.
     */
    public function getStudents()
    {
        try {
            $results = $this->student
                ->select(columns: [
                    'id',
                    'picture_ref',
                    'surname',
                    'first_name',
                    'middle',
                    'registration_no',
                    'student_class',
                    'student_level'
                ])->get();

            $studentDetails = array_map(fn($row) => [
                'id' => $row['id'],
                'picture_url' => $row['picture_ref'],
                'surname' => $row['surname'],
                'first_name' => $row['first_name'],
                'middle' => $row['middle'],
                'registration_no' => $row['registration_no'],
                'student_class' => $row['student_class'],
                'student_level' => $row['student_level']
            ], $results);

            $this->response = ['success' => true, 'students' => $studentDetails];
        } catch (\PDOException $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getStudentById(array $vars)
    {
        echo print_r($vars);
    }

    public function deleteStudent($id) {}
}
