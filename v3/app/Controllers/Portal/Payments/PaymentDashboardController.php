<?php

namespace V3\App\Controllers\Portal\Payments;

use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Payments\PaymentDashboardService;

class PaymentDashboardController extends BaseController
{
    private PaymentDashboardService $paymentService;

    public function __construct()
    {
        parent::__construct();
        $this->paymentService = new PaymentDashboardService($this->pdo);
    }

    public function getDashboardSummary(array $vars)
    {
        $filteredVars = $this->validate(
            $vars,
            [
                'year' => 'required|integer',
                'term' => 'required|integer',
            ]
        );

        try {
            return $this->respond([
                'success' => true,
                'data' => $this->paymentService->getSummary($filteredVars)
            ]);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getPaidInvoices(array $vars)
    {
        $filteredVars = $this->validate(
            $vars,
            [
                'year' => 'required|integer',
                'term' => 'required|integer',
                'level_id' => 'required|integer',
            ]
        );

        try {
            return $this->respond([
                'success' => true,
                'data' => $this->paymentService->paidInvoices($filteredVars)
            ]);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function getUnpaidInvoices(array $vars)
    {
        $filteredVars = $this->validate(
            $vars,
            [
                'year' => 'required|integer',
                'term' => 'required|integer',
                'level_id' => 'required|integer',
            ]
        );

        try {
            return $this->respond([
                'success' => true,
                'data' => $this->paymentService->unpaidInvoices($filteredVars)
            ]);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }
}
