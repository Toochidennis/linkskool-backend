<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Services\Explore\ChallengeParticipantsService;

#[Group('/public/cbt/challenge/participants')]
class ChallengeParticipantsController extends ExploreBaseController
{
    private ChallengeParticipantsService $participants;

    public function __construct()
    {
        parent::__construct();
        $this->participants = new ChallengeParticipantsService($this->pdo);
    }
}
