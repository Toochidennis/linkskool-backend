<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\ProgramProfile;

class ProgramProfileService
{
    protected ProgramProfile $programProfileModel;

    public function __construct(\PDO $pdo)
    {
        $this->programProfileModel = new ProgramProfile($pdo);
    }

    public function createProfile(array $data): array
    {
        $payload = [
            'user_id' => $data['user_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'birth_date' => $data['birth_date'],
            'gender' => $data['gender'] ?? 'male',
            'certificate_name' => $data['certificate_name'] ?? null,
        ];

        $id = $this->programProfileModel->insert($payload);

        if ($id) {
            return $this->getProfilesByUserId($data['user_id']);
        }

        return [];
    }

    public function updateProfile(array $data): array
    {
        $payload = [
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'certificate_name' => $data['certificate_name'] ?? null,
            'gender' => $data['gender'] ?? 'male'
        ];

        $success = $this->programProfileModel
            ->where('id', $data['id'])
            ->update(
                array_filter(
                    $payload,
                    fn($value) => $value !== null
                )
            );

        if ($success) {
            return $this->getProfilesByUserId($data['user_id']);
        }

        return [];
    }

    public function getProfilesByUserId(int $userId): array
    {
        return $this->programProfileModel
            ->where('user_id', $userId)
            ->get();
    }

    public function deleteProfile(int $id): bool
    {
        return $this->programProfileModel
            ->where('id', $id)
            ->delete();
    }
}
