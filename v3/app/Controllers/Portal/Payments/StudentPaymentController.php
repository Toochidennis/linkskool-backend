<?php

namespace V3\App\Controllers\Portal\Payments;

use V3\App\Common\Utilities\HttpStatus;
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

    public function makePayment(array $vars)
    {
        $cleanedData = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'invoice_id ' => 'required|string',
                'reference' => 'required|string',
                'student_id' => 'required|integer',
                'reg_no' => 'required|string',
                'name' => 'required|string',
                'amount' => 'required|numeric|min:1',
                'class_id' => 'required|integer',
                'level_id' => 'required|integer',
                'year' => 'required|digits:4',
                'term' => 'required|integer|in:1,2,3',
            ],
        );

        try {
            $newId = $this->studentPayment->addPayment($cleanedData);

            if ($newId) {
                $this->respond(
                    [
                        'success' => true,
                        'message' => 'Validation successfully'
                    ],
                    HttpStatus::CREATED
                );
            }

            $this->respondError(
                'Validation failed',
                HttpStatus::BAD_REQUEST
            );
        } catch (\Exception $e) {
            $this->respondError($e->getMessage());
        }
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
