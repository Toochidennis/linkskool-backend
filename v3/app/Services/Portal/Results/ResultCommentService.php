<?php

namespace V3\App\Services\Portal\Results;

use PDO;
use V3\App\Models\Portal\Results\ResultCommentModel;

class ResultCommentService
{
    private ResultCommentModel $resultComment;
    private string $role;

    public function __construct(PDO $pdo)
    {
        $this->resultComment = new ResultCommentModel($pdo);
        $this->role = $_SESSION['role'] ?? '';
    }

    public function insertComment(array $data)
    {
        $payload = [
            'year' => $data['year'],
            'term' => $data['term'],
            'reg_no' => $data['student_id'],
        ];

        if ($this->role === 'admin') {
            $payload['principal'] = $data['comment'];
        } else {
            $payload['form_teacher'] = "Aha";
        }

        if (
            $this->resultComment
            ->where('reg_no', '=', $data['student_id'])
            ->where('year', '=', $data['year'])
            ->where('term', '=', $data['term'])
            ->exists()
        ) {
            $this->resultComment
                ->where('reg_no', '=', $data['student_id'])
                ->where('year', '=', $data['year'])
                ->where('term', '=', $data['term'])
                ->update($payload);
        }

        return $this->resultComment->insert($payload);
    }

    public function updateComment(array $data)
    {
        $payload = [
            'year' => $data['year'],
            'term' => $data['term'],
            'reg_no' => $data['student_id'],
        ];

        if ($this->role === 'admin') {
            $payload['principal'] = $data['comment'];
        } else {
            $payload['form_teacher'] = $data['comment'];
        }

        return $this->resultComment
            ->where('id', '=', $data['id'])
            ->update($payload);
    }
}
