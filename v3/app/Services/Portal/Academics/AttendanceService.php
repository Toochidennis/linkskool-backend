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

    public function addAttendance(array $data, bool $isCourse = false): array
    {
        $data['course_id'] = $isCourse ? ($data['course_id'] ?? 0) : 0;
        $data['attendance_date'] = date('Y-m-d', strtotime($data['attendance_date'])); // Normalize

        $check = $this->attendance
            ->where('class', '=', $data['class_id'])
            ->where('year', '=', $data['year'])
            ->where('term', '=', $data['term'])
            ->where('date', '=', $data['attendance_date']);

        if ($isCourse) {
            $check = $check->where('course', '=', $data['course_id']);
        }

        if ($check->exists()) {
            return [
                'success' => false,
                'message' => 'Attendance already exists for today. You can only update it.',
                'data' => null
            ];
        }

        $payload = [
            'year' => $data['year'],
            'term' => $data['term'],
            'staff_id' => $data['staff_id'],
            'count' => $data['attendance_count'],
            'class' => $data['class_id'],
            'course' => $data['course_id'],
            'date' => $data['attendance_date'],
            'register' => json_encode($data['students'], JSON_UNESCAPED_UNICODE),
        ];

        $newId = $this->attendance->insert($payload);

        return [
            'success' => true,
            'message' => 'Attendance added successfully.',
            'data' => $newId
        ];
    }

    public function updateAttendance(array $data): array
    {
        $payload = [
            'staff_id' => $data['staff_id'],
            'count' => $data['attendance_count'],
            'register' => json_encode($data['students']),
        ];

        $updated = $this->attendance
            ->where('id', '=', $data['id'])
            ->update($payload);

        return [
            'success' => $updated,
            'message' => $updated ? 'Attendance updated successfully.' : 'Attendance update failed.',
            'data' => $updated
        ];
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
        $query = $this->attendance->select($columns);

        // Apply filters
        foreach ($filters as $key => $value) {
            $query->where($key, '=', $value);
        }

        $result = $singleRecord ? $query->first() : $query->get();

        if ($singleRecord && $result) {
            $result['students'] = json_decode($result['students'], true);
            return [
                'success' => true,
                'message' => 'Attendance record retrieved.',
                'data' => $result
            ];
        }

        if (!$singleRecord && $result) {
            foreach ($result as $record) {
                if (isset($record['students'])) {
                    $record['students'] = json_decode($record->students, true);
                }
            }
            return [
                'success' => true,
                'message' => 'Attendance records retrieved.',
                'data' => $result
            ];
        }

        return [
            'success' => false,
            'message' => 'No attendance records found.',
            'data' => []
        ];
    }

    public function getAttendanceHistory(array $filters): array
    {
        $modFilters = [
            'class' => $filters['class_id'],
            'year' => $filters['year'],
            'term' => $filters['term']
        ];

        $attendanceResponse = $this->getAttendance(
            $modFilters,
            [
                'id',
                'count AS attendance_count',
                'date AS attendance_date',
                'course AS course_id',
            ]
        );

        if (!$attendanceResponse['success']) {
            return [
                'success' => true,
                'message' => 'No attendance records found.',
                'data' => []
            ];
        }

        $attendances = $attendanceResponse['data'];

        $uniqueIds = array_unique(array_filter(array_column($attendances, 'course_id')));
        $courseIds = array_values($uniqueIds);

        if (empty($courseIds)) {
            return [
                'success' => true,
                'message' => 'Attendance history retrieved successfully.',
                'data' => $attendances
            ];
        }

        $fetchedCourses = $this->course
            ->select(['id', 'course_name'])
            ->in('id', $courseIds)
            ->get();

        $courseMap = [];
        foreach ($fetchedCourses as $course) {
            $courseMap[$course['id']] = $course['course_name'];
        }

        foreach ($attendances as &$att) {
            $att['course_name'] = isset($att['course_id']) && isset($courseMap[$att['course_id']])
                ? $courseMap[$att['course_id']]
                : '';
        }

        return [
            'success' => true,
            'message' => 'Attendance history retrieved successfully.',
            'data' => $attendances
        ];
    }

    public function getAttendanceHistoryByRange(array $filters): array
    {
        $modFilters = [
            'year' => $filters['year'],
            'term' => $filters['term'],
        ];

        if (!$filters['isCourse']) {
            $modFilters['class'] = $filters['class_id'];
            $modFilters['course'] = $filters['course_id'];
        } else {
            $modFilters['class'] = $filters['class_id'];
        }

      return $this->getAttendance($modFilters);
    }
}
