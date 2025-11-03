<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Services\Explore\AdmissionService;

#[Group('/public')]
class AdmissionController
{
    private AdmissionService $admissionService;

    public function __construct()
    {
        $this->admissionService = new AdmissionService();
    }

    #[Route('/admissions', 'GET')]
    public function getAdmissions(): array
    {
        return $this->admissionService->getAdmissionInfo();
    }
}
