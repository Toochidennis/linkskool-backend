<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Events\EventDispatcher;
use V3\App\Events\CbtUpdate\CbtUpdateCommentAdded;
use V3\App\Events\CbtUpdate\CbtUpdatePublished;
use V3\App\Models\Explore\CbtUpdateComment;
use V3\App\Models\Explore\CbtUpdate;

class CbtUpdateService
{
    private CbtUpdate $cbtUpdateModel;
    private CbtUpdateComment $cbtUpdateCommentModel;

    public function __construct(\PDO $pdo)
    {
        $this->cbtUpdateModel = new CbtUpdate($pdo);
        $this->cbtUpdateCommentModel = new CbtUpdateComment($pdo);
    }

    public function storeUpdate(array $data): array|false
    {
        $payload = [
            'title' => $data['title'],
            'email_body' => $data['email_body'] ?? null,
            'notification_body' => $data['notification_body'] ?? null,
            'author_id' => $data['author_id'],
            'author_name' => $data['author_name'],
            'schedule_time' => $data['schedule_time'] ?? null,
            'status' => $data['status'],
            'send_email' => $data['send_email'] ?? 1,
            'send_push' =>  $data['send_push'] ?? 1,
            'tag' => $data['tag'],
        ];

        $updateId = $this->cbtUpdateModel->insert($payload);
        if (!$updateId) {
            return false;
        }

        $dispatched = false;
        if ($this->shouldDispatchNow($payload)) {
            $dispatched = $this->dispatchUpdate((int) $updateId, (int) $payload['author_id']);
        }

        return [
            'id' => (int) $updateId,
            'dispatched' => $dispatched,
            'scheduled' => $this->isPublished($payload)
                && !$dispatched
                && !empty($payload['schedule_time']),
        ];
    }

    public function updateUpdate(int $updateId, array $data): array|false|null
    {
        $existing = $this->getUpdateById($updateId);
        if (empty($existing)) {
            return null;
        }

        $payload = [
            'title' => $data['title'],
            'email_body' => $data['email_body'] ?? null,
            'notification_body' => $data['notification_body'] ?? null,
            'author_id' => $data['author_id'],
            'author_name' => $data['author_name'],
            'schedule_time' => $data['schedule_time'] ?? null,
            'status' => $data['status'],
            'send_email' => $data['send_email'] ?? 1,
            'send_push' => $data['send_push'] ?? 1,
            'tag' => $data['tag'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $updated = $this->cbtUpdateModel
            ->where('id', $updateId)
            ->update($payload);

        if (!$updated) {
            return false;
        }

        $latest = $this->getUpdateById($updateId);
        $dispatched = false;

        if (
            !empty($latest)
            && empty($latest['notified_at'])
            && $this->shouldDispatchNow($latest)
        ) {
            $dispatched = $this->dispatchUpdate(
                $updateId,
                (int) ($data['author_id'] ?? ($latest['author_id'] ?? 0))
            );
        }

        return [
            'id' => $updateId,
            'dispatched' => $dispatched,
            'scheduled' => $this->isPublished($latest)
                && !$dispatched
                && !empty($latest['schedule_time']),
        ];
    }

    public function getUpdateById(int $updateId): array
    {
        $update = $this->cbtUpdateModel
            ->where('id', $updateId)
            ->first();

        if (empty($update)) {
            return [];
        }

        return $this->appendCommentCount($update);
    }

    public function dispatchUpdate(int $updateId, ?int $notifiedBy = null): bool
    {
        $update = $this->getUpdateById($updateId);

        if (
            empty($update)
            || !$this->isPublished($update)
            || !empty($update['notified_at'])
        ) {
            return false;
        }

        EventDispatcher::dispatch(new CbtUpdatePublished($updateId));

        return $this->cbtUpdateModel
            ->where('id', $updateId)
            ->update([
                'notified_at' => date('Y-m-d H:i:s'),
                'notified_by' => $notifiedBy ?: (int) ($update['author_id'] ?? 0),
            ]);
    }

    public function dispatchScheduledUpdates(): int
    {
        $updates = $this->cbtUpdateModel
            ->where('status', 'published')
            ->orderBy(['schedule_time' => 'ASC', 'id' => 'ASC'])
            ->get();

        $processed = 0;

        foreach ($updates as $update) {
            if (
                !$this->isDue($update)
                || !empty($update['notified_at'])
            ) {
                continue;
            }

            if ($this->dispatchUpdate((int) $update['id'], (int) ($update['author_id']))) {
                $processed++;
            }
        }

        return $processed;
    }

    public function updateStatus(int $updateId, string $status): bool
    {
        $updated =  $this->cbtUpdateModel
            ->where('id', $updateId)
            ->update(['status' => $status]);

        if ($updated && $status === 'published') {
            $update = $this->getUpdateById($updateId);
            if ($this->shouldDispatchNow($update)) {
                return $this->dispatchUpdate($updateId, (int) ($update['author_id'] ?? 0));
            }
        }

        return $updated;
    }

    private function shouldDispatchNow(array $update): bool
    {
        return $this->isPublished($update)
            && $this->isDue($update);
    }

    private function isPublished(array $update): bool
    {
        return (string) ($update['status'] ?? '') === 'published';
    }

    private function isDue(array $update): bool
    {
        $scheduleTime = trim((string) ($update['schedule_time'] ?? ''));
        if ($scheduleTime === '') {
            return true;
        }

        try {
            return new \DateTimeImmutable($scheduleTime) <= new \DateTimeImmutable();
        } catch (\Throwable) {
            return false;
        }
    }

    public function getNotifiedCbtUpdates(int $page, int $limit = 10): array
    {
        $rows =  $this->cbtUpdateModel
            ->where('status', 'published')
            ->whereNotNull('email_body')
            ->orderBy(['notified_at' => 'DESC', 'created_at' => 'DESC'])
            ->paginate($page, $limit);

        $data = array_map(fn($row) => [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'tag' => $row['tag'],
            'content' => $row['email_body'],
            'notified_at' => $row['notified_at'] ?? null,
        ], $rows['data']);

        $data = $this->appendCommentCounts($data);

        return [
            'data' => $data,
            'pagination' => $rows['meta'],
        ];
    }

    public function getNotifiedCbtUpdateById(int $id): array
    {
        $row =  $this->cbtUpdateModel
            ->where('status', 'published')
            ->whereNotNull('email_body')
            ->where('id', $id)
            ->first();

        if (!$row) {
            return [];
        }

        return $this->appendCommentCount([
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'tag' => $row['tag'],
            'content' => $row['email_body'],
            'notified_at' => $row['notified_at'] ?? null,
        ]);
    }

    public function getAllCbtUpdates(int $page, int $limit = 10): array
    {
        return $this->cbtUpdateModel
            ->orderBy(['created_at' => 'DESC'])
            ->paginate($page, $limit);
    }

    public function addComment(array $data): array|false|null
    {
        $update = $this->getCommentableUpdateById((int) $data['id']);
        if (empty($update)) {
            return null;
        }

        $commentId = $this->cbtUpdateCommentModel->insert([
            'update_id' => (int) $data['id'],
            'user_id' => (int) $data['user_id'],
            'username' => trim((string) $data['username']),
            'body' => trim((string) $data['body']),
        ]);

        if (!$commentId) {
            return false;
        }

        EventDispatcher::dispatch(new CbtUpdateCommentAdded((int) $commentId));

        return $this->getCommentById((int) $commentId);
    }

    public function getCommentsByUpdateId(int $updateId, int $page = 1, int $limit = 20): array|null
    {
        $update = $this->getCommentableUpdateById($updateId);
        if (empty($update)) {
            return null;
        }

        $rows = $this->cbtUpdateCommentModel
            ->where('update_id', $updateId)
            ->orderBy(['created_at' => 'DESC', 'id' => 'DESC'])
            ->paginate($page, $limit);

        return [
            'data' => array_map(
                fn(array $row) => $this->formatComment($row),
                $rows['data']
            ),
            'pagination' => $rows['meta'],
        ];
    }

    private function getCommentById(int $commentId): array
    {
        $comment = $this->cbtUpdateCommentModel
            ->where('id', $commentId)
            ->first();

        return empty($comment) ? [] : $this->formatComment($comment);
    }

    private function getCommentableUpdateById(int $updateId): array
    {
        return $this->cbtUpdateModel
            ->select(['id'])
            ->where('id', $updateId)
            ->whereNotNull('notified_at')
            ->whereNotNull('email_body')
            ->first();
    }

    private function formatComment(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'update_id' => (int) $row['update_id'],
            'user_id' => (int) $row['user_id'],
            'username' => $row['username'],
            'body' => $row['body'],
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }

    private function appendCommentCount(array $update): array
    {
        $update['comments_count'] = $this->getCommentCountsByUpdateIds([(int) $update['id']])[(int) $update['id']] ?? 0;
        return $update;
    }

    private function appendCommentCounts(array $updates): array
    {
        if (empty($updates)) {
            return $updates;
        }

        $updateIds = array_values(array_unique(array_map(
            fn(array $update) => (int) $update['id'],
            $updates
        )));

        $counts = $this->getCommentCountsByUpdateIds($updateIds);

        return array_map(function (array $update) use ($counts) {
            $update['comments_count'] = $counts[(int) $update['id']] ?? 0;
            return $update;
        }, $updates);
    }

    private function getCommentCountsByUpdateIds(array $updateIds): array
    {
        $updateIds = array_values(array_filter(array_map('intval', $updateIds)));
        if (empty($updateIds)) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($updateIds), '?'));
        $rows = $this->cbtUpdateCommentModel->rawQuery(
            "SELECT update_id, COUNT(*) AS total
             FROM cbt_update_comments
             WHERE update_id IN ($placeholders)
             GROUP BY update_id",
            $updateIds
        );

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['update_id']] = (int) $row['total'];
        }

        return $counts;
    }
}
