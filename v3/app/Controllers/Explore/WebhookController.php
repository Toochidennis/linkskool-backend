<?php

namespace V3\App\Controllers;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\DataExtractor;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\Explore\ExploreBaseController;
use V3\App\Services\Explore\BillingService;
use V3\App\Services\Explore\CourseCohortEnrollmentService;

#[Group('/public')]
class WebhookController extends ExploreBaseController
{
    private BillingService $billingService;
    private CourseCohortEnrollmentService $courseEnrollment;

    public function __construct()
    {
        parent::__construct();

        $this->billingService = new BillingService($this->pdo);
        $this->courseEnrollment = new CourseCohortEnrollmentService($this->pdo);
    }

    #[Route('/webhook/paystack', 'POST')]
    public function paystackWebhook(): void
    {
        $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? null;
        $rawBody   = DataExtractor::getRawBody();

        $expected = hash_hmac('sha512', $rawBody, getenv('PAYSTACK_PROD_SECRET_KEY'));

        if (!hash_equals($expected, $signature)) {
            $this->respond([
                'success' => false,
                'message' => 'Invalid webhook signature'
            ], HttpStatus::UNAUTHORIZED);
        }

        $payload = json_decode($rawBody, true);

        if (!$payload) {
            $this->respond([
                'success' => false,
                'message' => 'Invalid webhook payload'
            ], HttpStatus::BAD_REQUEST);
            return;
        }

        $event = $payload['event'] ?? null;
        $data  = $payload['data'] ?? [];

        if ($event !== 'charge.success') {
            $this->respond([
                'success' => true,
                'message' => 'Event ignored'
            ], HttpStatus::OK);
            return;
        }

        $metadata = $data['metadata'] ?? [];
        $type = $metadata['payment_type'] ?? null;

        switch ($type) {
            case 'course':
                $res = $this->courseEnrollment
                    ->handlePaymentWebhook($data['reference']);
                break;
            case 'cbt':
                $res = $this->billingService
                    ->handlePaystackWebhook($payload, $signature, $rawBody);
                break;
            default:
                $res = [
                    'status' => 'failed',
                    'message' => 'Unknown payment type'
                ];
        }

        if (($res['status'] ?? '') === 'failed') {
            $this->respond([
                'success' => false,
                'message' => $res['message'] ?? 'Webhook processing failed.',
                'data' => $res
            ], HttpStatus::BAD_REQUEST);

            return;
        }

        $this->respond([
            'success' => true,
            'message' => $res['message'] ?? 'Webhook processed.',
            'data' => $res
        ], HttpStatus::OK);
    }
}
