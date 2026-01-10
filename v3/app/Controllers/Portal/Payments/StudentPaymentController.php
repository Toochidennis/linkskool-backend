<?php

namespace V3\App\Controllers\Portal\Payments;

use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\BaseController;
use V3\App\Common\Routing\{Route, Group};
use V3\App\Services\Portal\Payments\StudentPaymentService;

#[Group('/portal')]
class StudentPaymentController extends BaseController
{
    private StudentPaymentService $studentPayment;

    public function __construct()
    {
        parent::__construct();
        $this->studentPayment = new StudentPaymentService($this->pdo);
    }

    #[Route(
        '/students/{student_id:\d+}/make-payment',
        'POST',
        ['auth', 'role:admin', 'role:student']
    )]
    public function makePayment(array $vars)
    {
        $cleanedData = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'invoice_id' => 'required|string|filled',
                'reference' => 'required|string|filled',
                'student_id' => 'required|integer|min:1',
                'reg_no' => 'required|string|filled',
                'name' => 'required|string|filled',
                'amount' => 'required|numeric|min:1',
                'class_id' => 'required|integer',
                'level_id' => 'required|integer',
                'type' => 'required|string|in:offline,online',
                'year' => 'required|digits:4',
                'term' => 'required|integer|in:1,2,3',
                'invoice_details' => 'required|array|min:1',
                'invoice_details.*.fee_id' => 'required|integer',
                'invoice_details.*.fee_name' => 'required|string|filled',
                'invoice_details.*.amount' => 'required|numeric',
            ],
        );

        $newId = $this->studentPayment->addPayment($cleanedData);

        if ($newId) {
            $this->respond(
                [
                    'success' => true,
                    'message' => 'Payment successful'
                ],
                HttpStatus::CREATED
            );
        }

        $this->respondError(
            'Validation failed',
            HttpStatus::BAD_REQUEST
        );
    }

    #[Route(
        '/students/{student_id:\d+}/financial-records',
        'GET',
        ['auth', 'role:student', 'role:admin']
    )]
    public function getFinancialRecords(array $vars)
    {
        $cleanedData = $this->validate($vars, ['student_id' => 'required|integer']);

        $this->respond([
            'success' => true,
            'response' => $this->studentPayment
                ->getInvoiceAndTransactionHistory($cleanedData['student_id']),
        ]);
    }
}
