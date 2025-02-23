<?php

namespace V3\App\Controllers\Portal;

use V3\App\Utilities\Permission;
use V3\App\Models\Portal\Student;
use V3\App\Traits\ValidationTrait;
use V3\App\Utilities\ResponseHandler;
use V3\App\Controllers\BaseController;
use V3\App\Utilities\DatabaseConnector;
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
    private Student $student;
    private SchoolSettings $schoolSettings;
    private RegistrationTracker $regTracker;
    private StudentService $studentService;

    use ValidationTrait;


    public function __construct()
    {
        parent::__construct();
        $this->initializeDatabaseAndServices();
    }

    /**
     * Initializes the database connection and service instances.
     *
     * Expects the request to contain a valid '_db' parameter.
     *
     * @param string $dbname The database name extracted from the request.
     * @return void
     */
    private function initializeDatabaseAndServices()
    {
        $this->student = new Student(pdo: $this->pdo);
        $this->schoolSettings = new SchoolSettings(pdo: $this->pdo);
        $this->regTracker = new RegistrationTracker(pdo: $this->pdo);
        $this->studentService = new StudentService(
            student: $this->student,
            schoolSettings: $this->schoolSettings,
            regTracker: $this->regTracker
        );
    }

    /**
     * Adds a new student.
     */
    public function addStudent()
    {
        // Define an array for required fields with custom error messages
        $requiredFields = [
            'surname',
            'first_name',
            'sex',
            'student_class',
            'student_level'
        ];

        try {
            $data = $this->validateData(data: $this->post, requiredFields: $requiredFields);
        } catch (\InvalidArgumentException $e) {
            http_response_code(response_code: 400);
            $this->response['message'] = $e->getMessage();
            ResponseHandler::sendJsonResponse(response: $this->response);
        }

        try {
            $studentId = $this->student->insertStudent($data);
            if ($studentId) {
                $success = $this->studentService->generateRegistrationNumber(studentId: $studentId);
                if ($success) {
                    $this->response = [
                        'success' => true,
                        'message' => 'Student added successfully.',
                        'student_id' => $studentId
                    ];
                } else {
                    throw new \Exception('Failed to generate registration number.');
                }
            }else{
                $this->response = [
                    'message' => 'Failed to add student',
                    'student_id' => $studentId
                ];
            }
        } catch (\Exception $e) {
            http_response_code(response_code: 500);
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
            $results = $this->student->getStudents(columns: [
                'id',
                'picture_ref',
                'surname',
                'first_name',
                'middle',
                'registration_no',
                'student_class',
                'student_level'
            ]);

            if ($results) {
                $studentDetails = array_map(function ($row) {
                    return [
                        'id' => $row['id'],
                        'picture_url' => $row['picture_ref'],
                        'surname' => $row['surname'],
                        'first_name' => $row['first_name'],
                        'middle' => $row['middle'],
                        'registration_no' => $row['registration_no'],
                        'student_class' => $row['student_class'],
                        'student_level' => $row['student_level']
                    ];
                }, $results);

                $this->response = ['success' => true, 'students' => $studentDetails];
            } else {
                $this->response = ['success' => true, 'students' => []];
            }
        } catch (\PDOException $e) {
            http_response_code(500);
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
