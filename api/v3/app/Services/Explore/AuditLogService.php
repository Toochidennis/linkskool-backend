<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\AuditLog;

class AuditLogService
{
    private AuditLog $auditLog;

    public function __construct(\PDO $pdo)
    {
        $this->auditLog = new AuditLog($pdo);
    }

    public function getLogsByUserId(int $userId): array
    {
        $auditLogs = $this->auditLog;

        if ($userId > 0) {
            $auditLogs = $auditLogs->where('user_id', '=', $userId);
        }

        return $auditLogs->orderBy('created_at', 'DESC')->get();
    }
}
