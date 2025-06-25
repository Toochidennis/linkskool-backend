<?php

namespace V3\App\Services\Portal\ELearning;

use V3\App\Models\Portal\ELearning\Content;

class MaterialService
{
    private Content $content;

    public function __construct(\PDO $pdo)
    {
        $this->content = new Content($pdo);
    }

    public function addMaterial(array $data)
    {
        $payload = [];
    }
}
