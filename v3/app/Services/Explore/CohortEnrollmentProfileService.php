<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\CohortEnrollmentProfile;

class CohortEnrollmentProfileService
{
    protected CohortEnrollmentProfile $enrollmentProfileModel;

    public function __construct(\PDO $pdo)
    {
        $this->enrollmentProfileModel = new CohortEnrollmentProfile($pdo);
    }

    public function createProfile(array $data): array
    {
        $payload = [
            'user_id' => $data['user_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'birth_year' => $data['birth_year'],
            'phone' => $data['phone'],
        ];

        $id = $this->enrollmentProfileModel->insert($payload);

        if ($id) {
            return $this->getProfilesByUserId($id);
        }

        return [];
    }

    public function updateProfile(array $data): bool
    {
        $payload = [
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'birth_year' => $data['birth_year'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'certificate_name' => $data['certificate_name'] ?? null,
        ];

        return $this->enrollmentProfileModel
            ->where('id', $data['id'])
            ->update(
                array_filter(
                    $payload,
                    fn($value) => $value !== null
                )
            );
    }

    public function getProfilesByUserId(int $userId): array
    {
        return $this->enrollmentProfileModel
            ->where('user_id', $userId)
            ->get();
    }

    public function deleteProfile(int $id): bool
    {
        return $this->enrollmentProfileModel
            ->where('id', $id)
            ->delete();
    }
}
