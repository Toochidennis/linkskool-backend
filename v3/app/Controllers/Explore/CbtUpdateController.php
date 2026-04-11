<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\CbtUpdateService;

#[Group('/public')]
class CbtUpdateController extends ExploreBaseController
{
    private CbtUpdateService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new CbtUpdateService($this->pdo);
    }

    #[Route('/cbt-updates', 'POST', ['api', 'auth', 'role:admin'])]
    public function storeUpdate(): void
    {
        $data = $this->validate(
            $this->getRequestData(),
            [
                'title' => 'required|string|max:255',
                'email_body' => 'nullable|string',
                'notification_body' => 'nullable|string',
                'status' => 'required|string|in:draft,published,archived',
                'schedule_time' => 'nullable|date',
                'author_id' => 'required|integer|min:1',
                'author_name' => 'required|string|max:255',
                'send_email' => 'required|boolean',
                'send_push' => 'required|boolean',
                'tagline' => 'required|string|max:255',
            ]
        );

        $result = $this->service->storeUpdate($data);


        if (!$result) {
            $this->respondError(
                'Failed to send CBT update.',
                HttpStatus::BAD_REQUEST
            );
            return;
        }

        $message = $result['dispatched']
            ? 'CBT update created and dispatched successfully'
            : 'CBT update created successfully';

        $this->respond(
            [
                'success' => true,
                'message' => $message,
                'data' => $result,
            ],
            HttpStatus::CREATED
        );
    }

    #[Route('/cbt-updates/{id:\d+}/notify', 'PUT', ['api', 'auth', 'role:admin'])]
    public function notifyUpdate(array $vars): void
    {
        $data = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer|min:1',
                'notified_by' => 'required|integer|min:1',
            ]
        );

        $dispatched = $this->service->dispatchUpdate(
            (int) $data['id'],
            (int) $data['notified_by']
        );

        if (!$dispatched) {
            $this->respondError(
                'Failed to dispatch CBT update.',
                HttpStatus::BAD_REQUEST
            );
            return;
        }

        $this->respond([
            'success' => true,
            'message' => 'CBT update dispatched successfully',
        ]);
    }

    #[Route('/cbt-updates', 'GET', ['api', 'auth', 'role:admin'])]
    public function listUpdates(array $vars): void
    {
        $filters = $this->validate(
            $vars,
            [
                'page' => 'nullable|integer|min:1',
                'limit' => 'nullable|integer|min:1|max:50',
            ]
        );

        $page = $filters['page'] ?? 1;
        $limit = $filters['limit'] ?? 25;

        $updates = $this->service->getNotifiedCbtUpdates($page, $limit);

        $this->respond([
            'success' => true,
            'data' => $updates,
        ]);
    }

    public function listAllUpdates(array $vars)
    {
        $filters = $this->validate(
            $vars,
            [
                'page' => 'nullable|integer|min:1',
                'limit' => 'nullable|integer|min:1|max:50',
            ]
        );

        $page = $filters['page'] ?? 1;
        $limit = $filters['limit'] ?? 25;

        $updates = $this->service->getAllCbtUpdates($page, $limit);

        $this->respond([
            'success' => true,
            'data' => $updates,
        ]);
    }
}
