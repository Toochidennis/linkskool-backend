<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\Program;

class ProgramQueryService
{
    private Program $program;

    public function __construct(\PDO $pdo)
    {
        $this->program = new Program($pdo);
    }

    public function getPrograms()
    {
        $sql = "
        SELECT 
            p.id,
            p.slug,
            p.name,
            p.description,
            p.image_url,
            p.shortname,
            p.sponsor,
            p.status,
            COUNT(pc.course_id) AS course_count
        FROM programs p
        LEFT JOIN program_courses pc
            ON pc.program_id = p.id
            AND pc.is_active = 1
        WHERE p.status = :status
        GROUP BY
            p.id,
            p.slug,
            p.name,
            p.description,
            p.image_url,
            p.shortname,
            p.sponsor,
            p.status
        ORDER BY p.created_at DESC
        ";

        return $this->program->rawQuery($sql, ['status' => 'published']);
    }

    public function getProgramCourseCohorts(string $slug)
    {
        $summarySql = "
        SELECT
            p.id,
            p.slug,
            p.name,
            p.description,
            p.image_url,
            p.shortname,
            p.sponsor,
            p.start_date,
            COUNT(DISTINCT pc.course_id) AS course_count
        FROM programs p
        LEFT JOIN program_courses pc
            ON pc.program_id = p.id
            AND pc.is_active = 1
        WHERE p.slug = :slug
            AND p.status = :status
        GROUP BY
            p.id,
            p.slug,
            p.name,
            p.description,
            p.image_url,
            p.shortname,
            p.sponsor,
            p.start_date
        ORDER BY p.created_at DESC
        LIMIT 1
        ";

        $summaryRows = $this->program->rawQuery($summarySql, [
            'slug' => $slug,
            'status' => 'published'
        ]);

        if (empty($summaryRows)) {
            return [];
        }

        $sql = "
        SELECT 
            lc.id AS course_id,
            lc.title AS course_name,
            lc.description AS course_description,
            lc.image_url AS course_image_url,
            c.id AS cohort_id,
            c.slug AS cohort_slug,
            c.title AS cohort_title,
            c.discount,
            c.cost,
            c.trial_type,
            c.trial_value,
            c.is_free,
            c.enrollment_deadline,
            c.learning_type
        FROM programs p
        INNER JOIN program_courses pc
            ON pc.program_id = p.id
            AND pc.is_active = 1
        INNER JOIN learning_courses lc
            ON lc.id = pc.course_id
        INNER JOIN program_course_cohorts c
            ON c.program_id = p.id
            AND c.course_id = lc.id
        WHERE p.slug = :slug
            AND p.status = :status
            AND c.status IN ('ongoing', 'upcoming')
        ORDER BY lc.title ASC, c.created_at DESC, c.id DESC
        ";

        $rows = $this->program->rawQuery($sql, [
            'slug' => $slug,
            'status' => 'published'
        ]);

        $courses = [];

        foreach ($rows as $row) {
            $courseId = (int) $row['course_id'];

            if (!isset($courses[$courseId])) {
                $courses[$courseId] = [
                    'course_id' => $courseId,
                    'course_name' => $row['course_name'],
                    'description' => $row['course_description'],
                    'image_url' => $row['course_image_url'],
                    'start_date' => $row['start_date'] ?? null,
                    'cohort' => null,
                ];
            }

            if ($row['cohort_id'] !== null && $courses[$courseId]['cohort'] === null) {
                $courses[$courseId]['cohort'] = [
                    'cohort_id' => (int) $row['cohort_id'],
                    'slug' => $row['cohort_slug'],
                    'title' => $row['cohort_title'],
                    'discount' => $row['discount'] !== null ? (int) $row['discount'] : null,
                    'cost' => $row['cost'] !== null ? (float) $row['cost'] : null,
                    'trial_type' => $row['trial_type'],
                    'trial_value' => $row['trial_value'] !== null ? (int) $row['trial_value'] : null,
                    'is_free' => (bool) $row['is_free'],
                    'enrollment_deadline' => $row['enrollment_deadline'] ?? null,
                    'learning_type' => $row['learning_type'],
                ];
            }
        }

        $program = $summaryRows[0];

        return [
            'program' => [
                'id' => (int) $program['id'],
                'slug' => $program['slug'],
                'name' => $program['name'],
                'description' => $program['description'],
                'image_url' => $program['image_url'],
                'shortname' => $program['shortname'],
                'sponsor' => $program['sponsor'],
                'start_date' => $program['start_date'] ?? null,
                'course_count' => (int) $program['course_count'],
            ],
            'courses' => array_values($courses),
        ];
    }

    public function getCohortDetails(string $slug): array
    {
        $sql = "
        SELECT
            p.id AS program_id,
            p.slug AS program_slug,
            p.name AS program_name,
            p.description AS program_description,
            p.image_url AS program_image_url,
            p.sponsor AS program_sponsor,
            p.start_date AS program_start_date,
            lc.id AS course_id,
            lc.slug AS course_slug,
            lc.title AS course_name,
            lc.description AS course_description,
            lc.image_url AS course_image_url,
            c.id AS cohort_id,
            c.slug AS cohort_slug,
            c.title AS cohort_title,
            c.description AS cohort_description,
            c.benefits AS cohort_benefits,
            c.start_date,
            c.end_date,
            c.instructor_name,
            c.discount,
            c.cost,
            c.trial_type,
            c.trial_value,
            c.delivery_mode,
            c.video_url,
            c.image_url AS cohort_image_url,
            c.is_free,
            c.enrollment_deadline,
            c.learning_type
        FROM program_course_cohorts c
        INNER JOIN programs p
            ON p.id = c.program_id
        INNER JOIN program_courses pc
            ON pc.program_id = p.id
            AND pc.course_id = c.course_id
            AND pc.is_active = 1
        INNER JOIN learning_courses lc
            ON lc.id = c.course_id
        WHERE c.slug = :slug
            AND p.status = :status
            AND c.status IN ('ongoing', 'upcoming')
        LIMIT 1
        ";

        $rows = $this->program->rawQuery($sql, [
            'slug' => $slug,
            'status' => 'published',
        ]);

        if (empty($rows)) {
            return [];
        }

        $row = $rows[0];

        return [
            'program' => [
                'id' => (int) $row['program_id'],
                'slug' => $row['program_slug'],
                'name' => $row['program_name'],
                'description' => $row['program_description'],
                'image_url' => $row['program_image_url'],
                'sponsor' => $row['program_sponsor'],
                'start_date' => $row['program_start_date'] ?? null,
            ],
            'course' => [
                'courseId' => (int) $row['course_id'],
                'slug' => $row['course_slug'],
                'courseName' => $row['course_name'],
                'description' => $row['course_description'],
                'image_url' => $row['course_image_url'],
            ],
            'cohort' => [
                'cohortId' => (int) $row['cohort_id'],
                'slug' => $row['cohort_slug'],
                'title' => $row['cohort_title'],
                'description' => $row['cohort_description'],
                'benefits' => $row['cohort_benefits'],
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'instructor_name' => $row['instructor_name'],
                'discount' => $row['discount'] !== null ? (int) $row['discount'] : null,
                'cost' => $row['cost'] !== null ? (float) $row['cost'] : null,
                'trial_type' => $row['trial_type'],
                'trial_value' => $row['trial_value'] !== null ? (int) $row['trial_value'] : null,
                'delivery_mode' => $row['delivery_mode'],
                'video_url' => $row['video_url'],
                'is_free' => (bool) $row['is_free'],
                'image_url' => $row['cohort_image_url'],
                'enrollment_deadline' => $row['enrollment_deadline'],
                'learning_type' => $row['learning_type'],
            ],
        ];
    }
}
