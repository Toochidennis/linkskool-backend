<?php

namespace V3\App\Services\Portal\Academics;

use V3\App\Models\Portal\Academics\Attendance;
use V3\App\Models\Portal\Academics\Course;

class AttendanceService
{
    private Attendance $attendance;
    private Course $course;

    public function __construct($pdo)
    {
        $this->attendance = new Attendance($pdo);
        $this->course = new Course($pdo);
    }

    public function upsertAttendance(array $data): array
    {
        if ($data['type'] === 'class') {
            $data['course_id'] = 0;
        }

        $check = $this->attendance
            ->where('class', '=', $data['class_id'])
            ->where('year', '=', $data['year'])
            ->where('term', '=', $data['term'])
            ->where('date', '=', $data['attendance_date']);

        if ($data['type'] === 'course') {
            $check->where('course', '=', $data['course_id']);
        }

        $exists = $check->first();

        $payload = [
            'year'      => $data['year'],
            'term'      => $data['term'],
            'staff_id'  => $data['staff_id'],
            'count'     => $data['attendance_count'],
            'class'     => $data['class_id'],
            'course'    => $data['course_id'],
            'date'      => $data['attendance_date'],
            'register'  => json_encode($data['students'], JSON_UNESCAPED_UNICODE),
        ];

        if ($exists) {
            $updated = $this->attendance
                ->where('id', '=', $exists['id'])
                ->update($payload);
            return $this->buildResponse($updated, 'updated', $exists['id']);
        }

        $newId = $this->attendance->insert($payload);
        return $this->buildResponse($newId, 'added', $newId);
    }

    public function getAttendance(
        array $filters,
        array $columns = [
            'id',
            'count AS attendance_count',
            'date AS attendance_date',
            'register AS students'
        ],
        bool $singleRecord = false
    ): array {
        $query = $this->attendance
            ->select($columns)
            ->orderBy('date', 'DESC');

        foreach ($filters as $key => $value) {
            $query->where($key, '=', $value);
        }

        $records = $singleRecord ? $query->first() : $query->get();

        if (!$records) {
            return ['success' => false, 'message' => 'No attendance records found.', 'data' => []];
        }

        // Decode students field
        if ($singleRecord) {
            $records['students'] = json_decode($records['students'] ?? '[]', true);
        } else {
            foreach ($records as &$record) {
                if (isset($record['students'])) {
                    $record['students'] = json_decode($record['students'], true);
                }
            }
        }

        return [
            'success' => true,
            'message' => $singleRecord
                ? 'Attendance record retrieved.'
                : 'Attendance records retrieved.',
            'data' => $records
        ];
    }

    public function getAttendanceHistory(array $filters): array
    {
        $attendanceResponse = $this->getAttendance([
            'class' => $filters['class_id'],
            'year'  => $filters['year'],
            'term'  => $filters['term']
        ], [
            'id',
            'count AS attendance_count',
            'date AS attendance_date',
            'course AS course_id'
        ]);

        if (!$attendanceResponse['success']) {
            return ['success' => true, 'message' => 'No attendance records found.', 'data' => []];
        }

        $attendances = $attendanceResponse['data'];
        $courseIds = array_values(
            array_unique(
                array_filter(
                    array_column($attendances, 'course_id')
                )
            )
        );

        if (empty($courseIds)) {
            return ['success' => true, 'message' => 'Attendance history retrieved.', 'data' => $attendances];
        }

        $courses = $this->course
            ->select(['id', 'course_name'])
            ->in('id', $courseIds)
            ->get();

        $courseMap = array_column($courses, 'course_name', 'id');

        foreach ($attendances as &$record) {
            $record['course_name'] = $courseMap[$record['course_id']] ?? '';
        }

        return ['success' => true, 'message' => 'Attendance history retrieved.', 'data' => $attendances];
    }

    private function buildResponse(bool|int $status, string $action, int|string|null $id): array
    {
        $success = (bool)$status;
        return [
            'success' => $success,
            'message' => $success
                ? "Attendance {$action} successfully."
                : "Attendance {$action} failed.",
            'data' => $id
        ];
    }
}
