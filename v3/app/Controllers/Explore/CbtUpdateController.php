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
                'send_email' => 'required|integer|in:0,1',
                'send_push' => 'required|integer|in:0,1',
                'tag' => 'required|string|max:255',
            ]
        );

        $result = $this->service->storeUpdate($data);


        if (!$result) {
            $this->respondError(
                'Failed to send CBT update.',
                HttpStatus::BAD_REQUEST
            );
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

    #[Route('/cbt-updates/{id:\d+}/status', 'PUT', ['api', 'auth', 'role:admin'])]
    public function updateStatus(array $vars): void
    {
        $data = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer|min:1',
                'status' => 'required|string|in:draft,published,archived',
            ]
        );

        $updated = $this->service->updateStatus(
            (int) $data['id'],
            $data['status']
        );

        if (!$updated) {
            $this->respondError(
                'Failed to update CBT update status.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'CBT update status updated successfully',
        ]);
    }

    #[Route('/cbt-updates/{id:\d+}', 'PUT', ['api', 'auth', 'role:admin'])]
    public function updateUpdate(array $vars): void
    {
        $data = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer|min:1',
                'title' => 'required|string|max:255',
                'email_body' => 'nullable|string',
                'notification_body' => 'nullable|string',
                'status' => 'required|string|in:draft,published,archived',
                'schedule_time' => 'nullable|date',
                'author_id' => 'required|integer|min:1',
                'author_name' => 'required|string|max:255',
                'send_email' => 'required|integer|in:0,1',
                'send_push' => 'required|integer|in:0,1',
                'tag' => 'required|string|max:255',
            ]
        );

        $result = $this->service->updateUpdate((int) $data['id'], $data);

        if ($result === null) {
            $this->respondError(
                'CBT update not found.',
                HttpStatus::NOT_FOUND
            );
            return;
        }

        if ($result === false) {
            $this->respondError(
                'Failed to update CBT update.',
                HttpStatus::BAD_REQUEST
            );
            return;
        }

        $message = $result['dispatched']
            ? 'CBT update updated and dispatched successfully'
            : 'CBT update updated successfully';

        $this->respond([
            'success' => true,
            'message' => $message,
            'data' => $result,
        ]);
    }

    #[Route('/cbt-updates', 'GET', ['api'])]
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

    #[Route('/cbt-updates/{id:\d+}', 'GET', ['api'])]
    public function getUpdateById(array $vars): void
    {
        $data = $this->validate(
            $vars,
            [
                'id' => 'required|integer|min:1',
            ]
        );
        $update = $this->service->getUpdateById((int) $data['id']);

        if (!$update) {
            $this->respondError(
                'CBT update not found.',
                HttpStatus::NOT_FOUND
            );
            return;
        }

        $this->respond([
            'success' => true,
            'data' => $update,
        ]);
    }

    #[Route('/cbt-updates/all', 'GET', ['api', 'auth', 'role:admin'])]
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
