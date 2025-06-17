<?php

namespace V3\App\Services\Portal\Academics;

use PDO;
use V3\App\Models\Portal\Level;

class LevelService
{
    private Level $level;

    public function __construct(PDO $pdo)
    {
        $this->level = new Level($pdo);
    }

    public function addLevel(array $data)
    {
        // TODO
    }
}
