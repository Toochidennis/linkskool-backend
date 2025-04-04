<?php

namespace V3\App\Services\Portal;

use V3\App\Models\Portal\Attendance;
use V3\App\Utilities\HttpStatus;
use Exception;

class AttendanceService
{
    private Attendance $attendance;

    public function __construct($pdo)
    {
        $this->attendance = new Attendance($pdo);
    }

    public function addAttendance(array $data, bool $isCourse = false)
    {
        if (!$isCourse) {
            $data['course'] = "0"; // Default for class attendance
        }

        try {
            $attendanceId = $this->attendance->insert($data);

            return $attendanceId ?
                ['success' => true, 'message' => 'Attendance added successfully.'] :
                ['success' => false, 'message' => 'Failed to add attendance'];
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateAttendance(array $data)
    {
        try {
            $isUpdated = $this->attendance
                ->where('id', '=', $data['id'])
                ->update($data);

            return $isUpdated ?
                ['success' => true, 'message' => 'Attendance updated successfully.'] :
                ['success' => false, 'message' => 'Failed to update attendance'];
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getAttendance(array $filters)
    {
        try {
            $query = $this->attendance
                ->select(['id', 'count', 'date', 'register']);

            foreach ($filters as $key => $value) {
                $query = $query->where($key, '=', $value);
            }

            $result = $query->get();
            return ['success' => true, 'attendance_records' => $result];
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
