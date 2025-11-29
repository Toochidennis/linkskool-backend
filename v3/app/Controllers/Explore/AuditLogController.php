<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Services\Explore\AuditLogService;

#[Group('/public')]
class AuditLogController extends ExploreBaseController
{
    private AuditLogService $auditLogService;

    public function __construct()
    {
        parent::__construct();
        $this->auditLogService = new AuditLogService($this->pdo);
    }

    #[Route(
        '/audit-logs/user/{userId:\d+}',
        'GET',
        ['api', 'auth', 'role:admin', 'role:user']
    )]
    public function getLogsByUserId(int $userId): void
    {
        $logs = $this->auditLogService->getLogsByUserId($userId);

        $this->respond([
            'success' => true,
            'logs' => $logs
        ]);
    }
}
