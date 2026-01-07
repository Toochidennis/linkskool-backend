<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\AdvertisementService;

#[Group('/public/advertisements')]
class AdvertisementController extends ExploreBaseController
{
    private AdvertisementService $advertisementService;

    public function __construct()
    {
        parent::__construct();
        $this->advertisementService = new AdvertisementService($this->pdo);
    }

    #[Route('', 'POST', ['api', 'auth'])]
    public function createAdvertisement(): void
    {
        $validatedData = $this->validate(
            $this->getRequestData(),
            [
                'title' => 'required|string',
                'content' => 'required|string',
                'action_url' => 'required|url',
                'action_text' => 'required|string',
                'display_position' => 'nullable|in:top,bottom,sidebar,center',
                'status' => 'required|in:published,draft,archived',
                'author_id' => 'required|integer',
                'author_name' => 'required|string|max:100',
                'is_sponsored' => 'required|boolean',
                'image' => 'required|array',
                'image.name' => 'required|string',
                'image.tmp_name' => 'required|string',
                'image.error' => 'required|integer',
                'image.size' => 'required|integer'
            ]
        );

        $advertisementId = $this->advertisementService->createAdvertisement($validatedData);

        if (!$advertisementId) {
            $this->respondError(
                'Failed to create advertisement.',
                HttpStatus::BAD_REQUEST
            );
            return;
        }
        $this->respond(
            [
                'success' => true,
                'message' => 'Advertisement created successfully.',
                'id' => $advertisementId
            ],
            HttpStatus::CREATED
        );
    }

    #[Route('', 'GET', ['api', 'auth'])]
    public function getAllAdvertisements(): void
    {
        $advertisements = $this->advertisementService->getAllAdvertisements();
        $this->respond(
            [
                'success' => true,
                'data' => $advertisements
            ],
            HttpStatus::OK
        );
    }

    #[Route('/published', 'GET', ['api'])]
    public function getPublishedAdvertisements(): void
    {
        $advertisements = $this->advertisementService->getPublishedAdvertisements();
        $this->respond(
            [
                'success' => true,
                'data' => $advertisements
            ],
            HttpStatus::OK
        );
    }

    #[Route('/{id}/status', 'PUT', ['api', 'auth'])]
    public function updateAdvertisementStatus(array $vars): void
    {
        $validatedData = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'id' => 'required|integer',
                'status' => 'required|in:published,draft,archived'
            ]
        );

        $updated = $this->advertisementService->updateStatus(
            $validatedData['id'],
            $validatedData['status']
        );

        if (!$updated) {
            $this->respondError(
                'Failed to update advertisement status.',
                HttpStatus::BAD_REQUEST
            );
            return;
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Advertisement status updated successfully.'
            ],
            HttpStatus::OK
        );
    }

    #[Route('/{id}', 'DELETE', ['api', 'auth'])]
    public function deleteAdvertisement(array $vars): void
    {
        $validatedData = $this->validate(
            $vars,
            [
                'id' => 'required|integer'
            ]
        );

        $deleted = $this->advertisementService->deleteAdvertisement(
            $validatedData['id']
        );

        if (!$deleted) {
            $this->respondError(
                'Failed to delete advertisement.',
                HttpStatus::BAD_REQUEST
            );
            return;
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Advertisement deleted successfully.'
            ],
            HttpStatus::OK
        );
    }
}
