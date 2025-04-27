<?php

namespace V3\App\Controllers\Portal;

use V3\App\Traits\ValidationTrait;
use V3\App\Utilities\ResponseHandler;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\AttendanceService;

class AttendanceController extends BaseController
{
    use ValidationTrait;

    private AttendanceService $attendanceService;

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

        $data = $this->validateData(data: $this->post + ['course' => $vars['id']], requiredFields: $requiredFields);
        $this->response = $this->attendanceService->addAttendance(data: $data, isCourse: true);
        ResponseHandler::sendJsonResponse(response: $this->response);
    }

    public function updateAttendance(array $vars)
    {
        $requiredFields = ['staff_id', 'count', 'register'];
        $data = $this->validateData(data: $this->post, requiredFields: $requiredFields);
        $id = (int) $vars['id'];
        $this->response = $this->attendanceService->updateAttendance(data: $data, id: $id);
        ResponseHandler::sendJsonResponse(response: $this->response);
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
    public function getClassAttendance(array $vars)
    {
        $data = $this->validateData(data: $vars, requiredFields: ['id', 'date']);
        $this->response = $this->attendanceService
            ->getAttendance(
                filters: [
                    'class' => $data['id'],
                    'date' => $data['date']
                ],
                singleRecord: true
            );
        ResponseHandler::sendJsonResponse(response: $this->response);
    }

    public function getCourseAttendance(array $vars)
    {
        $data = $this->validateData(
            $vars,
            ['id', 'class_id', 'date']
        );
        $this->response = $this->attendanceService
            ->getAttendance(
                [
                    'class' => $data['class_id'],
                    'course' => $data['id'],
                    'date' => $data['date']
                ],
                singleRecord: true
            );
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
     * @param  array $params
     * @return void
     */
    public function getAllAttendance($params)
    {
        $data = $this->validateData(
            $params,
            ['class_id', 'term', 'year']
        );
        $this->response = $this->attendanceService
            ->getAttendance(
                columns: ['id', 'count', 'date'],
                filters: [
                    'class' => $data['class_id'],
                    'year' => $data['year'],
                    'term' => $data['term']
                ]
            );
        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getAttendanceById(array $params)
    {
        $data = $this->validateData($params, ['id']);
        $this->response = $this->attendanceService->getAttendance(['id' => $data['id']], singleRecord:true);
        ResponseHandler::sendJsonResponse($this->response);
    }
}
