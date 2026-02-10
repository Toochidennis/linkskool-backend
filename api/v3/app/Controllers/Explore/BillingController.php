<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Services\Explore\BillingService;

#[Group('/public/cbt')]
class BillingController extends ExploreBaseController
{
    private BillingService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new BillingService($this->pdo);
    }

    #[Route('/billing/verify', 'POST', ['api'])]
    public function verify()
    {
        $validated = $this->validate(
            $this->getRequestData(),
            [
                'user_id' => 'required|integer',
                'plan_id' => 'required|integer',
                'method' => 'required|string|in:online,voucher',
                'platform' => 'required|string|in:mobile,desktop',
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'voucher_code' => 'required_if:method,voucher|string',
                'reference' => 'required_if:method,online|string',
            ]
        );

        $res =  $this->service->verify($validated);

        $this->respond([
            'success' => true,
            'message' => 'Payment successful',
            'data' => $res
        ]);
    }
}
