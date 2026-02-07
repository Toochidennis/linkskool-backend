<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\LicenseService;

#[Group('/public/cbt')]
class LicenseController extends ExploreBaseController
{
    private LicenseService $license;

    public function __construct()
    {
        parent::__construct();
        $this->license = new LicenseService($this->pdo);
    }

    #[Route('/license/activate/desktop', 'POST', ['api'])]
    public function activateDesktop()
    {
        $validated =  $this->validate(
            $this->getRequestData(),
            [
                'user_id' => 'required|integer',
                'fingerprint' => 'required|string',
            ]
        );

        $response = $this->license->activateDesktop($validated);

        if (!$response) {
            $this->respondError(
                'Failed to activate device',
                HttpStatus::BAD_REQUEST,
            );
        }

        $this->respond([
            'success' => true,
            'message' => '',
            'data' => $response,
        ]);
    }

    #[Route('/license/activate/mobile', 'POST', ['api'])]
    public function activateMobile()
    {
        $validated =  $this->validate(
            $this->getRequestData(),
            [
                'user_id' => 'required|integer',
            ]
        );

        $response = $this->license->activateMobile($validated['user_id']);

        if (!$response) {
            $this->respondError(
                'Failed to activate device',
                HttpStatus::BAD_REQUEST,
            );
        }

        $this->respond([
            'success' => true,
            'message' => '',
            'data' => $response,
        ]);
    }

    #[Route('/license/status/desktop', 'GET', ['api'])]
    public function desktopStatus(array $vars)
    {
        $validated =  $this->validate(
            $vars,
            [
                'user_id' => 'required|integer',
                'fingerprint' => 'required|string',
            ]
        );

        $response = $this->license->checkDesktopStatus(
            $validated['user_id'],
            $validated['fingerprint']
        );

        $this->respond(
            [
                'success' => true,
                'message' => 'Status retrieved successfully',
                'data' => $response,
            ]
        );
    }

    #[Route('/license/status/mobile', 'GET', ['api'])]
    public function mobileStatus(array $vars)
    {
        $validated =  $this->validate(
            $vars,
            [
                'user_id' => 'required|integer',
            ]
        );

        $response = $this->license->checkMobileStatus((int) $validated['user_id']);

        $this->respond(
            [
                'success' => true,
                'message' => 'Status retrieved successfully',
                'data' => $response,
            ]
        );
    }

    #[Route('/license/plans/desktop', 'GET', ['api'])]
    public function desktopPlans()
    {
        $plans = $this->license->getDesktopPlans();

        $this->respond([
            'success' => true,
            'message' => 'Plans retrieved successfully',
            'data' => $plans,
        ]);
    }

    #[Route('/license/plans/mobile', 'GET', ['api'])]
    public function mobilePlans()
    {
        $plans = $this->license->getMobilePlans();

        $this->respond([
            'success' => true,
            'message' => 'Plans retrieved successfully',
            'data' => $plans,
        ]);
    }
}
