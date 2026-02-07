<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\Device;
use V3\App\Models\Explore\License;
use V3\App\Models\Explore\Plan;

class LicenseService
{
    private License $license;
    private Device $device;
    private Plan $plan;
    private BillingService $billing;

    public function __construct(\PDO $pdo)
    {
        $this->license = new License($pdo);
        $this->device = new Device($pdo);
        $this->plan = new Plan($pdo);
        $this->billing = new BillingService($pdo);
    }

    public function activateDesktop(array $data)
    {
        $userId = (int) $data['user_id'];
        $fingerprint = $data['fingerprint'];

        if (!$this->billing->hasEntitlement($userId, 'desktop')) {
            return [
                'status' => 'payment_required',
                'message' => 'No active subscription for desktop access',
            ];
        }

        $deviceId = $this->device->insert([
            'user_id' => $userId,
            'fingerprint' => $fingerprint,
            'platform' => $data['platform']
        ]);

        $existing = $this->license
            ->where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->where('status', 'active')
            ->where('type', 'desktop')
            ->where('revoked_at', null)
            ->first();

        if ($existing && !$this->isExpired($existing['expires_at'])) {
            return [
                'status' => 'already_active',
                'message' => 'Device already activated'
            ];
        }

        $plan = $this->plan->where('platform', 'desktop')->first();
        $activeCount = $this->license
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where('type', 'desktop')
            ->where('revoked_at', null)
            ->count();

        if ($plan && $plan['max_devices'] !== null && $activeCount >= $plan['max_devices']) {
            return [
                'status' => 'limit_reached',
                'message' => 'Device limit reached for desktop access'
            ];
        }

        $payment = $this->billing->getLatestPaidPayment($userId, 'desktop');

        $expiresAt = $this->computeExpiry('desktop');

        $licenseId = $this->license->insert([
            'user_id' => $userId,
            'device_id' => $deviceId,
            'type' => 'desktop',
            'payment_id' => $payment['id'],
            'expires_at' => $expiresAt,
            'status' => 'active',
        ]);

        return [
            'status' => 'activated',
            'license' => [
                'license_id' => $licenseId,
                'type' => 'desktop',
                'expires_at' => $expiresAt,
                'issued_at' => date('Y-m-d H:i:s'),
                'status' => 'active',
                'device_bound' => true,
            ],
            'policy' => [
                'revalidate_after' => 86400,
                'offline_allowed'  => true,
            ],
            'message' => 'Device activated successfully',
        ];
    }

    public function activateMobile(int $userId)
    {
        //$userId = (int) $data['user_id'];
        //$fingerprint = $data['fingerprint'];

        if (!$this->billing->hasEntitlement($userId, 'mobile')) {
            return [
                'status' => 'payment_required',
                'message' => 'No active subscription for mobile access',
            ];
        }

        // $deviceId = $this->device->insert([
        //     'user_id' => $userId,
        //     'fingerprint' => $fingerprint,
        //     'platform' => $data['platform']
        // ]);

        $existing = $this->license
            ->where('user_id', $userId)
            // ->where('device_id', $deviceId)
            ->where('status', 'active')
            ->where('type', 'mobile')
            ->where('revoked_at', null)
            ->first();

        if ($existing && !$this->isExpired($existing['expires_at'])) {
            return [
                'status' => 'already_active',
                'message' => 'Device already activated'
            ];
        }

        $plan = $this->plan->where('platform', 'mobile')->first();
        $activeCount = $this->license
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where('type', 'mobile')
            ->where('revoked_at', null)
            ->count();

        if ($plan && $plan['max_devices'] !== null && $activeCount >= $plan['max_devices']) {
            return [
                'status' => 'limit_reached',
                'message' => 'Device limit reached for mobile access'
            ];
        }

        $payment = $this->billing->getLatestPaidPayment($userId, 'mobile');

        $expiresAt = $this->computeExpiry('mobile');

        $licenseId = $this->license->insert([
            'user_id' => $userId,
            'type' => 'mobile',
            'payment_id' => $payment['id'],
            'expires_at' => $expiresAt,
            'status' => 'active',
        ]);

        return [
            'active' => 'activated',
            'license' => [
                'license_id' => $licenseId,
                'type' => 'desktop',
                'expires_at' => $expiresAt,
                'issued_at' => date('Y-m-d H:i:s'),
                'status' => 'active',
                'device_bound' => false,
            ],
            'policy' => [
                'revalidate_after' => 86400,
                'offline_allowed'  => true,
            ],
            'message' => 'Device activated successfully',
        ];
    }

    private function isExpired(?string $expiresAt): bool
    {
        if ($expiresAt === null) {
            return false;
        }

        return strtotime($expiresAt) < time();
    }

    private function computeExpiry(string $platform): ?string
    {
        $plan = $this->plan->where('platform', $platform)->first();

        if ($plan && $plan['duration_days'] !== null) {
            return date('Y-m-d H:i:s', strtotime('+' . $plan['duration_days'] . ' days'));
        }

        return null;
    }

    public function revokeLicense(int $licenseId): bool
    {
        $license = $this->license
            ->where('id', $licenseId)
            ->first();

        if (!$license) {
            throw new \RuntimeException('License not found');
        }

        return $this->license
            ->where('id', $licenseId)
            ->update([
                'status' => 'revoked',
                'revoked_at' => date('Y-m-d H:i:s'),
            ]) > 0;
    }

    public function checkDesktopStatus(int $userId, string $fingerprint): array
    {
        $device = $this->device
            ->where('user_id', $userId)
            ->where('fingerprint', $fingerprint)
            ->first();

        if (!$device) {
            return [
                'active' => false,
                'reason' => 'device_not_registered',
            ];
        }

        $license = $this->license
            ->where('user_id', $userId)
            ->where('device_id', $device['id'])
            ->where('type', 'desktop')
            ->where('status', 'active')
            ->where('revoked_at', null)
            ->first();

        if (!$license) {
            return [
                'active' => false,
                'reason' => 'no_license_for_device',
            ];
        }

        if ($this->isExpired($license['expires_at'])) {
            return [
                'active' => false,
                'reason' => 'expired',
                'expires_at' => $license['expires_at'],
            ];
        }

        return [
            'active' => true,
            'license_id' => (int) $license['id'],
            'expires_at' => $license['expires_at'],
            'device_bound' => true,
        ];
    }

    public function checkMobileStatus(int $userId): array
    {
        $license = $this->license
            ->where('user_id', $userId)
            ->where('type', 'mobile')
            ->where('status', 'active')
            ->where('revoked_at', null)
            ->first();

        if (!$license || $this->isExpired($license['expires_at'])) {
            return [
                'active' => false,
            ];
        }

        return [
            'active' => true,
            'license_id' => (int) $license['id'],
            'expires_at' => $license['expires_at'],
        ];
    }

    public function getDesktopPlans()
    {
        return $this->plan
            ->where('platform', 'desktop')
            ->get();
    }

    public function getMobilePlans()
    {
        return $this->plan
            ->where('platform', 'mobile')
            ->get();
    }
}
