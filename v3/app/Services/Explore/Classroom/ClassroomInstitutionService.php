<?php

namespace V3\App\Services\Explore\Classroom;

use V3\App\Common\Utilities\Str;
use V3\App\Common\Utilities\Uuid;
use V3\App\Models\Explore\Classroom\ClassroomInstitution;
use V3\App\Services\Explore\StorageService;

class ClassroomInstitutionService
{
    private ClassroomInstitution $model;
    private ClassroomAccessCodeService $accessCodeService;

    public function __construct(\PDO $pdo)
    {
        $this->model = new ClassroomInstitution($pdo);
        $this->accessCodeService = new ClassroomAccessCodeService($pdo);
    }

    public function createClassroomInstitution(array $data): bool
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
            ]);

            if (!$institutionId) {
                throw new \RuntimeException("Failed to create institution");
            }

            $accessCodeResult = $this->accessCodeService->assignCode(
                $data['access_code'],
                $institutionId
            );

            $this->model->commit();
            return $accessCodeResult;
        } catch (\Exception $e) {
            $this->model->rollBack();
            return false;
        }
    }

    public function getInstitutionByUserId(int $userId): array
    {
        return $this->model->where('user_id', $userId)->first();
    }
}
