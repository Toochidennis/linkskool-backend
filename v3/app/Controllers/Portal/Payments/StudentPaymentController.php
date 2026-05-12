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
        '/students/{student_id:\d+}/payment/initiate',
        'POST',
        ['auth', 'role:admin', 'role:student']
    )]
    public function initiatePayment(array $vars)
    {
        $cleanedData = $this->validate(
            data: [...$this->post, ...$vars],
            rules: [
                'student_id' => 'required|integer|min:1',
                'invoice_id' => 'required|integer|min:1',
                'reg_no' => 'required|string|filled',
                'name' => 'required|string|filled',
                'email' => 'required_if:type,online|email',
                'items' => 'required|array|min:1',
                'class_id' => 'required|integer',
                'level_id' => 'required|integer',
                'type' => 'required|string|in:offline,online',
                'year' => 'required|digits:4',
                'term' => 'required|integer|in:1,2,3',
            ],
        );

        $result = $this->studentPayment->addPayment($cleanedData);

        return $this->respond([
            'success' => true,
            'data' => $result,
        ], HttpStatus::CREATED);
    }

    #[Route(
        '/students/payment/status/{reference}',
        'GET',
        ['auth', 'role:admin', 'role:student']
    )]
    public function checkPaymentStatus(array $vars)
    {
        $cleanedData = $this->validate($vars, [
            'reference' => 'required|string|filled',
        ]);

        return $this->respond([
            'success' => true,
            'data' => $this->studentPayment->checkPaymentStatus($cleanedData['reference']),
        ]);
    }

    #[Route(
        '/students/{student_id:\d+}/payment/history',
        'GET',
        ['auth', 'role:student', 'role:admin']
    )]
    public function getFinancialRecords(array $vars)
    {
        $cleanedData = $this->validate($vars, ['student_id' => 'required|integer']);

        return $this->respond([
            'success' => true,
            'data' => $this->studentPayment->getInvoiceAndTransactionHistory($cleanedData['student_id']),
        ]);
    }
}
