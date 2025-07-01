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

        return $this->attendance->insert($data);
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
        array $columns = ['id', 'count', 'date', 'register'],
        bool $singleRecord = false
    ) {
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
                    if (isset($record['register'])) {
                        $record->register = json_decode($record->register, true);
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

        $attendances = $this->getAttendance($modFilters, ['id', 'count', 'date', 'course']);

        if (empty($attendances)) {
            return [];
        }

        $uniqueIds = array_unique(array_filter(array_column($attendances, 'course')));
        $courseIds = array_values($uniqueIds);

        $fetchedCourses = $this->course
            ->select(['id', 'course_name'])
            ->in('id', $courseIds)
            ->get();

        $courseMap = [];
        foreach ($fetchedCourses as $course) {
            $courseMap[$course['id']] = $course['course_name'];
        }

        foreach ($attendances as &$att) {
            $att['course_name'] = isset($att['course']) && isset($courseMap[$att['course']])
                ? $courseMap[$att['course']]
                : '';
        }
        return $attendances;
    }
}
