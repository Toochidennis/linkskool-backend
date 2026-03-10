<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\Faqs;

class FaqsService
{
    private Faqs $faqs;

    public function __construct(\PDO $pdo)
    {
        $this->faqs = new Faqs($pdo);
    }
}
