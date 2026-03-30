<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Events\EventDispatcher;
use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\DataExtractor;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Controllers\Explore\ExploreBaseController;
use V3\App\Database\DatabaseConnector;
use V3\App\Events\Email\PaymentReceipt;
use V3\App\Services\Explore\BillingService;
use V3\App\Services\Explore\CbtPaymentFulfillmentService;
use V3\App\Services\Explore\CourseCohortEnrollmentService;

#[Group('/public')]
class WebhookController extends ExploreBaseController
{
    private BillingService $billingService;
    private CourseCohortEnrollmentService $courseEnrollment;
    private CbtPaymentFulfillmentService $cbtPaymentFulfillmentService;

    public function __construct()
    {
        $this->post = [];
        $this->pdo = DatabaseConnector::connect();
        $this->billingService = new BillingService($this->pdo);
        $this->courseEnrollment = new CourseCohortEnrollmentService($this->pdo);
        $this->cbtPaymentFulfillmentService = new CbtPaymentFulfillmentService($this->pdo);
    }

    #[Route('/webhooks/paystack', 'POST')]
    public function paystackWebhook(): void
    {
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);

        $signature =
            $headers['x-paystack-signature']
            ?? $_SERVER['http_x_paystack_signature']
            ?? null;

        $rawBody   = DataExtractor::getRawBody();

        $secretKey = getenv('APP_ENV') === 'production'
            ? (string) getenv('PAYSTACK_PROD_SECRET_KEY')
            : (string) getenv('PAYSTACK_DEV_SECRET_KEY');

        $expected = hash_hmac('sha512', $rawBody, $secretKey);

        if (!hash_equals($expected, $signature)) {
            $this->respond([
                'success' => false,
                'message' => 'Invalid webhook signature'
            ], HttpStatus::UNAUTHORIZED);

            return;
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

        if (!in_array($event, ['charge.success', 'transaction.success'], true)) {
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
                if (\in_array(($res['status'] ?? ''), ['success', 'failed'], true) && !empty($res['payment_id'])) {
                    EventDispatcher::dispatch(new PaymentReceipt((int) $res['payment_id']));
                }
                if (($res['status'] ?? '') === 'success') {
                    $fulfillment = $this->cbtPaymentFulfillmentService->handleSuccessfulWebhook([
                        'status' => $res['status'],
                        'method' => $res['method'],
                        'platform' => $res['platform'],
                        'user_id' => $res['user_id'],
                    ]);

                    $res['fulfillment'] = $fulfillment;

                    if (($fulfillment['status'] ?? '') === 'failed') {
                        $res = [
                            'status' => 'failed',
                            'message' => $fulfillment['message'] ?? 'Payment fulfillment failed.',
                            'payment' => $res,
                            'fulfillment' => $fulfillment,
                        ];
                    }
                }
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
