<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\LessonAttendance;

class LessonAttendanceService
{
    private LessonAttendance $lessonAttendance;

    public function __construct(\PDO $pdo)
    {
        $this->lessonAttendance = new LessonAttendance($pdo);
    }

    public function takeLessonAttendance(array $data): bool|int
    {
        $attendanceDate = date('Y-m-d');

        $conditions = [
            ['profile_id', '=', $data['profile_id']],
            ['cohort_id', '=', $data['cohort_id']],
            ['lesson_id', '=', $data['lesson_id']],
            ['attendance_date', '=', $attendanceDate],
        ];

        $query = $this->lessonAttendance->whereGroup($conditions);

        $payload = [
            'course_id' => $data['course_id'],
            'marked_by' => $data['marked_by'] ?? null,
            'remark' => $data['remark'] ?? null,
        ];

        if ($query->exists()) {
            return $this->lessonAttendance
                ->whereGroup($conditions)
                ->update($payload);
        }

        return $this->lessonAttendance->insert([
            ...$payload,
            'profile_id' => $data['profile_id'],
            'cohort_id' => $data['cohort_id'],
            'lesson_id' => $data['lesson_id'],
            'attendance_date' => $attendanceDate,
        ]);
    }

    public function getLessonAttendance(
        int $lessonId,
        ?string $attendanceDate = null,
        int $page = 1,
        int $limit = 25
    ): array {
        $page = max(1, $page);
        $limit = max(1, min(50, $limit));
        $offset = ($page - 1) * $limit;

        $countSql = '
            SELECT COUNT(*) AS total
            FROM cohort_lesson_attendance a
            WHERE a.lesson_id = :lesson_id
        ';

        $countParams = ['lesson_id' => $lessonId];

        if ($attendanceDate !== null) {
            $countSql .= ' AND a.attendance_date = :attendance_date';
            $countParams['attendance_date'] = $attendanceDate;
        }

        $countRows = $this->lessonAttendance->rawQuery($countSql, $countParams);
        $total = (int) ($countRows[0]['total'] ?? 0);

        $sql = '
            SELECT
                a.id,
                a.profile_id,
                a.lesson_id,
                a.cohort_id,
                a.course_id,
                a.program_id,
                a.attendance_date,
                a.marked_by,
                a.remark,
                a.created_at,
                a.updated_at,
                p.first_name,
                p.last_name
            FROM cohort_lesson_attendance a
            LEFT JOIN program_profiles p
                ON p.id = a.profile_id
            WHERE a.lesson_id = :lesson_id
        ';

        $params = ['lesson_id' => $lessonId];

        if ($attendanceDate !== null) {
            $sql .= ' AND a.attendance_date = :attendance_date';
            $params['attendance_date'] = $attendanceDate;
        }

        $sql .= '
            ORDER BY a.id DESC
            LIMIT :limit OFFSET :offset
        ';

        $rows = $this->lessonAttendance->rawQuery($sql, [
            ...$params,
            'limit' => $limit,
            'offset' => $offset,
        ]);

        $data = array_map(function (array $row): array {
            $fullName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));

            return [
                'id' => (int) $row['id'],
                'profile' => [
                    'id' => (int) $row['profile_id'],
                    'first_name' => $row['first_name'] ?? null,
                    'last_name' => $row['last_name'] ?? null,
                    'full_name' => $fullName !== '' ? $fullName : null,
                ],
                'lesson_id' => (int) $row['lesson_id'],
                'cohort_id' => (int) $row['cohort_id'],
                'course_id' => (int) $row['course_id'],
                'program_id' => (int) $row['program_id'],
                'attendance_date' => $row['attendance_date'],
                'marked_by' => $row['marked_by'] !== null ? (int) $row['marked_by'] : null,
                'remark' => $row['remark'] ?? null,
                'created_at' => $row['created_at'] ?? null,
                'updated_at' => $row['updated_at'] ?? null,
            ];
        }, $rows);

        return [
            'data' => $data,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => $limit > 0 ? (int) ceil($total / $limit) : 0,
                'has_next_page' => ($page * $limit) < $total,
                'has_prev_page' => $page > 1,
            ],
        ];
    }
}
