<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
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

    #[Route('', 'POST', ['api'])]
    public function storeParticipantData(): void
    {
        $validatedData = $this->validate(
            $this->getRequestData(),
            [
                'username' => 'required|string',
                'user_id' => 'required|integer',
                'challenge_id' => 'required|integer',
            ]
        );

        $result = $this->participants->storeParticipantData($validatedData);

        if (!$result) {
            $this->respondError('Failed to store participant data', HttpStatus::BAD_REQUEST);
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Participant data stored successfully',
                'data' => ['id' => $result],
            ]
        );
    }
}
