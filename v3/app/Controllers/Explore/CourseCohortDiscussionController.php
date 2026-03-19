<?php

namespace V3\App\Controllers\Explore;

use V3\App\Common\Routing\Group;
use V3\App\Common\Routing\Route;
use V3\App\Common\Utilities\HttpStatus;
use V3\App\Services\Explore\CourseCohortDiscussionService;

#[Group('/public/learning')]
class CourseCohortDiscussionController extends ExploreBaseController
{
    private CourseCohortDiscussionService $discussionService;

    public function __construct()
    {
        parent::__construct();
        $this->discussionService = new CourseCohortDiscussionService($this->pdo);
    }

    #[Route('/cohorts/{cohort_id}/discussions', 'POST', ['api'])]
    public function createDiscussion(array $vars)
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'cohort_id' => 'required|integer',
                'program_id' => 'required|integer',
                'course_id' => 'required|integer',
                'body' => 'required|string',
                'author_id' => 'required|integer',
                'is_locked' => 'nullable|boolean',
                'is_pinned' => 'nullable|boolean',
                'files' => 'nullable|array|max:3',
                'files.*.file_name' => 'required_with:files|string',
                'files.*.old_file_name' => 'nullable|string',
                'files.*.file' => 'required_with:files|string',
                'files.*.type' => 'required_with:files|string|in:image',
            ]
        );

        $discussionId = $this->discussionService->createDiscussion($validated);

        if (!$discussionId) {
            $this->respondError(
                'Failed to create discussion.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'Discussion created successfully.',
            'data' => ['discussion_id' => $discussionId],
        ], HttpStatus::CREATED);
    }

    #[Route('/cohorts/{cohort_id}/discussions/{discussion_id}/posts', 'POST', ['api'])]
    public function createPost(array $vars)
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'parent_post_id' => 'nullable|integer',
                'discussion_id' => 'required|integer',
                'program_id' => 'required|integer',
                'course_id' => 'required|integer',
                'cohort_id' => 'required|integer',
                'body' => 'required|string',
                'author_id' => 'required|integer',
                'files' => 'nullable|array|max:3',
                'files.*.file_name' => 'required_with:files|string',
                'files.*.old_file_name' => 'nullable|string',
                'files.*.file' => 'required_with:files|string',
                'files.*.type' => 'required_with:files|string|in:image',
            ]
        );

        $postId = $this->discussionService->createDiscussionPost($validated);

        if (!$postId) {
            $this->respondError(
                'Failed to create post.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'Post created successfully.',
            'data' => ['post_id' => $postId],
        ], HttpStatus::CREATED);
    }

    #[Route('/cohorts/{cohort_id}/discussions/{discussion_id}', 'PUT', ['api'])]
    public function updateDiscussion(array $vars)
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'discussion_id' => 'required|integer',
                'cohort_id' => 'required|integer',
                'program_id' => 'required|integer',
                'course_id' => 'required|integer',
                'body' => 'required|string',
                'author_id' => 'required|integer',
                'is_locked' => 'nullable|boolean',
                'is_pinned' => 'nullable|boolean',
                'files' => 'nullable|array|max:3',
                'files.*.file_name' => 'required_with:files|string',
                'files.*.old_file_name' => 'nullable|string',
                'files.*.file' => 'required_with:files|string',
                'files.*.type' => 'required_with:files|string|in:image',
            ]
        );

        $updated = $this->discussionService->updateDiscussion($validated);

        if (!$updated) {
            $this->respondError(
                'Failed to update discussion.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'Discussion updated successfully.',
        ]);
    }

    #[Route('/cohorts/{cohort_id}/discussions/{discussion_id}/posts/{post_id}', 'PUT', ['api'])]
    public function updatePost(array $vars)
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'post_id' => 'required|integer',
                'cohort_id' => 'required|integer',
                'program_id' => 'required|integer',
                'course_id' => 'required|integer',
                'discussion_id' => 'required|integer',
                'body' => 'required|string',
                'author_id' => 'required|integer',
                'files' => 'nullable|array|max:3',
                'files.*.file_name' => 'required_with:files|string',
                'files.*.old_file_name' => 'nullable|string',
                'files.*.file' => 'required_with:files|string',
                'files.*.type' => 'required_with:files|string|in:image',
            ]
        );

        $updated = $this->discussionService->updateDiscussionPost($validated);

        if (!$updated) {
            $this->respondError(
                'Failed to update post.',
                HttpStatus::BAD_REQUEST
            );
        }

        $this->respond([
            'success' => true,
            'message' => 'Post updated successfully.',
        ]);
    }

    #[Route('/cohorts/{cohort_id}/posts/{post_id}/like', 'POST', ['api'])]
    public function likePost(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'post_id' => 'required|integer',
                'cohort_id' => 'required|integer',
                'author_id' => 'required|integer',
            ]
        );

        $this->discussionService->likeDiscussionPost($validated);

        $this->respond([
            'success' => true,
            'message' => 'Post liked successfully.',
        ]);
    }

    #[Route('/cohorts/{cohort_id}/posts/{post_id}/unlike', 'POST', ['api'])]
    public function unlikePost(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'post_id' => 'required|integer',
                'cohort_id' => 'required|integer',
                'author_id' => 'required|integer',
            ]
        );

        $this->discussionService->unlikeDiscussionPost($validated);

        $this->respond([
            'success' => true,
            'message' => 'Post unliked successfully.',
        ]);
    }

    #[Route('/cohorts/{cohort_id}/discussions/{discussion_id}/like', 'POST', ['api'])]
    public function likeDiscussion(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'discussion_id' => 'required|integer',
                'cohort_id' => 'required|integer',
                'author_id' => 'required|integer',
            ]
        );

        $this->discussionService->likeDiscussion($validated);

        $this->respond([
            'success' => true,
            'message' => 'Discussion liked successfully.',
        ]);
    }

    #[Route('/cohorts/{cohort_id}/discussions/{discussion_id}/unlike', 'POST', ['api'])]
    public function unlikeDiscussion(array $vars): void
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'discussion_id' => 'required|integer',
                'cohort_id' => 'required|integer',
                'author_id' => 'required|integer',
            ]
        );

        $this->discussionService->unlikeDiscussion($validated);

        $this->respond([
            'success' => true,
            'message' => 'Discussion unliked successfully.',
        ]);
    }

    #[Route('/cohorts/{cohort_id}/discussions', 'GET', ['api'])]
    public function getDiscussions(array $vars)
    {
        $validated = $this->validate(
            $vars,
            [
                'cohort_id' => 'required|integer',
                'page' => 'nullable|integer|min:1',
                'limit' => 'nullable|integer|min:1|max:100',
            ]
        );
        $result = $this->discussionService
            ->getDiscussions($validated);
        return $this->respond([
            'success' => true,
            'data' => $result
        ]);
    }

    #[Route('/cohorts/discussions/{discussion_id}', 'GET', ['api'])]
    public function getDiscussionById(array $vars)
    {
        $validated = $this->validate(
            $vars,
            [
                'discussion_id' => 'required|integer',
            ]
        );
        $result = $this->discussionService->getDiscussionById($validated['discussion_id']);
        return $this->respond([
            'success' => true,
            'data' => $result
        ]);
    }

    #[Route('/discussions/{discussion_id}/posts', 'GET', ['api'])]
    public function getPosts(array $vars)
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'discussion_id' => 'required|integer',
                'author_id' => 'nullable|integer',
                'page' => 'nullable|integer|min:1',
                'limit' => 'nullable|integer|min:1|max:100',
            ]
        );
        $result = $this->discussionService
            ->getDiscussionPosts(
                $validated['discussion_id'],
                $validated['page'] ?? 1,
                $validated['limit'] ?? 20,
                $validated['author_id'] ?? null
            );
        return $this->respond([
            'success' => true,
            'data' => $result
        ]);
    }

    #[Route('/posts/{post_id}/replies', 'GET', ['api'])]
    public function getPostReplies(array $vars)
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'post_id' => 'required|integer',
                'author_id' => 'nullable|integer',
                'page' => 'nullable|integer|min:1',
                'limit' => 'nullable|integer|min:1|max:100',
            ]
        );
        $result = $this->discussionService->getPostReplies(
            $validated['post_id'],
            $validated['page'] ?? 1,
            $validated['limit'] ?? 20,
            $validated['author_id'] ?? null
        );

        return $this->respond([
            'success' => true,
            'data' => $result
        ]);
    }

    #[Route('/cohorts/{cohort_id}/discussions/{discussion_id}', 'DELETE', ['api'])]
    public function deleteDiscussion(array $vars)
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'discussion_id' => 'required|integer',
                'cohort_id' => 'required|integer',
                'author_id' => 'required|integer',
            ]
        );
        $this->discussionService->deleteDiscussion($validated);

        $this->respond([
            'success' => true,
            'message' => 'Discussion deleted successfully.'
        ]);
    }

    #[Route('/cohorts/{cohort_id}/posts/{post_id}', 'DELETE', ['api'])]
    public function deletePost(array $vars)
    {
        $validated = $this->validate(
            [...$this->getRequestData(), ...$vars],
            [
                'post_id' => 'required|integer',
                'cohort_id' => 'required|integer',
                'author_id' => 'required|integer',
            ]
        );

        $this->discussionService->deleteDiscussionPost($validated);

        $this->respond([
            'success' => true,
            'message' => 'Post deleted successfully.'
        ]);
    }
}
