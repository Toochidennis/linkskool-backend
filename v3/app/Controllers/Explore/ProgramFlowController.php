<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\ProgramFlowService;

#[Group('/public')]
class ProgramFlowController extends ExploreBaseController
{
    private ProgramFlowService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ProgramFlowService($this->pdo);
    }


    // #[Route('/learn/programs/{program_id}/cohorts', 'GET', ['api', 'auth'])]
    // public function getProgramCohorts(array $vars)
    // {
    //     $validatedData = $this->validate(
    //         $vars,
    //         [
    //             'program_id' => 'required|integer',
    //         ]
    //     );

    //     $cohorts = $this->service
    //         ->getCohorts(
    //             (int)$validatedData['program_id']
    //         );

    //     $this->respond(
    //         [
    //             'success' => true,
    //             'data' => $cohorts
    //         ],
    //         HttpStatus::OK
    //     );
    // }

    // public function upsert(array $vars)
    // {
    //     $validatedData = $this->validate(
    //         [...$this->getRequestData(), ...$vars],
    //         [
    //         ],
    //     );
    // }
}
