<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Events\EventDispatcher;
use V3\App\Events\Email\LicenseActivated;
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

        $deviceId = 0;

        $existingDevice = $this->device
            ->where('user_id', $userId)
            ->where('fingerprint', $fingerprint)
            ->first();

        if (empty($existingDevice)) {
            $deviceId = $this->device->insert([
                'user_id' => $userId,
                'fingerprint' => $fingerprint,
                'platform' => 'desktop'
            ]);
        } else {
            $deviceId = $existingDevice['id'];
        }

        if ($deviceId <= 0) {
            throw new \RuntimeException('Error creating device');
        }

        $existing = $this->license
            ->where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->where('status', 'active')
            ->where('platform', 'desktop')
            ->whereNull('revoked_at')
            ->first();

        if ($existing && !$this->isExpired($existing['expires_at'])) {
            return [
                'status' => 'already_active',
                'message' => 'Device already activated'
            ];
        }

        if ($existing && $this->isExpired($existing['expires_at'])) {
            $this->license
                ->where('id', $existing['id'])
                ->update([
                    'status' => 'expired',
                ]);

            return [
                'status' => 'expired',
                'message' => 'Previous license expired, please reactivate',
            ];
        }

        $plan = $this->plan->where('platform', 'desktop')->first();
        $activeCount = $this->license
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where('platform', 'desktop')
            ->whereNull('revoked_at')
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
            'platform' => 'desktop',
            'type' => 'payment',
            'payment_id' => $payment['id'],
            'expires_at' => $expiresAt,
            'status' => 'active',
        ]);

        EventDispatcher::dispatch(new LicenseActivated((int) $licenseId));

        return [
            'status' => 'activated',
            'license' => [
                'license_id' => $licenseId,
                'platform' => 'desktop',
                'type' => 'payment',
                'expires_at' => $expiresAt,
                'issued_at' => date('Y-m-d H:i:s'),
                'status' => 'active',
                'device_bound' => true,
            ],
            'policy' => [
                'revalidate_after_days' => 2,
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
            ->where('platform', 'mobile')
            ->whereNull('revoked_at')
            ->first();

        if ($existing && !$this->isExpired($existing['expires_at'])) {
            return [
                'status' => 'already_active',
                'message' => 'Device already activated'
            ];
        }

        if ($existing && $this->isExpired($existing['expires_at'])) {
            $this->license
                ->where('id', $existing['id'])
                ->update([
                    'status' => 'expired',
                ]);

            return [
                'status' => 'expired',
                'message' => 'Previous license expired, please reactivate',
            ];
        }

        $plan = $this->plan->where('platform', 'mobile')->first();
        $activeCount = $this->license
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where('platform', 'mobile')
            ->whereNull('revoked_at')
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
            'platform' => 'mobile',
            'type' => 'payment',
            'payment_id' => $payment['id'],
            'expires_at' => $expiresAt,
            'status' => 'active',
        ]);

        if (!$licenseId) {
            throw new \RuntimeException('Error creating license');
        }

        EventDispatcher::dispatch(new LicenseActivated((int) $licenseId));

        return [
            'status' => 'activated',
            'license' => [
                'license_id' => $licenseId,
                'platform' => 'mobile',
                'type' => 'payment',
                'expires_at' => $expiresAt,
                'issued_at' => date('Y-m-d H:i:s'),
                'status' => 'active',
                'device_bound' => false,
            ],
            'policy' => [
                'revalidate_after_days' => 2,
                'offline_allowed'  => false,
            ],
            'message' => 'Device activated successfully',
        ];
    }

    public function startTrial(int $userId, string $platform): array
    {
        $existingTrial = $this->license
            ->where('user_id', $userId)
            ->where('type', 'trial')
            ->where('platform', $platform)
            ->first();

        if ($existingTrial) {
            if ($this->isExpired($existingTrial['expires_at'])) {
                return [
                    'status' => 'expired',
                    'message' => 'Trial has expired',
                    'expires_at' => $existingTrial['expires_at'],
                ];
            }

            return [
                'status' => 'active',
                'message' => 'Trial already active',
                'expires_at' => $existingTrial['expires_at'],
            ];
        }

        $plan = $this->plan
            ->where('platform', $platform)
            ->first();

        if (!$plan || empty($plan['free_trial_days'])) {
            return [
                'status' => 'not_available',
                'message' => 'No trial available for this platform',
            ];
        }

        $expiresAt = date(
            'Y-m-d H:i:s',
            strtotime('+' . (int) $plan['free_trial_days'] . ' days')
        );

        $this->license->insert([
            'user_id' => $userId,
            'type' => 'trial',
            'platform' => $platform,
            'payment_id' => null,
            'expires_at' => $expiresAt,
            'status' => 'active',
        ]);

        return [
            'status' => 'started_trial',
            'message' => 'Trial started',
            'license' => [
                'type' => 'trial',
                'platform' => $platform,
                'expires_at' => $expiresAt,
                'issued_at' => date('Y-m-d H:i:s'),
                'status' => 'active',
            ],
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

    public function getActivationReceiptDetails(int $licenseId): array
    {
        $rows = $this->license->rawQuery(
            "
            SELECT
                l.id,
                l.user_id,
                l.platform,
                l.type,
                l.payment_id,
                l.expires_at,
                l.status,
                l.created_at AS issued_at,
                p.reference,
                p.first_name,
                p.last_name,
                u.email,
                pl.name AS plan_name,
                pl.duration_days
            FROM licenses l
            LEFT JOIN payments p ON p.id = l.payment_id
            LEFT JOIN cbt_users u ON u.id = l.user_id
            LEFT JOIN plans pl ON pl.id = p.plan_id
            WHERE l.id = :license_id
            LIMIT 1
            ",
            [
                'license_id' => $licenseId,
            ]
        );

        return $rows[0] ?? [];
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

    public function expireElapsedLicenses(): int
    {
        $cutoff = date('Y-m-d H:i:s');

        return $this->license->rawExecute(
            "
            UPDATE licenses
            SET status = :expired_status
            WHERE status = :active_status
              AND revoked_at IS NULL
              AND expires_at IS NOT NULL
              AND expires_at <= :cutoff
            ",
            [
                'expired_status' => 'expired',
                'active_status' => 'active',
                'cutoff' => $cutoff,
            ]
        );
    }

    public function checkDesktopStatus(int $userId, string $fingerprint): array
    {
        $device = $this->device
            ->where('user_id', $userId)
            ->where('fingerprint', $fingerprint)
            ->first();

        if ($device) {
            $license = $this->license
                ->where('user_id', $userId)
                ->where('device_id', $device['id'])
                ->where('platform', 'desktop')
                ->where('status', 'active')
                ->whereNull('revoked_at')
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
                'source' => $license['type'],
            ];
        }

        $trial = $this->license
            ->where('user_id', $userId)
            ->where('type', 'trial')
            ->where('platform', 'desktop')
            ->where('status', 'active')
            ->first();

        if ($trial && !$this->isExpired($trial['expires_at'])) {
            return [
                'active' => true,
                'source' => $trial['type'],
                'device_bound' => false,
                'license_id' => (int) $trial['id'],
                'expires_at' => $trial['expires_at'],
            ];
        }

        return [
            'active' => false,
            'reason' => 'no_valid_license',
        ];
    }

    public function checkMobileStatus(int $userId): array
    {
        $license = $this->license
            ->where('user_id', $userId)
            ->where('platform', 'mobile')
            ->where('type', 'payment')
            ->whereNull('revoked_at')
            ->first();

        if ($license && $this->isExpired($license['expires_at'])) {
            return [
                'active' => false,
                'reason' => 'expired',
                'expires_at' => $license['expires_at'],
            ];
        }

        if ($license && !$this->isExpired($license['expires_at'])) {
            return [
                'active' => true,
                'license_id' => (int) $license['id'],
                'expires_at' => $license['expires_at'],
                'device_bound' => false,
                'source' => $license['type'],
            ];
        }

        $trial = $this->license
            ->where('user_id', $userId)
            ->where('type', 'trial')
            ->where('platform', 'mobile')
            ->whereNull('revoked_at')
            ->first();

        if ($trial && !$this->isExpired($trial['expires_at'])) {
            return [
                'active' => true,
                'source' => $trial['type'],
                'license_id' => (int) $trial['id'],
                'expires_at' => $trial['expires_at'],
            ];
        }

        if ($trial && $this->isExpired($trial['expires_at'])) {
            return [
                'active' => false,
                'reason' => 'trial_expired',
                'expires_at' => $trial['expires_at'],
            ];
        }

        return [
            'active' => false,
            'reason' => 'no_valid_license',
        ];
    }

    public function getDesktopPlans()
    {
        $rows = $this->plan
            ->where('platform', 'desktop')
            ->orderBy('created_at', 'desc')
            ->get();

        if (empty($rows)) {
            return [];
        }

        return $this->computePrice($rows);
    }

    public function getMobilePlans()
    {
        $rows = $this->plan
            ->where('platform', 'mobile')
            ->orderBy('created_at', 'desc')
            ->get();

        if (empty($rows)) {
            return [];
        }

        return $this->computePrice($rows);
    }

    private function computePrice(array $plans): array
    {
        $formatted = [];
        foreach ($plans as $p) {
            $price = (int) $p['price'];
            $discountPercent = $p['discount_percent'] ?? null;

            if ($discountPercent !== null) {
                $discount = ($price * $discountPercent) / 100;
                $price -= (int) $discount;
            }

            $formatted[] = [
                'id' => $p['id'],
                'name' => $p['name'],
                'discount_percent' => $p['discount_percent'],
                'price' => $p['price'],
                'final_price' => $price,
                'free_trial_days' => $p['free_trial_days'],
                'currency' => 'NGN',
                'features' => $p['features'] ? json_decode($p['features'], true) : [],
            ];
        }
        return $formatted;
    }
}
