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
    public function getAllStudents()
    {
        try {
            $results = $this->student
                ->select(columns: [
                    'id',
                    'picture_ref AS picture_url',
                    'surname',
                    'first_name',
                    'middle',
                    'registration_no',
                    'student_class AS class_id',
                    'student_level AS level_id'
                ])->get();

            $this->response = ['success' => true, 'students' => $results];
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getStudentById(array $vars)
    {
        echo print_r($vars);
    }

    public function getStudentsByClass(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['id']);

        try {
            $results = $this->student
                ->select(columns: ['id', "CONCAT(surname, ' ', first_name, ' ', middle) AS student_name"])
                ->where("student_class AS class_id", '=', $data['id'])
                ->get();

            $this->response = ['success' => true, 'students' => $results];
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getClassRegisteredStudents(array $vars)
    {
        $data = $this->validateData($vars, ['id', 'term', 'year']);
        try {
            $result  = $this->student
                ->rawQuery(
                    query: "SELECT students_record.id, 
                        concat(surname,' ', first_name,' ', middle) AS student_name, 
                        COUNT(rt.course) AS course_count FROM students_record 
                        LEFT JOIN result_table rt ON students_record.id = rt.reg_no 
                        AND rt.term = ? AND rt.year = ? WHERE students_record.student_class = ?
                        GROUP BY students_record.id, student_name",
                    bindings: [$data['term'], $data['year'], $data['id']]
                );
            $this->response = ['success' => true, 'registered_students' => $result];
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            $this->response['message'] = $e->getMessage();
        }

        ResponseHandler::sendJsonResponse($this->response);
    }

    public function deleteStudent($id) {}
}
