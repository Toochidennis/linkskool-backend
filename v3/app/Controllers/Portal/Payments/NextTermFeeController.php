<?php

namespace V3\App\Controllers\Portal\Payments;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Payments\NextTermFeeService;

class NextTermFeeController extends BaseController
{
    private NextTermFeeService $nextTermFeeService;

    public function __construct()
    {
        parent::__construct();
        $this->nextTermFeeService = new NextTermFeeService($this->pdo);
    }

    public function upsert()
    {
        $cleanedData = $this->validate(
            data: $this->post,
            rules: [
                'year' => 'required|integer',
                'term' => 'required|integer',
                'level_id' => 'required|integer',
                'fees' => 'required|array|min:1',
                'fees.*.fee_id' => 'required|integer',
                'fees.*.fee_name' => 'required|string|filled',
                'fees.*.amount' => 'required|numeric',
            ]
        );

        try {
            $newId = $this->nextTermFeeService->upsertFeeAmount($cleanedData);

            if ($newId > 0) {
                $this->respond([
                    'success' => true,
                    'message' => 'Next term fee added successfully'
                ]);
            }
            $this->respondError(
                'Failed to add fee. Are there students in this class?',
                HttpStatus::BAD_REQUEST
            );
        } catch (\Exception $e) {
            $this->respondError($e->getMessage());
        }
    }

    public function get(array $vars)
    {
        $cleanedData = $this->validate(
            data: $vars,
            rules: [
                'year' => 'required|integer',
                'term' => 'required|integer',
                'level_id' => 'required|integer',
            ]
        );

        try {
            $this->respond([
                'success' => true,
                'response' => $this->nextTermFeeService->termFeesByLevel($cleanedData),
            ]);
        } catch (\Exception $e) {
            $this->respondError($e->getMessage());
        }
    }
}

// /Models
//   - Account.php
//   - FeeType.php
//   - FeeAmount.php
//   - Vendor.php
//   - Expenditure.php
//   - Receipt.php
//   - Transaction.php
//   - Payment.php

// /Controllers
//   - AccountController.php
//   - FeeTypeController.php
//   - FeeAmountController.php
//   - VendorController.php
//   - ExpenditureController.php
//   - ReceiptController.php
//   - TransactionController.php
//   - StudentPaymentController.php
//   - PaymentStatusController.php
//   - PaymentDashboardController.php
//   - ReceiptViewerController.php

// /Services
//   - AccountService.php
//   - FeeTypeService.php
//   - FeeAmountService.php
//   - VendorService.php
//   - ExpenditureService.php
//   - ReceiptService.php
//   - TransactionService.php
//   - StudentPaymentService.php
//   - PaymentStatusService.php
//   - PaymentAnalyticsService.php
//   - ReceiptGeneratorService.php
