<?php

namespace V3\App\Services\Explore;

class CbtPaymentFulfillmentService
{
    private LicenseService $licenseService;

    public function __construct(\PDO $pdo)
    {
        $this->licenseService = new LicenseService($pdo);
    }

    public function handleSuccessfulWebhook(array $paymentData): array
    {
        $method = (string) ($paymentData['method']);
        $platform = (string) ($paymentData['platform']);
        $userId = (int) ($paymentData['user_id']);


        if ($method !== 'online' || $platform !== 'mobile') {
            return [
                'status' => 'ignored',
                'message' => 'No mobile activation required for this CBT payment.',
            ];
        }

        return $this->licenseService->activateMobile($userId);
    }
}
