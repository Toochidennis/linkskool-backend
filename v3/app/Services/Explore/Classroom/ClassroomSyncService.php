<?php

namespace V3\App\Services\Explore\Classroom;

use PDO;

class ClassroomSyncService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function pull(string $institutionId): array
    {
        return [
            'institution'        => $this->fetchOne(
                "SELECT * FROM classroom_institutions WHERE id = ?",
                [$institutionId]
            ),
            'courses'            => $this->fetchAll(
                "SELECT * FROM classroom_courses WHERE institution_id = ?",
                [$institutionId]
            ),
            'students'           => $this->fetchAll(
                "SELECT * FROM classroom_students WHERE institution_id = ?",
                [$institutionId]
            ),
            'staff'              => $this->fetchAll(
                "SELECT * FROM classroom_staff WHERE institution_id = ?",
                [$institutionId]
            ),
            'course_staff'       => $this->fetchAll(
                "SELECT * FROM classroom_course_staff WHERE institution_id = ?",
                [$institutionId]
            ),
            'enrollments'        => $this->fetchAll(
                "SELECT * FROM classroom_course_enrollments WHERE institution_id = ?",
                [$institutionId]
            ),
            'lessons'            => $this->fetchAll(
                "SELECT * FROM classroom_course_lessons WHERE institution_id = ?",
                [$institutionId]
            ),
            'lesson_assignments' => $this->fetchAll(
                "SELECT * FROM classroom_course_lesson_assignments WHERE institution_id = ?",
                [$institutionId]
            ),
            'lesson_files'       => $this->fetchAll(
                "SELECT * FROM classroom_course_lesson_files WHERE institution_id = ?",
                [$institutionId]
            ),
            'quiz_settings'      => $this->fetchAll(
                "SELECT * FROM classroom_course_quiz_settings WHERE institution_id = ?",
                [$institutionId]
            ),
            'quizzes'            => $this->fetchAll(
                "SELECT * FROM classroom_course_quizzes WHERE institution_id = ?",
                [$institutionId]
            ),
            'quiz_attempts'      => $this->fetchAll(
                "SELECT * FROM classroom_quiz_attempts WHERE institution_id = ?",
                [$institutionId]
            ),
            'lesson_progress'    => $this->fetchAll(
                "SELECT * FROM classroom_course_lesson_progress WHERE institution_id = ?",
                [$institutionId]
            ),
            'sync_logs'          => $this->fetchAll(
                "SELECT * FROM classroom_sync_logs WHERE institution_id = ?",
                [$institutionId]
            ),
            'level_mappings'     => $this->fetchAll(
                "SELECT * FROM classroom_level_mappings WHERE institution_id = ?",
                [$institutionId]
            ),
        ];
    }

    public function push(array $payload): array
    {
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        $this->pdo->beginTransaction();

        try {
            $counts = [];

            // FK order: institution → courses/students/staff → dependents → leaves
            if (!empty($payload['institution'])) {
                $row = isset($payload['institution']['id'])
                    ? [$payload['institution']]
                    : $payload['institution'];
                $counts['institution'] = $this->upsert('classroom_institutions', $row, [
                    'id', 'slug', 'name', 'type', 'user_id',
                    'logo_url', 'banner_url', 'website', 'phone', 'email', 'address',
                    'join_code', 'created_at', 'updated_at', 'deleted_at',
                ]);
            }

            if (!empty($payload['level_mappings'])) {
                $counts['level_mappings'] = $this->upsert('classroom_level_mappings', $payload['level_mappings'], [
                    'id', 'institution_id', 'local_level_id', 'external_level_id',
                    'external_system', 'external_name',
                    'created_at', 'updated_at', 'deleted_at',
                ]);
            }

            if (!empty($payload['courses'])) {
                $counts['courses'] = $this->upsert('classroom_courses', $payload['courses'], [
                    'id', 'institution_id', 'slug', 'name', 'description', 'image_url',
                    'created_by', 'subject_id', 'level_id', 'duration',
                    'pricing_type', 'price', 'discount_price', 'status', 'join_code',
                    'created_at', 'updated_at', 'deleted_at',
                ]);
            }

            if (!empty($payload['students'])) {
                $counts['students'] = $this->upsert('classroom_students', $payload['students'], [
                    'id', 'institution_id', 'level_id',
                    'first_name', 'last_name', 'middle_name', 'phone', 'reg_number',
                    'created_at', 'updated_at', 'deleted_at',
                ]);
            }

            if (!empty($payload['staff'])) {
                $counts['staff'] = $this->upsert('classroom_staff', $payload['staff'], [
                    'id', 'institution_id', 'first_name', 'last_name', 'email', 'phone',
                    'created_at', 'updated_at', 'deleted_at',
                ]);
            }

            if (!empty($payload['course_staff'])) {
                $counts['course_staff'] = $this->upsert('classroom_course_staff', $payload['course_staff'], [
                    'id', 'institution_id', 'course_id', 'staff_id',
                    'created_at', 'updated_at', 'deleted_at',
                ]);
            }

            if (!empty($payload['enrollments'])) {
                $counts['enrollments'] = $this->upsert('classroom_course_enrollments', $payload['enrollments'], [
                    'id', 'institution_id', 'course_id', 'student_id',
                    'created_at', 'updated_at', 'deleted_at',
                ]);
            }

            if (!empty($payload['lessons'])) {
                $counts['lessons'] = $this->upsert('classroom_course_lessons', $payload['lessons'], [
                    'id', 'institution_id', 'course_id', 'slug', 'title',
                    'description', 'goals', 'objectives',
                    'video_url', 'recorded_video_url', 'thumbnail', 'zoom_info',
                    'display_order', 'write_up_content', 'is_final_lesson',
                    'author_name', 'author_id', 'lesson_date', 'status',
                    'created_at', 'updated_at', 'deleted_at',
                ]);
            }

            if (!empty($payload['lesson_assignments'])) {
                $counts['lesson_assignments'] = $this->upsert('classroom_course_lesson_assignments', $payload['lesson_assignments'], [
                    'id', 'institution_id', 'course_id', 'lesson_id',
                    'instructions', 'due_date', 'submission_type',
                    'created_at', 'updated_at', 'deleted_at',
                ]);
            }

            if (!empty($payload['lesson_files'])) {
                $counts['lesson_files'] = $this->upsert('classroom_course_lesson_files', $payload['lesson_files'], [
                    'id', 'institution_id', 'course_id', 'lesson_id', 'type', 'url',
                    'created_at', 'updated_at', 'deleted_at',
                ]);
            }

            if (!empty($payload['quiz_settings'])) {
                $counts['quiz_settings'] = $this->upsert('classroom_course_quiz_settings', $payload['quiz_settings'], [
                    'id', 'institution_id', 'course_id', 'lesson_id',
                    'topic', 'duration', 'start_date', 'end_date',
                    'created_at', 'updated_at', 'deleted_at',
                ]);
            }

            if (!empty($payload['quizzes'])) {
                $counts['quizzes'] = $this->upsert('classroom_course_quizzes', $payload['quizzes'], [
                    'id', 'institution_id', 'quiz_settings_id', 'course_id',
                    'question_text', 'options', 'correct',
                    'created_at', 'updated_at', 'deleted_at',
                ]);
            }

            if (!empty($payload['quiz_attempts'])) {
                $counts['quiz_attempts'] = $this->upsert('classroom_quiz_attempts', $payload['quiz_attempts'], [
                    'id', 'institution_id', 'quiz_id', 'course_id', 'student_id',
                    'score', 'answers',
                    'created_at', 'updated_at', 'deleted_at',
                ]);
            }

            if (!empty($payload['lesson_progress'])) {
                $counts['lesson_progress'] = $this->upsert('classroom_course_lesson_progress', $payload['lesson_progress'], [
                    'id', 'institution_id', 'course_id', 'lesson_id', 'student_id',
                    'progress_percent', 'completed',
                    'created_at', 'updated_at', 'deleted_at',
                ]);
            }

            if (!empty($payload['sync_logs'])) {
                $counts['sync_logs'] = $this->upsert('classroom_sync_logs', $payload['sync_logs'], [
                    'id', 'institution_id', 'table_name', 'record_id', 'operation', 'payload',
                    'local_version', 'sync_status', 'source', 'device_id',
                    'retry_count', 'error_message', 'last_synced_at',
                    'created_at', 'updated_at', 'deleted_at',
                ]);
            }

            $this->pdo->commit();
            return $counts;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        } finally {
            $this->pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    private function upsert(string $table, array $rows, array $columns): int
    {
        $total = 0;
        foreach (array_chunk($rows, 200) as $chunk) {
            $total += $this->upsertChunk($table, $chunk, $columns);
        }
        return $total;
    }

    private function upsertChunk(string $table, array $rows, array $columns): int
    {
        $colList  = implode(', ', array_map(fn($c) => "`$c`", $columns));
        $rowPh    = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        $allPh    = implode(', ', array_fill(0, count($rows), $rowPh));
        $updates  = implode(', ', array_map(
            fn($c) => "`$c` = VALUES(`$c`)",
            array_filter($columns, fn($c) => $c !== 'id' && $c !== 'created_at')
        ));

        $sql = "INSERT INTO `$table` ($colList) VALUES $allPh ON DUPLICATE KEY UPDATE $updates";

        $bindings = [];
        foreach ($rows as $row) {
            foreach ($columns as $col) {
                $val = $row[$col] ?? null;
                if (\is_array($val)) {
                    $val = json_encode($val);
                } elseif (\is_string($val) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $val)) {
                    $val = (new \DateTime($val))->format('Y-m-d H:i:s');
                }
                $bindings[] = $val;
            }
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->rowCount();
    }

    private function fetchOne(string $sql, array $bindings = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        return $this->normalizeDates($row);
    }

    private function fetchAll(string $sql, array $bindings = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return array_map([$this, 'normalizeDates'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function normalizeDates(array $row): array
    {
        foreach ($row as $key => $val) {
            if (\is_string($val) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $val)) {
                $row[$key] = str_replace(' ', 'T', $val) . 'Z';
            }
        }
        return $row;
    }
}
