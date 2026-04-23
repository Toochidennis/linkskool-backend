<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\StudyTopicExamType;

class StudyExamTypeTopicsService
{
    private StudyTopicExamType $studyTopicExamType;

    public function __construct(\PDO $pdo)
    {
        $this->studyTopicExamType = new StudyTopicExamType($pdo);
    }

    public function linkTopicToExamType(array $data): bool
    {
        $this->studyTopicExamType->beginTransaction();

        try {
            $this->studyTopicExamType
                ->where('exam_type_id', $data['exam_type_id'])
                ->where('course_id', $data['course_id'])
                ->delete();

            foreach ($data['topic_ids'] as $index => $topicId) {
                $this->studyTopicExamType->insert([
                    'topic_id' => $topicId,
                    'exam_type_id' => $data['exam_type_id'],
                    'course_id' => $data['course_id'],
                    'display_order' => $index + 1,
                ]);
            }

            $this->studyTopicExamType->commit();
            return true;
        } catch (\Throwable $e) {
            $this->studyTopicExamType->rollBack();
            throw $e;
        }
    }

    public function getTopicsByExamType(int $examTypeId): array
    {
        $topics = $this->studyTopicExamType
            ->select([
                'study_topic_exam_types.id AS id',
                'study_topic_exam_types.topic_id AS topic_id',
                'study_topics.title AS topic_name',
            ])
            ->join('study_topics', 'study_topics.id = study_topic_exam_types.topic_id')
            ->where('study_topic_exam_types.exam_type_id', $examTypeId)
            ->orderBy('study_topic_exam_types.display_order')
            ->get();

        return $topics;
    }
}
