<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Utilities\AssetUrl;
use V3\App\Models\Explore\ProgramCourseCohort;
use V3\App\Models\Explore\ProgramFlow;

class ProgramFlowService
{
    protected ProgramFlow $programFlow;
    private ProgramCourseCohort $courseCohort;

    public function __construct(\PDO $pdo)
    {
        $this->programFlow = new ProgramFlow($pdo);
        $this->courseCohort = new ProgramCourseCohort($pdo);
    }

    public function upsert(int $programId, array $cohortIds): bool
    {
        $this->programFlow->beginTransaction();

        try {
            $this->programFlow
                ->where('program_id', $programId)
                ->delete();

            foreach ($cohortIds as $index => $cohortId) {
                $this->programFlow->insert([
                    'program_id' => $programId,
                    'cohort_id'  => (int) $cohortId,
                    'flow_order' => $index + 1,
                    'is_active'  => 1,
                ]);
            }

            $this->programFlow->commit();
            return true;
        } catch (\Throwable $e) {
            $this->programFlow->rollBack();
            throw $e;
        }
    }

    public function getCohorts(int $programId): array
    {
        $allCohorts = $this->courseCohort
            ->where('program_id', $programId)
            ->orderBy('start_date', 'ASC')
            ->get();

        // Fetch flow
        $flow = $this->programFlow
            ->rawQuery(
                "SELECT
                pf.id AS flow_id,
                pf.program_id AS flow_program_id,
                pf.cohort_id,
                pf.flow_order,
                pf.is_active,
                c.id,
                c.program_id,
                c.course_id,
                c.course_name,
                c.title,
                c.description,
                c.slug,
                c.code,
                c.start_date,
                c.end_date,
                c.status,
                c.image_url,
                c.capacity,
                c.delivery_mode,
                c.zoom_link,
                c.is_free,
                c.trial_type,
                c.trial_value,
                c.cost,
                c.instructor_name,
                c.created_at,
                c.updated_at
             FROM program_flow pf
             INNER JOIN program_course_cohorts c
                ON c.id = pf.cohort_id
             WHERE pf.program_id = :program_id
             ORDER BY pf.flow_order ASC",
                ['program_id' => $programId]
            );

        if (empty($flow)) {
            return [
                'program_flow' => [],
                'available_cohorts' => $this->formatCohortImageUrls($allCohorts)
            ];
        }

        $flowCohortIds = array_column($flow, 'cohort_id');

        $available = array_filter($allCohorts, function ($cohort) use ($flowCohortIds) {
            return !in_array($cohort['id'], $flowCohortIds);
        });

        return [
            'program_flow' => $this->formatCohortImageUrls($flow),
            'available_cohorts' => $this->formatCohortImageUrls(array_values($available)),
        ];
    }

    private function formatCohortImageUrls(array $cohorts): array
    {
        foreach ($cohorts as &$cohort) {
            if (array_key_exists('image_url', $cohort)) {
                $cohort['image_url'] = AssetUrl::fromAppUrl($cohort['image_url']);
            }
        }

        unset($cohort);

        return $cohorts;
    }
}
