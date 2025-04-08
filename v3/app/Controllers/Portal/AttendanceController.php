<?php

namespace V3\App\Controllers\Portal;

use V3\App\Controllers\BaseController;
use V3\App\Traits\ValidationTrait;
use V3\App\Utilities\ResponseHandler;
use V3\App\Services\Portal\AttendanceService;


class AttendanceController extends BaseController
{
    private AttendanceService $attendanceService;
    use ValidationTrait;

    public function __construct()
    {
        parent::__construct();
        $this->attendanceService = new AttendanceService($this->pdo);
    }

    public function addClassAttendance(array $vars)
    {
        $requiredFields = [
            'year',
            'term',
            'staff_id',
            'count',
            'class',
            'register',
            'date'
        ];

        $data = $this->validateData($this->post + ['class' => $vars['id']], $requiredFields);
        $this->response = $this->attendanceService->addAttendance($data, false);
        ResponseHandler::sendJsonResponse($this->response);
    }

    public function addCourseAttendance(array $vars)
    {
        $requiredFields = [
            'year',
            'term',
            'staff_id',
            'count',
            'class',
            'course',
            'register',
            'date'
        ];

        $data = $this->validateData($this->post + ['course' => $vars['id']], $requiredFields);
        $this->response = $this->attendanceService->addAttendance($data, true);
        ResponseHandler::sendJsonResponse($this->response);
    }

    public function updateClassAttendance(array $vars)
    {
        $requiredFields = [
            'id',
            'year',
            'term',
            'staff_id',
            'count',
            'class',
            'register',
            'date'
        ];
        $data = $this->validateData($this->post + ['id' => $vars['id']], $requiredFields);
        $this->response = $this->attendanceService->updateAttendance($data);
        ResponseHandler::sendJsonResponse($this->response);
    }

    public function updateCourseAttendance(array $vars)
    {
        $requiredFields = [
            'id',
            'year',
            'term',
            'staff_id',
            'count',
            'class',
            'register',
            'date'
        ];

        $data = $this->validateData($this->post + ['id' => $vars['id']], $requiredFields);
        $this->response = $this->attendanceService->updateAttendance($data);
        ResponseHandler::sendJsonResponse($this->response);
    }

    /**
     * Returns attendance data based on provided filters.
     *
     * Expected query parameters:
     * - date
     * - class
     * - course_id
     *
     * @param array $params
     */
    public function getClassAttendance(array $params)
    {
        $data = $this->validateData($params, ['id', 'date']);
        $this->response = $this->attendanceService
            ->getAttendance(['class' => $data['id'], 'date' => $data['date']]);
        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getCourseAttendance(array $params)
    {
        $data = $this->validateData(
            $params,
            ['id', 'class_id', 'date']
        );
        $this->response = $this->attendanceService->getAttendance([
            'class' => $data['id'],
            'course' => $data['course_id'],
            'date' => $data['date']
        ]);
        ResponseHandler::sendJsonResponse($this->response);
    }

    /**
     * Returns summary of  attendance data.
     *
     *  * Expected query parameters:
     * - class_id
     * - term
     * - year
     * 
     * @param array $params
     * @return void
     */
    public function getAllAttendance($params)
    {
        $data = $this->validateData(
            $params,
            ['class_id', 'term', 'year']
        );
        $this->response = $this->attendanceService
            ->getAttendance([
                'class' => $data['class_id'],
                'year' => $data['year'],
                'term' => $data['term']
            ]);
        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getAttendanceById(array $params)
    {
        $data = $this->validateData($params, ['id']);
        $this->response = $this->attendanceService->getAttendance(['id' => $data['id']]);
        ResponseHandler::sendJsonResponse($this->response);
    }
}
