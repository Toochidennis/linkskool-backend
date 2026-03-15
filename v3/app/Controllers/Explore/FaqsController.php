<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\FaqsService;

#[Group('/public')]
class FaqsController extends ExploreBaseController
{
    private FaqsService $faqsService;

    public function __construct()
    {
        parent::__construct();
        $this->faqsService = new FaqsService($this->pdo);
    }

    #[Route('/faqs', 'POST', ['api', 'auth'])]
    public function addFaq(): void
    {
        $data = $this->validate($this->getRequestData(), [
            'author_name' => 'required|string|filled',
            'author_id' => 'required|integer',
            'question' => 'required|string|filled',
            'answer' => 'required|string|filled',
        ]);

        $faqId = $this->faqsService->addFaq($data);

        if ($faqId <= 0) {
            $this->respondError(
                'Failed to create FAQ.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'FAQ created successfully.',
            'faq_id' => $faqId,
        ], HttpStatus::CREATED);
    }

    #[Route('/faqs/{id:\d+}', 'PUT', ['api', 'auth'])]
    public function updateFaq(array $vars): void
    {
        $data = $this->validate(
            array_merge($this->getRequestData(), $vars),
            [
                'id' => 'required|integer',
                'author_name' => 'required|string|filled',
                'author_id' => 'required|integer',
                'question' => 'required|string|filled',
                'answer' => 'required|string|filled',
            ]
        );

        $updated = $this->faqsService->updateFaq((int) $data['id'], $data);

        if (!$updated) {
            $this->respondError(
                'Failed to update FAQ.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'FAQ updated successfully.',
        ]);
    }

    #[Route('/faqs', 'GET', ['api'])]
    public function getAllFaqs(): void
    {
        $this->respond([
            'success' => true,
            'data' => $this->faqsService->getAllFaqs(),
        ]);
    }

    #[Route('/faqs/{id:\d+}', 'DELETE', ['api', 'auth'])]
    public function deleteFaq(array $vars): void
    {
        $data = $this->validate($vars, [
            'id' => 'required|integer',
        ]);

        $deleted = $this->faqsService->deleteFaq((int) $data['id']);

        if (!$deleted) {
            $this->respondError(
                'Failed to delete FAQ.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'FAQ deleted successfully.',
        ]);
    }
}
