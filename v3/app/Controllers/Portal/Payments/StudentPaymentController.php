<?php

namespace V3\App\Controllers\Portal\Payments;

use V3\App\Controllers\BaseController;
use V3\App\Services\Portal\Payments\StudentPaymentService;

class StudentPaymentController extends BaseController
{
    private StudentPaymentService $studentPayment;

    public function __construct()
    {
        parent::__construct();
        $this->studentPayment = new StudentPaymentService($this->pdo);
    }

    public function getFinancialRecords(array $vars)
    {
        $cleanedData = $this->validate($vars, ['student_id' => 'required|integer']);

        try {
            $this->respond([
                'success' => true,
                'response' => $this->studentPayment
                    ->getInvoiceAndTransactionHistory($cleanedData['student_id']),
            ]);
        } catch (\Exception $e) {
            $this->respondError($e->getMessage());
        }
    }
}
