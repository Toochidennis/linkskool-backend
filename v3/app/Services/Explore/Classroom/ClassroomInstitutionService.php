<?php

namespace V3\App\Services\Explore\Classroom;

use V3\App\Common\Utilities\AssetUrl;
use V3\App\Common\Utilities\Str;
use V3\App\Common\Utilities\TemplateRenderer;
use V3\App\Common\Utilities\Uuid;
use V3\App\Models\Explore\Classroom\ClassroomInstitution;
use V3\App\Services\Common\MailService;
use V3\App\Services\Explore\StorageService;

class ClassroomInstitutionService
{
    private ClassroomInstitution $model;
    private ClassroomAccessCodeService $accessCodeService;
    private MailService $mailService;

    public function __construct(\PDO $pdo)
    {
        $this->model = new ClassroomInstitution($pdo);
        $this->accessCodeService = new ClassroomAccessCodeService($pdo);
        $this->mailService = new MailService($pdo);
    }

    public function createClassroomInstitution(array $data): array|bool
    {
        $institutionSlug = Str::slug($data['name']);

        if (isset($_FILES['logo'])) {
            $data['logo_url'] = StorageService::saveFile(
                $_FILES['logo'],
                "explore/classrooms/institutions/{$institutionSlug}/logo"
            );
        }

        if (isset($_FILES['banner'])) {
            $data['banner_url'] = StorageService::saveFile(
                $_FILES['banner'],
                "explore/classrooms/institutions/{$institutionSlug}/banner"
            );
        }

        $this->model->beginTransaction();
        try {
            $institutionId = $this->model->insert([
                'id' => $data['id'],
                'slug' => Uuid::v4(),
                'name' => $data['name'],
                'type' => $data['type'],
                'user_id' => $data['user_id'],
                'logo_url' => $data['logo_url'] ?? null,
                'banner_url' => $data['banner_url'] ?? null,
                'website' => $data['website'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'join_code' => $this->generateJoinCode($data['name']),
            ]);

            if (!$institutionId) {
                throw new \RuntimeException("Failed to create institution");
            }

            $accessCodeResult = $this->accessCodeService->assignCode(
                $data['access_code'],
                $data['id']
            );

            $this->model->commit();

            $institution = $this->getInstitutionByUserId($data['user_id']);
            $this->sendWelcomeEmail($data['email'], $data['name'], $institution['join_code'] ?? '');

            return $institution;
        } catch (\Exception $e) {
            $this->model->rollBack();
            return false;
        }
    }

    public function savePassword(string $institutionId, string $newPassword): array
    {
        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $updated =  $this->model
            ->where('id', $institutionId)
            ->update([
                'password_hash' => $passwordHash,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        if (!$updated) {
            return [
                'success' => false,
                'message' => 'Failed to update password. Please try again.'
            ];
        }

        $institution =  $this->model
            ->where('id', $institutionId)
            ->first();

        return [
            'success' => true,
            'message' => 'Password updated successfully.',
            'institution' => $institution
        ];
    }

    public function getInstitutionByUserId(int $userId): array
    {
        $row = $this->model->where('user_id', $userId)->first();

        if (!empty($row)) {
            $row['logo_url']   = AssetUrl::fromAppUrl($row['logo_url'] ?? null);
            $row['banner_url'] = AssetUrl::fromAppUrl($row['banner_url'] ?? null);
        }

        return $row;
    }

    private function sendWelcomeEmail(string $email, string $institutionName, string $joinCode): void
    {
        try {
            $v3root = realpath(__DIR__ . '/../../../');
            if ($v3root === false) {
                return;
            }

            $html = TemplateRenderer::render(
                $v3root . '/Templates/emails/classroom_institution_welcome.php',
                [
                    'institution_name' => $institutionName,
                    'join_code'        => $joinCode,
                ]
            );

            $this->mailService->send(
                $email,
                "Your Classroom is Ready — {$institutionName}",
                $html
            );
        } catch (\Throwable) {
            return;
        }
    }

    private function generateJoinCode(string $name): string
    {
        $prefix   = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3));
        $segment1 = strtoupper(substr(bin2hex(random_bytes(4)), 0, 4));
        $segment2 = strtoupper(substr(bin2hex(random_bytes(4)), 0, 4));

        return "{$prefix}-{$segment1}-{$segment2}";
    }
}
