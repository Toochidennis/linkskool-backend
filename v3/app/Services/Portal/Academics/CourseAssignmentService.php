<?php

namespace V3\App\Services\Portal\Academics;

use PDO;
use V3\App\Models\Portal\Academics\CourseAssignment;

class CourseAssignmentService
{
    private CourseAssignment $courseAssignment;

    public function __construct(PDO $pdo)
    {
        $this->courseAssignment = new CourseAssignment($pdo);
    }

    public function assignCourses(array $data): bool
    {
        $this->syncAssignments($data);

        foreach ($data['courses'] as $course) {
            $payload = [
                'ref_no' => $data['staff_id'],
                'course' => $course['course_id'],
                'class'  => $course['class_id'],
                'year'   => $data['year'],
                'term'   => $data['term']
            ];

            $existPayload = [
                ['ref_no', '=', $data['staff_id']],
                ['course', '=', $course['course_id']],
                ['class',  '=', $course['class_id']],
                ['year',   '=', $data['year']],
                ['term',   '=', $data['term']]
            ];

            $exists = $this->courseAssignment
                ->whereGroup($existPayload)
                ->first();

            if (!$exists) {
                $inserted = $this->courseAssignment->insert($payload);
                if (!$inserted) {
                    return false;
                }
            }
        }

        return true;
    }

    private function syncAssignments(array $data): void
    {
        $pairs = array_map(fn($c) => [
            'course' => $c['course_id'],
            'class'  => $c['class_id']
        ], $data['courses']);

        $this->courseAssignment
            ->where('ref_no', '=', $data['staff_id'])
            ->where('year', '=', $data['year'])
            ->where('term', '=', $data['term'])
            ->whereNotInComposite(['course', 'class'], $pairs)
            ->delete();
    }

    public function getAssignedCourses(array $filters)
    {
        return $this->courseAssignment
            ->select(
                columns: [
                    'course_table.id as course_id',
                    'course_table.course_name',
                    'course_table.course_code',
                    'class_table.id as class_id',
                    'class_table.class_name',
                    'class_table.level as level_id',
                    'level_table.level_name'
                ]
            )
            ->join('course_table', 'staff_course_table.course = course_table.id')
            ->join('class_table', 'staff_course_table.class = class_table.id')
            ->join('level_table', 'class_table.level = level_table.id')
            ->where('staff_course_table.ref_no', '=', $filters['staff_id'])
            ->where('staff_course_table.year', '=', $filters['year'])
            ->where('staff_course_table.term', '=', $filters['term'])
            ->orderBy('course_table.course_name', 'ASC')
            ->get();
    }
}
