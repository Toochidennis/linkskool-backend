<?php

namespace V3\App\Services\Portal\Academics;

use PDO;
use V3\App\Common\Enums\SchoolType;
use V3\App\Models\Portal\Academics\Level;

class LevelService
{
    private Level $level;

    private array $schoolType = [
        'primary' => SchoolType::PRIMARY->value,
        'secondary' => SchoolType::SECONDARY->value,
        'nursery' => SchoolType::NURSERY->value
    ];

    public function __construct(PDO $pdo)
    {
        $this->level = new Level($pdo);
    }

    public function addLevel(array $data): bool|int
    {
        $payload = [
            'level_name' => $data['level_name'],
            'school_type' => $this->schoolType[$data['school_type']],
            'result_template' => $data['result_template'] ?? '',
            'rank' => $data['rank'] ?? 0,
            'admit' => 0
        ];

        return $this->level->insert($payload);
    }

    public function updateLevel(array $data): bool|int
    {
        $payload = [
            'level_name' => $data['level_name'],
            'school_type' => $this->schoolType[$data['school_type']],
            'result_template' => $data['result_template'] ?? '',
            'rank' => $data['rank'] ?? 0,
        ];

        return $this->level
            ->where('id', $data['id'])
            ->update($payload);
    }

    public function fetchLevels(): array
    {
        return $this->level->orderBy('level_name')->get();
    }

    public function deleteLevel(int $id): bool|int
    {
        return $this->level
            ->where('id', $id)
            ->delete();
    }
}
