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

    public function addAttendance(array $data, bool $isCourse = false)
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
                false,
                'Attendance already exists for today. You can only update it.',
                0
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

        $newId =  $this->attendance->insert($payload);

        return [true, 'Attendance added successfully.', $newId];
    }

    public function updateAttendance(array $data, int $id)
    {
        if (!isset($data['register']) || !is_array($data['register'])) {
            return ['success' => false, 'message' => 'Invalid register format'];
        }

        $data['register'] = json_encode($data['register'], JSON_UNESCAPED_UNICODE);

        return $this->attendance
            ->where('id', '=', $id)
            ->update($data);
    }

    public function getAttendance(
        array $filters,
        array $columns = ['id', 'count AS attendance_count', 'date AS attendance_date', 'register AS students'],
        bool $singleRecord = false
    ) {
        $query = $this->attendance->select($columns);

        // Apply filters
        foreach ($filters as $key => $value) {
            $query->where($key, '=', $value);
        }

        $result = $singleRecord ? $query->first() : $query->get();

        if (in_array('students', $columns)) {
            if ($singleRecord && $result) {
                $result['students'] = json_decode($result['students'], true);
            } elseif (!$singleRecord && $result) {
                foreach ($result as $record) {
                    if (isset($record['students'])) {
                        $record['students'] = json_decode($record->students, true);
                    }
                }
            }
        }

        return $result;
    }

    public function getAttendanceHistory(array $filters)
    {
        $modFilters = [
            'class' => $filters['class_id'],
            'year' => $filters['year'],
            'term' => $filters['term']
        ];

        $attendances = $this->getAttendance(
            $modFilters,
            [
                'id',
                'count AS attendance_count',
                'date AS attendance_date',
                'course AS course_id',
            ]
        );

        if (empty($attendances)) {
            return [];
        }

        $uniqueIds = array_unique(array_filter(array_column($attendances, 'course_id')));
        $courseIds = array_values($uniqueIds);

        if (empty($courseIds)) {
            return $attendances; // No courses to fetch
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
        return $attendances;
    }
}
