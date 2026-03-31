<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\ChallengeService;

#[Group('/public/cbt/challenges')]
class ChallengeController extends ExploreBaseController
{
    private ChallengeService $challengeService;

    public function __construct()
    {
        parent::__construct();
        $this->challengeService = new ChallengeService($this->pdo);
    }

    /**
     * storeChallenge
     */
    #[Route('', 'POST', ['api'])]
    public function storeChallenge(): void
    {
        $filters = $this->validate(
            $this->post,
            [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'duration' => 'required|integer|min:0',
                'exam_type_id' => 'required|integer|min:1',
                'status' => 'required|string|in:published,draft,archived',
                'author_id' => 'required|integer|min:1',
                'author_name' => 'required|string|max:255',
                'items' => 'required|array|min:1',
                'items.*.course_id' => 'required|integer|min:1',
                'items.*.course_name' => 'required|string|filled',
                'items.*.question_count' => 'required|integer|min:1',
                'items.*.years' => 'required|array',
                'items.*.years.*' => 'required|integer',
            ]
        );

        $response = $this->challengeService->createChallenge($filters);

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

    #[Route('/{challenge_id:\d+}', 'PUT', ['api'])]
    public function updateChallenge(array $vars): void
    {
        $filters = $this->validate(
            [...$this->post, ...$vars],
            [
                'challenge_id' => 'required|integer|min:1',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'duration' => 'required|integer|min:0',
                'exam_type_id' => 'required|integer|min:1',
                'status' => 'required|string|in:published,draft,archived',
                'author_id' => 'required|integer|min:1',
                'author_name' => 'required|string|max:255',
                'items' => 'required|array|min:1',
                'items.*.course_id' => 'required|integer|min:1',
                'items.*.course_name' => 'required|string|filled',
                'items.*.question_count' => 'required|integer|min:1',
                'items.*.years' => 'required|array',
                'items.*.years.*' => 'required|integer',
            ]
        );

        $response = $this->challengeService->updateChallenge($filters);

        if (!$response) {
            $this->respondError(
                "Failed to update challenge. Please ensure that the selected exams have sufficient questions.",
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Challenge updated successfully.'
            ],
            HttpStatus::OK
        );
    }

    #[Route('/status/{challenge_id:\d+}', 'PUT', ['api'])]
    public function updateChallengeStatus(array $vars): void
    {
        $filters = $this->validate(
            [...$vars, ...$this->post],
            [
                'challenge_id' => 'required|integer|min:1',
                'status' => 'required|string|in:published,draft,archived',
            ]
        );

        $response = $this->challengeService->updateStatus(
            $filters['challenge_id'],
            $filters['status']
        );

        if (!$response) {
            $this->respondError(
                "Failed to update challenge status.",
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Challenge status updated successfully.'
            ],
            HttpStatus::OK
        );
    }

    #[Route('', 'GET', ['api'])]
    public function getChallenges(array $vars): void
    {
        $filters = $this->validate(
            $vars,
            [
                'author_id' => 'required|integer|min:1',
                'exam_type_id' => 'required|integer|min:1',
            ]
        );

        $challenges = $this->challengeService->getChallenges($filters);
        $message = empty($challenges) ? 'No challenges found.' : 'Challenges retrieved successfully.';

        $this->respond(
            [
                'success' => true,
                'message' => $message,
                'data' => $challenges
            ]
        );
    }

    #[Route('/questions', 'GET', ['api'])]
    public function getChallengeQuestions(array $vars): void
    {
        $filters = $this->validate(
            $vars,
            [
                'challenge_id' => 'required|integer|min:1',
                'course_id' => 'required|integer|min:1',
            ]
        );

        $questions = $this->challengeService->getChallengeQuestions($filters);
        $message = empty($questions) ?
            'No questions found for the specified challenge and subject.'
            : 'Questions retrieved successfully.';

        $this->respond(
            [
                'success' => true,
                'message' => $message,
                'data' => $questions
            ]
        );
    }

    #[Route('/{challenge_id:\d+}', 'DELETE', ['api'])]
    public function deleteChallenge(array $vars): void
    {
        $filters = $this->validate(
            [...$vars, ...$this->post],
            [
                'challenge_id' => 'required|integer|min:1',
                'author_id' => 'required|integer|min:1',
            ]
        );

        $response = $this->challengeService->deleteChallenge(
            $filters['challenge_id'],
            $filters['author_id']
        );

        if (!$response) {
            $this->respondError(
                "Failed to delete challenge.",
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond(
            [
                'success' => true,
                'message' => 'Challenge deleted successfully.'
            ],
            HttpStatus::OK
        );
    }
}
