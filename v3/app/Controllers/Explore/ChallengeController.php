<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\ChallengeService;

#[Group('/public/cbt/challenge')]
class ChallengeController extends ExploreBaseController
{
    private ChallengeService $challengeService;

    public function __construct()
    {
        parent::__construct();
        $this->challengeService = new ChallengeService($this->pdo);
    }

    #[Route('', 'POST', ['api'])]
    public function storeChallenge(): void
    {
        $data = $this->validate(
            $this->post,
            [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'time_limit' => 'required|integer|min:0',
                'count_per_question' => 'required|integer|min:1',
                'exam_ids' => 'required|array|min:1',
                'exam_ids.*' => 'required|integer|min:1',
                'exam_type_id' => 'required|integer|min:1',
                'status' => 'required|string|in:published,draft',
                'author_id' => 'required|integer|min:1',
                'author_name' => 'required|string|max:255',
                'score' => 'required|integer|min:0',
            ]
        );

        $response = $this->challengeService->createChallenge($data);

        if (!$response) {
            $this->respondError(
                "Failed to create challenge. Please ensure that the selected exams have sufficient questions.",
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Challenge created successfully.'
            ],
            HttpStatus::CREATED
        );
    }

    public function getChallenges(array $vars): void
    {
        $filters = $this->validate(
            $vars,
            [
                'author_id' => 'required|integer|min:1',
            ]
        );

        $challenges = $this->challengeService->getChallenges($filters['author_id']);
        $message = empty($challenges) ? 'No challenges found.' : 'Challenges retrieved successfully.';

        $this->respond(
            [
                'success' => true,
                'message' => $message,
                'data' => $challenges
            ]
        );
    }

    public function getChallengeQuestions(): void
    {
        $filters = $this->validate(
            $this->getRequestData(),
            [
                'challenge_id' => 'required|integer|min:1',
                'exam_id' => 'required|integer|min:1',
            ]
        );

        $questions = $this->challengeService->getChallengeQuestions($filters);
        $message = empty($questions) ?
            'No questions found for the specified challenge and exam.'
            : 'Questions retrieved successfully.';

        $this->respond(
            [
                'success' => true,
                'message' => $message,
                'data' => $questions
            ]
        );
    }
}
