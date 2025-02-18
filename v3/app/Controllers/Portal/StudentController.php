<?php

namespace V3\App\Controllers\Portal;


use V3\App\Utilities\Sanitizer;
use V3\App\Utilities\AuthHelper;
use V3\App\Utilities\Permission;
use V3\App\Models\Portal\Student;
use V3\App\Utilities\DataExtractor;
use V3\App\Utilities\ResponseHandler;
use V3\App\Utilities\DatabaseConnector;
use V3\App\Services\Portal\AuthService;
use V3\App\Models\Portal\SchoolSettings;
use V3\App\Services\Portal\StudentService;
use V3\App\Models\Portal\RegistrationTracker;

/**
 * Class StudentController
 *
 * Handles student-related operations.
 */

class StudentController
{
    private array $post;
    private Student $student;
    private SchoolSettings $schoolSettings;
    private RegistrationTracker $regTracker;
    private StudentService $studentService;
    private array $response = ['success' => false, 'message' => ''];


    public function __construct()
    {
        AuthService::verifyAPIKey();
        AuthService::verifyJWT();

        $this->init();
    }

    private function init()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->post = DataExtractor::extractPostData();
            $dbname = $this->post['_db'] ?? '';
        } else {
            $dbname = $_GET['_db'] ?? '';
        }

        if (!empty($dbname)) {
            $db = DatabaseConnector::connect(dbname: $dbname);
            $this->student = new Student(pdo: $db);
            $this->schoolSettings = new SchoolSettings(pdo: $db);
            $this->regTracker = new RegistrationTracker(pdo: $db);
        } else {
            $this->response['message'] = '_db is required.';

            ResponseHandler::sendJsonResponse(response: $this->response);
        }

        // Instantiate the service with the necessary Models\Portal
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
        try {
            $data = $this->studentService->validateAndGetData(post: $this->post);
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
            }
        } catch (\PDOException $e) {
            http_response_code(response_code: 500);
            $this->response['message'] = $e->getMessage();
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
            }else{
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
