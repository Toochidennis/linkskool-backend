<?php

namespace V3\App\Services\Portal\Academics;

use PDO;
use V3\App\Models\Portal\Academics\Assessment;

class AssessmentService
{
    private Assessment $assessment;

    public function __construct(PDO $pdo)
    {
        $this->assessment = new Assessment($pdo);
    }

    public function insertAssessment($assessments): bool
    {
        $assessments = array_map(
            fn($assess) =>
            [
                'assesment_name' => $assess['assessment_name'],
                'max_score' => $assess['max_score'],
                'level' => $assess['level_id'],
                'type' => $assess['type']
            ],
            $assessments
        );

        $count = 0;
        foreach ($assessments as $assess) {
            $inserted = $this->assessment->insert($assess);

            if ($inserted) {
                $count++;
            }
        }

        return $count === count($assessments);
    }

    public function updateAssessment($assessment)
    {
        $assess = [
            'assesment_name' => $assessment['assessment_name'],
            'max_score' => $assessment['max_score'],
            'level' => $assessment['level_id'],
            'type' => $assessment['type']
        ];

        return $this->assessment
            ->where('id', '=', $assessment['id'])
            ->update($assess);
    }

    private function getAssessments(int $levelId = 0)
    {
        $assessments = $this->assessment
            ->select(
                columns: [
                    'assessment_table.id',
                    'assessment_table.assesment_name AS assessment_name',
                    'assessment_table.max_score',
                    'assessment_table.type',
                    'level_table.id AS level_id',
                    'level_table.level_name'
                ]
            )
            ->join(
                'level_table',
                'assessment_table.level = level_table.id',
                'LEFT'
            );

        if ($levelId != 0) {
            $assessments->where('level', '=', $levelId);
        }

        return $assessments = $assessments->get();
    }

    public function getAllAssessments($levelId = 0)
    {
        $assessments = $this->getAssessments($levelId);

        $formatted = [];

        foreach ($assessments as $assessment) {
            $levelName = $assessment['level_name'] ?? 0;
            $levelId = $assessment['level_id'] ?? 0;

            if (!isset($formatted[$levelName]['level_id'])) {
                $formatted[$levelName] = ['level_id' => $levelId, 'level_name' => $levelName];
            }

            $formatted[$levelName]['assessments'][] = [
                'id' => $assessment['id'],
                'assessment_name' => $assessment['assessment_name'],
                'assessment_score' => $assessment['max_score'],
                'type' => $assessment['type']
            ];
        }

        return array_values($formatted);
    }

    public function deleteAssessment($id): bool
    {
        return $this->assessment
            ->where('id', '=', $id)
            ->delete();
    }
}
