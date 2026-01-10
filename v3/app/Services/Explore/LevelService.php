<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\Level;

class LevelService
{
    private Level $level;

    public function __construct(\PDO $pdo)
    {
        $this->level = new Level($pdo);
    }

    public function addLevel(array $data): bool|int
    {
        return $this->level->insert($data);
    }

    public function updateLevel(array $data): bool
    {
        $id = $data['id'];
        unset($data['id']);
        return $this->level
            ->where('id', '=', $id)
            ->update($data);
    }

    public function deleteLevel(int $id): bool
    {
        return $this->level
            ->where('id', '=', $id)
            ->delete();
    }

    public function getLevels(): array
    {
        return $this->level
            ->orderBy('rank', 'ASC')
            ->get();
    }
}
