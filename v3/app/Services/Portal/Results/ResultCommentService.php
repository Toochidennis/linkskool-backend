<?php

namespace V3\App\Services\Portal\Results;

use PDO;
use V3\App\Models\Portal\Results\ResultCommentModel;

class ResultCommentService
{
    private ResultCommentModel $resultComment;

    public function __construct(PDO $pdo)
    {
        $this->resultComment = new ResultCommentModel($pdo);
    }

    public function insertComment(array $data)
    {
        $payload = [
            'year' => $data['year'],
            'term' => $data['term'],
            'reg_no' => $data['student_id'],
        ];

        if ($data['role'] === 'admin') {
            $payload['principal'] = $data['comment'];
        } else {
            $payload['form_teacher'] = $data['comment'];
        }

        return $this->resultComment->insert($payload);
    }

    public function updateComment(array $data)
    {
        return $this->resultComment
            ->where('id', '=', $data['id'])
            ->update(['comment' => $data['comment']]);
    }
}
