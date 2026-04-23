<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\StudyTopicExamType;

class StudyContentService
{
    protected StudyTopicExamType $studyTopicExamType;

    public function __construct(\PDO $pdo)
    {
        $this->studyTopicExamType = new StudyTopicExamType($pdo);
    }

    public function getTopicByExamType(int $examTypeId): array
    {
        $topics = $this->studyTopicExamType
            ->select([
                'topic_id' => 't.id',
                'topic_name' => 't.title',
                'category_id' => 't.category_id',
                'category_name' => 'c.name',
            ])
            ->join('study_topics as t', 'study_topic_exam_types.topic_id', '=', 't.id')
            ->join('study_categories as c', 't.category_id', '=', 'c.id')
            ->where('study_topic_exam_types.exam_type_id', $examTypeId)
            ->get();

        return array_map(function ($row) {
            return [
                'topic_id' => $row['topic_id'],
                'topic_name' => $row['topic_name'],
                'category_name' => $row['category_name'],
            ];
        }, $topics);
    }
}
