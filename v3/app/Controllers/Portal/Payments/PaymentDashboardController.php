<?php

namespace V3\App\Controllers\Portal\Payments;

use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\Payments\PaymentDashboardService;

#[Group('/portal/payments')]
class PaymentDashboardController extends BaseController
{
    private PaymentDashboardService $paymentService;

    public function __construct()
    {
        parent::__construct();
        $this->paymentService = new PaymentDashboardService($this->pdo);
    }

    #[Route(
        '/dashboard/summary',
        'GET',
        ['auth', 'role:admin']
    )]
    public function getDashboardSummary(array $vars)
    {
        $filteredVars = $this->validate(
            $vars,
            [
                'year' => 'required|integer',
                'term' => 'required|integer',
            ]
        );

        return $this->respond([
            'success' => true,
            'data' => $this->paymentService->getSummary($filteredVars)
        ]);
    }

    #[Route(
        '/invoices/paid',
        'GET',
        ['auth', 'role:admin']
    )]
    public function getPaidInvoices(array $vars)
    {
        $filteredVars = $this->validate(
            $vars,
            [
                'year' => 'required|integer',
                'term' => 'required|integer',
                'class_id' => 'required|integer',
            ]
        );

        return $this->respond([
            'success' => true,
            'data' => $this->paymentService->paidInvoices($filteredVars)
        ]);
    }

    #[Route(
        '/invoices/unpaid',
        'GET',
        ['auth', 'role:admin']
    )]
    public function getUnpaidInvoices(array $vars)
    {
        $filteredVars = $this->validate(
            $vars,
            [
                'year' => 'required|integer',
                'term' => 'required|integer',
                'class_id' => 'required|integer',
            ]
        );

        return $this->respond([
            'success' => true,
            'data' => $this->paymentService->unpaidInvoices($filteredVars)
        ]);
    }
}
