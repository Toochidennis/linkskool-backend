<?php

namespace V3\App\Services\Portal;

use PDO;
use V3\App\Models\Portal\Assessment;

class AssessmentService
{
    private Assessment $assessment;

    public function __construct(PDO $pdo)
    {
        $assessment = new Assessment($pdo);
    }

    public function insertAssessment($assessments): bool
    {
        $assessments = array_map(
            fn($assess) =>
            [
                'assesment_name' => $assess['assessment_name'],
                'max_score' => $assess['max_score'],
                'level' => $assess['level_id']
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
        //TODO
    }

    public function getAssessments(int $levelId = 0)
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
            ->join('level_table', 'assessment_table.level = level_table.id');

        if ($levelId != 0) {
            $assessments->where('level', '=', $levelId);
        }

        return $assessments = $assessments->get();
    }

    public function transformAssessment($assessments)
    {
        print_r($assessments);
        die;

        $formatted = [];

        foreach ($assessments as $assessment) {
            $levelName = $assessment['level_name'];
            $levelId = $assessment['level_id'];

            if (!isset($formatted[$levelName])) {
                $formatted[$levelName] = [];
            }

            if (!isset($formatted[$levelName]['level_id'])) {
                $formatted[$levelName] = ['level_id' => $levelId, 'level_name' => $levelName];
            }

            $formatted[$levelName]['assessments'][] = [
                'id' => $assessment['id'],
                'assessment_name' => $assessment['assessment_name'],
                'type' => $assessment['type']
            ];
        }
    }

    public function deleteAssessment($id): bool
    {
        return $this->assessment
            ->where('id', '=', $id)
            ->delete();
    }
}
