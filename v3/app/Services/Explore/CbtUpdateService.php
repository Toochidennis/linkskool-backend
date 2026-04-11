<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Events\EventDispatcher;
use V3\App\Events\CbtUpdate\CbtUpdatePublished;
use V3\App\Models\Explore\CbtUpdate;

class CbtUpdateService
{
    private CbtUpdate $cbtUpdateModel;

    public function __construct(\PDO $pdo)
    {
        $this->cbtUpdateModel = new CbtUpdate($pdo);
    }

    public function storeUpdate(array $data): array|false
    {
        $this->guardPayload($data);

        $payload = [
            'title' => $data['title'],
            'email_body' => $data['email_body'] ?? null,
            'notification_body' => $data['notification_body'] ?? null,
            'author_id' => $data['author_id'],
            'author_name' => $data['author_name'],
            'schedule_time' => $data['schedule_time'] ?? null,
            'status' => $data['status'],
            'send_email' => $this->normalizeBoolean($data['send_email'] ?? false) ? 1 : 0,
            'send_push' => $this->normalizeBoolean($data['send_push'] ?? false) ? 1 : 0,
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

    public function getUpdateById(int $updateId): array
    {
        return $this->cbtUpdateModel
            ->where('id', $updateId)
            ->first();
    }

    public function dispatchUpdate(int $updateId, ?int $notifiedBy = null): bool
    {
        $update = $this->getUpdateById($updateId);

        if (
            empty($update)
            || !$this->isPublished($update)
            || !$this->hasSelectedChannel($update)
            || !$this->isDue($update)
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
                !$this->hasSelectedChannel($update)
                || !$this->isDue($update)
                || !empty($update['notified_at'])
            ) {
                continue;
            }

            if ($this->dispatchUpdate((int) $update['id'], (int) ($update['author_id'] ?? 0))) {
                $processed++;
            }
        }

        return $processed;
    }

    private function guardPayload(array $data): void
    {
        $sendEmail = $this->normalizeBoolean($data['send_email'] ?? false);
        $sendPush = $this->normalizeBoolean($data['send_push'] ?? false);

        if (!$sendEmail && !$sendPush) {
            throw new \InvalidArgumentException('Select at least one delivery channel.');
        }

        if ($sendEmail && trim((string) ($data['email_body'] ?? '')) === '') {
            throw new \InvalidArgumentException('email_body is required when send_email is enabled.');
        }

        if ($sendPush && trim((string) ($data['notification_body'] ?? '')) === '') {
            throw new \InvalidArgumentException('notification_body is required when send_push is enabled.');
        }
    }

    private function shouldDispatchNow(array $update): bool
    {
        return $this->isPublished($update)
            && $this->hasSelectedChannel($update)
            && $this->isDue($update);
    }

    private function hasSelectedChannel(array $update): bool
    {
        return $this->normalizeBoolean($update['send_email'] ?? false)
            || $this->normalizeBoolean($update['send_push'] ?? false);
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

    private function normalizeBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        return in_array(
            strtolower(trim((string) $value)),
            ['1', 'true', 'yes', 'on'],
            true
        );
    }
}
