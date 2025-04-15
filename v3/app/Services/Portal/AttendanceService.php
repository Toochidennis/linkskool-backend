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
        $data['course'] = $isCourse ? $data['course'] : 0;
        $data['date'] = date('Y-m-d', strtotime($data['date'])); // Normalize

        $check = $this->attendance
            ->where('class', '=', $data['class'])
            ->where('year', '=', $data['year'])
            ->where('term', '=', $data['term'])
            ->where('date', '=', $data['date']);

        if ($isCourse) {
            $check = $check->where('course', '=', $data['course']);
        }

        if ($check->exists()) {
            return [
                'success' => false,
                'message' => 'Attendance already exists for today. You can only update it.'
            ];
        }

        if (!isset($data['register']) || !is_array($data['register'])) {
            return ['success' => false, 'message' => 'Invalid register format'];
        }

        $data['register'] = json_encode($data['register'], JSON_UNESCAPED_UNICODE);

        try {
            $inserted = $this->attendance->insert($data);

            return $inserted
                ? ['success' => true, 'message' => 'Attendance added successfully.']
                : ['success' => false, 'message' => 'Failed to add attendance'];
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }


    public function updateAttendance(array $data, int $id)
    {
        if (!isset($data['register']) || !is_array($data['register'])) {
            return ['success' => false, 'message' => 'Invalid register format'];
        }

        $data['register'] = json_encode($data['register'], JSON_UNESCAPED_UNICODE);

        try {
            $isUpdated = $this->attendance
                ->where('id', '=', $id)
                ->update($data);

            return $isUpdated ?
                ['success' => true, 'message' => 'Attendance updated successfully.'] :
                ['success' => false, 'message' => 'Failed to update attendance'];
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getAttendance(
        array $filters,
        array $columns = ['id', 'count', 'date', 'register'],
        bool $singleRecord = false
    ) {
        try {
            $query = $this->attendance->select($columns);

            // Apply filters
            foreach ($filters as $key => $value) {
                $query->where($key, '=', $value);
            }

            $result = $singleRecord ? $query->first() : $query->get();

            if (in_array('register', $columns)) {
                if ($singleRecord && $result) {
                    $result['register'] = json_decode($result['register'], true);
                } elseif (!$singleRecord && $result) {
                    foreach ($result as $record) {
                        if (isset($record['register']))
                            $record->register = json_decode($record->register, true);
                    }
                }
            }

            return ['success' => true, 'attendance_records' => $result];
        } catch (Exception $e) {
            http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
