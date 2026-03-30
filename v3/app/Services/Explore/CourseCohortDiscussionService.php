<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Events\EventDispatcher;
use V3\App\Common\Utilities\FileHandler;
use V3\App\Events\Discussion\DiscussionCommentAdded;
use V3\App\Events\Discussion\DiscussionPostReplied;
use V3\App\Events\Discussion\DiscussionReplyReplied;
use V3\App\Events\Discussion\DiscussionStarted;
use V3\App\Models\Explore\Discussion;
use V3\App\Models\Explore\DiscussionLike;
use V3\App\Models\Explore\DiscussionPost;
use V3\App\Models\Explore\DiscussionPostLike;
use V3\App\Models\Explore\ProgramCourseCohort;

class CourseCohortDiscussionService
{
    protected Discussion $discussionModel;
    protected DiscussionLike $discussionLikeModel;
    protected DiscussionPost $discussionPostModel;
    protected DiscussionPostLike $discussionPostLikeModel;
    protected ProgramCourseCohort $cohortModel;
    private FileHandler $fileHandler;

    private const MAX_DEPTH = 5;

    public function __construct(\PDO $pdo)
    {
        $this->discussionModel = new Discussion($pdo);
        $this->discussionLikeModel = new DiscussionLike($pdo);
        $this->discussionPostModel = new DiscussionPost($pdo);
        $this->discussionPostLikeModel = new DiscussionPostLike($pdo);
        $this->cohortModel = new ProgramCourseCohort($pdo);
        $this->fileHandler = new FileHandler();
    }

    public function createDiscussion(array $data): int
    {
        if (!$this->isEnrolledInCohort($data['author_id'], $data['cohort_id'])) {
            throw new \Exception("User must be enrolled in the cohort to create a discussion.");
        }

        if (isset($data['files']) && \is_array($data['files'])) {
            $data['images'] = $this->fileHandler->handleFiles(
                $data['files'],
                false,
                "explore/programs/{$data['program_id']}/courses/{$data['course_id']}/cohorts/{$data['cohort_id']}/discussions"
            );
        }

        $payload = [
            'cohort_id' => $data['cohort_id'],
            'author_id' => $data['author_id'],
            'body' => $data['body'] ?? null,
            'images' => $data['images'] ? json_encode($data['images']) : null,
            'is_pinned' => $data['is_pinned'] ?? false,
            'is_locked' => $data['is_locked'] ?? false,
        ];

        $discussionId = $this->discussionModel->insert($payload);
        EventDispatcher::dispatch(new DiscussionStarted($discussionId));

        return $discussionId;
    }

    public function updateDiscussion(array $data): bool
    {
        $discussion = $this->discussionModel
            ->where('id', $data['discussion_id'])
            ->first();

        if (!$discussion) {
            throw new \Exception("Discussion not found.");
        }

        if (!$this->isEnrolledInCohort($data['author_id'], $discussion['cohort_id'])) {
            throw new \Exception("User must be enrolled in the cohort to update the discussion.");
        }

        $payload = [
            'body' => $data['body'] ?? $discussion['body'],
            'is_pinned' => $data['is_pinned'] ?? (bool) $discussion['is_pinned'],
            'is_locked' => $data['is_locked'] ?? (bool) $discussion['is_locked'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (isset($data['files']) && \is_array($data['files'])) {
            $payload['images'] = json_encode(
                $this->fileHandler->handleFiles(
                    $data['files'],
                    false,
                    "explore/programs/{$data['program_id']}/courses/{$data['course_id']}/cohorts/{$data['cohort_id']}/discussions/{$data['discussion_id']}"
                )
            );
        }

        return $this->discussionModel
            ->where('id', $data['discussion_id'])
            ->update($payload);
    }

    public function createDiscussionPost(array $data): int
    {
        if (!$this->isEnrolledInCohort($data['author_id'], $data['cohort_id'])) {
            throw new \Exception("User must be enrolled in the cohort to create a discussion post.");
        }
        if (isset($data['files']) && \is_array($data['files'])) {
            $data['images'] = $this->fileHandler->handleFiles(
                $data['files'],
                false,
                "explore/programs/{$data['program_id']}/courses/{$data['course_id']}/cohorts/{$data['cohort_id']}/discussions/{$data['discussion_id']}/posts"
            );
        }

        $depth = 0;
        $parentPostId = $data['parent_post_id'] ?? null;
        $parentPost = null;

        if ($parentPostId) {
            $parentPost = $this->discussionPostModel
                ->select(['depth', 'discussion_id'])
                ->where('id', $parentPostId)
                ->first();

            if (!$parentPost) {
                throw new \RuntimeException("Parent post not found.");
            }

            if ($parentPost['discussion_id'] != $data['discussion_id']) {
                throw new \RuntimeException("Parent post does not belong to the same discussion.");
            }

            $depth = $parentPost['depth'] + 1;

            if ($depth > self::MAX_DEPTH) {
                throw new \RuntimeException("Maximum reply depth of " . self::MAX_DEPTH . " exceeded.");
            }
        }

        $discussion = $this->discussionModel
            ->select(['is_locked', 'cohort_id'])
            ->where('id', $data['discussion_id'])
            ->first();

        if ($discussion['is_locked']) {
            throw new \RuntimeException("Discussion is locked.");
        }

        if ($discussion['cohort_id'] != $data['cohort_id']) {
            throw new \RuntimeException("Invalid cohort for this post.");
        }

        $payload = [
            'parent_post_id' => $parentPostId,
            'discussion_id' => $data['discussion_id'],
            'author_id' => $data['author_id'],
            'body' => $data['body'],
            'images' => $data['images'] ? json_encode($data['images']) : null,
            'depth' => $depth,
        ];

        $postId =  $this->discussionPostModel->insert($payload);

        if ($parentPostId) {
            $this->discussionPostModel->rawQuery(
                "UPDATE discussion_posts 
             SET reply_count = reply_count + 1
             WHERE id = :id",
                ['id' => $parentPostId]
            );
        }

        if ($parentPostId === null) {
            EventDispatcher::dispatch(new DiscussionCommentAdded($postId));
        } elseif ((int) $parentPost['depth'] === 0) {
            EventDispatcher::dispatch(new DiscussionPostReplied($postId));
        } else {
            EventDispatcher::dispatch(new DiscussionReplyReplied($postId));
        }

        return $postId;
    }

    public function updateDiscussionPost(array $data): bool
    {
        $post = $this->discussionPostModel
            ->where('id', $data['post_id'])
            ->first();

        if (!$post) {
            throw new \RuntimeException("Post not found.");
        }

        if (!$this->isEnrolledInCohort($data['author_id'], $data['cohort_id'])) {
            throw new \RuntimeException("User must be enrolled in the cohort to update the discussion post.");
        }

        $discussion = $this->discussionModel
            ->select(['is_locked', 'cohort_id'])
            ->where('id', $data['discussion_id'])
            ->first();

        if ($discussion['is_locked']) {
            throw new \RuntimeException("Discussion is locked.");
        }

        if ($discussion['cohort_id'] != $data['cohort_id']) {
            throw new \RuntimeException("Invalid cohort for this post.");
        }

        $payload = [
            'body' => $data['body'] ?? $post['body'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (isset($data['files']) && \is_array($data['files'])) {
            $payload['images'] = json_encode(
                $this->fileHandler->handleFiles(
                    $data['files'],
                    false,
                    "explore/programs/{$data['program_id']}/courses/{$data['course_id']}/cohorts/{$data['cohort_id']}/discussions/{$data['discussion_id']}/posts/{$data['post_id']}"
                )
            );
        }

        return $this->discussionPostModel
            ->where('id', $data['post_id'])
            ->update($payload);
    }

    public function likeDiscussionPost(array $data): void
    {
        if (!$this->isEnrolledInCohort($data['author_id'], $data['cohort_id'])) {
            throw new \RuntimeException("User must be enrolled in the cohort to like a discussion post.");
        }
        $existingLike = $this->discussionPostLikeModel
            ->where('post_id', $data['post_id'])
            ->where('profile_id', $data['author_id'])
            ->first();

        if ($existingLike) {
            throw new \RuntimeException("Post already liked by this user.");
        }

        $payload = [
            'post_id' => $data['post_id'],
            'profile_id' => $data['author_id'],
        ];

        $this->discussionPostLikeModel->insert($payload);
        $this->discussionPostModel->rawQuery(
            "UPDATE discussion_posts 
             SET likes_count = likes_count + 1 
             WHERE id = :id",
            ['id' => $data['post_id']]
        );
    }

    public function unlikeDiscussionPost(array $data): void
    {
        if (!$this->isEnrolledInCohort($data['author_id'], $data['cohort_id'])) {
            throw new \RuntimeException("User must be enrolled in the cohort to unlike a discussion post.");
        }

        $existingLike = $this->discussionPostLikeModel
            ->where('post_id', $data['post_id'])
            ->where('profile_id', $data['author_id'])
            ->first();

        if (!$existingLike) {
            throw new \RuntimeException("Like not found for this user and post.");
        }

        $this->discussionPostLikeModel
            ->where('id', $existingLike['id'])
            ->delete();

        $this->discussionPostModel->rawQuery(
            "UPDATE discussion_posts 
             SET likes_count = likes_count - 1 
             WHERE id = :id",
            ['id' => $data['post_id']]
        );
    }

    public function likeDiscussion(array $data): void
    {
        if (!$this->isEnrolledInCohort($data['author_id'], $data['cohort_id'])) {
            throw new \RuntimeException("User must be enrolled in the cohort to like a discussion.");
        }

        $existingLike = $this->discussionLikeModel
            ->where('discussion_id', $data['discussion_id'])
            ->where('profile_id', $data['author_id'])
            ->first();

        if ($existingLike) {
            throw new \RuntimeException("Discussion already liked by this user.");
        }

        $this->discussionLikeModel->insert([
            'discussion_id' => $data['discussion_id'],
            'profile_id' => $data['author_id'],
        ]);

        $this->discussionModel->rawQuery(
            "UPDATE discussions
             SET likes_count = likes_count + 1
             WHERE id = :id",
            ['id' => $data['discussion_id']]
        );
    }

    public function unlikeDiscussion(array $data): void
    {
        if (!$this->isEnrolledInCohort($data['author_id'], $data['cohort_id'])) {
            throw new \RuntimeException("User must be enrolled in the cohort to unlike a discussion.");
        }

        $existingLike = $this->discussionLikeModel
            ->where('discussion_id', $data['discussion_id'])
            ->where('profile_id', $data['author_id'])
            ->first();

        if (!$existingLike) {
            throw new \RuntimeException("Like not found for this user and discussion.");
        }

        $this->discussionLikeModel
            ->where('id', $existingLike['id'])
            ->delete();

        $this->discussionModel->rawQuery(
            "UPDATE discussions
             SET likes_count = likes_count - 1
             WHERE id = :id AND likes_count > 0",
            ['id' => $data['discussion_id']]
        );
    }

    public function deleteDiscussion(array $data): bool
    {
        if (!$this->isEnrolledInCohort($data['author_id'], $data['cohort_id'])) {
            throw new \RuntimeException("User must be enrolled in the cohort to delete a discussion.");
        }

        // $this->discussionLikeModel
        //     ->where('discussion_id', $data['discussion_id'])
        //     ->update(['deleted_at' => date('Y-m-d H:i:s')]);

        // $posts = $this->discussionPostModel
        //     ->where('discussion_id', $data['discussion_id'])
        //     ->get();

        // foreach ($posts as $post) {
        //     $this->discussionPostLikeModel
        //         ->where('post_id', $post['id'])
        //         ->update(['deleted_at' => date('Y-m-d H:i:s')]);
        // }

        // $this->discussionPostModel
        //     ->where('discussion_id', $data['discussion_id'])
        //     ->update(['deleted_at' => date('Y-m-d H:i:s')]);

        return $this->discussionModel
            ->where('id', $data['discussion_id'])
            ->update(['deleted_at' => date('Y-m-d H:i:s')]);
    }

    public function deleteDiscussionPost(array $data): bool
    {
        $post = $this->discussionPostModel
            ->where('id', $data['post_id'])
            ->first();

        if (!$post) {
            throw new \RuntimeException("Post not found.");
        }

        if (!$this->isEnrolledInCohort($data['author_id'], $data['cohort_id'])) {
            throw new \RuntimeException("User must be enrolled in the cohort to delete a discussion post.");
        }

        // $this->discussionPostLikeModel
        //     ->where('post_id', $data['post_id'])
        //     ->update(['deleted_at' => date('Y-m-d H:i:s')]);

        if ($post['parent_post_id']) {
            $this->discussionPostModel->rawQuery(
                "UPDATE discussion_posts 
             SET reply_count = reply_count - 1
             WHERE id = :id",
                ['id' => $post['parent_post_id']]
            );
        }

        return $this->discussionPostModel
            ->where('id', $data['post_id'])
            ->update(['deleted_at' => date('Y-m-d H:i:s')]);
    }

    public function getDiscussions(array $filters): array
    {
        $totalRows = $this->discussionModel->rawQuery(
            "SELECT COUNT(*) AS total
            FROM discussions
            WHERE cohort_id = :cohort_id
              AND deleted_at IS NULL",
            [
                'cohort_id' => $filters['cohort_id'],
            ]
        );
        $total = (int) ($totalRows[0]['total'] ?? 0);

        $limit = $filters['limit'] ?? 20;
        $page = $filters['page'] ?? 1;
        $offset = ($page - 1) * $limit;

        $discussions = $this->discussionModel
            ->rawQuery(
                "SELECT
                    d.id,
                    d.cohort_id,
                    d.author_id,
                    d.title,
                    d.body,
                    d.created_at,
                    d.images,
                    d.is_pinned AS is_pinned,
                    d.is_locked AS is_locked,
                    d.likes_count AS likes_count,
                    pp.first_name,
                    pp.last_name,
                    COUNT(p.id) AS posts_count
                FROM discussions d
                LEFT JOIN program_profiles pp
                    ON pp.id = d.author_id
                LEFT JOIN discussion_posts p
                    ON p.discussion_id = d.id
                    AND p.deleted_at IS NULL
                WHERE d.cohort_id = :cohort_id
                AND d.deleted_at IS NULL
                GROUP BY d.id
                ORDER BY d.created_at DESC
                LIMIT :limit OFFSET :offset",
                [
                    'limit' => $limit,
                    'offset' => $offset,
                    'cohort_id' => $filters['cohort_id'],
                ]
            );

        return [
            'data' => array_map(function (array $discussion) {
                $fullName = trim((string) ($discussion['first_name'] ?? '') . ' ' . (string) ($discussion['last_name'] ?? ''));

                return [
                    'id' => (int) $discussion['id'],
                    'cohort_id' => (int) $discussion['cohort_id'],
                    'author_id' => (int) $discussion['author_id'],
                    'author' => [
                        'first_name' => $discussion['first_name'] ?? null,
                        'last_name' => $discussion['last_name'] ?? null,
                        'full_name' => $fullName !== '' ? $fullName : null,
                    ],
                    'title' => $discussion['title'],
                    'body' => $discussion['body'],
                    'created_at' => $discussion['created_at'],
                    'images' => $discussion['images'] ? json_decode($discussion['images'], true) : [],
                    'is_pinned' => (bool) $discussion['is_pinned'],
                    'is_locked' => (bool) $discussion['is_locked'],
                    'likes_count' => (int) ($discussion['likes_count'] ?? 0),
                    'posts_count' => (int) $discussion['posts_count'],
                ];
            }, $discussions),
            'meta' => [
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => $limit > 0 ? (int) ceil($total / $limit) : 0,
                'has_next' => ($page * $limit) < $total,
                'has_prev' => $page > 1,
            ],
        ];
    }

    public function getDiscussionById(int $discussionId): ?array
    {
        $discussion = $this->findDiscussionWithAuthor($discussionId);

        return $discussion ? $this->formatDiscussion($discussion) : null;
    }

    public function getDiscussionPosts(int $discussionId, int $page = 1, int $limit = 20, ?int $authorId = null): array
    {
        $offset = ($page - 1) * $limit;
        $discussion = $this->findDiscussionWithAuthor($discussionId);
        $totalRows = $this->discussionPostModel->rawQuery(
            "SELECT COUNT(*) AS total
        FROM discussion_posts
        WHERE discussion_id = :discussion_id
        AND deleted_at IS NULL
        AND parent_post_id IS NULL",
            [
                'discussion_id' => $discussionId,
            ]
        );
        $total = (int) ($totalRows[0]['total'] ?? 0);

        $posts = $this->discussionPostModel->rawQuery(
            "SELECT 
            p.*,
            pp.first_name,
            pp.last_name,
            CASE
                WHEN dpl.id IS NULL THEN 0
                ELSE 1
            END AS is_liked
        FROM discussion_posts p
        LEFT JOIN program_profiles pp
            ON pp.id = p.author_id
        LEFT JOIN discussion_post_likes dpl
            ON dpl.post_id = p.id
            AND dpl.profile_id = :author_id
        WHERE 
            p.discussion_id = :discussion_id
        AND
            p.deleted_at IS NULL
        AND 
            p.parent_post_id IS NULL
        ORDER BY p.created_at ASC
        LIMIT :limit OFFSET :offset",
            [
                'discussion_id' => $discussionId,
                'author_id' => $authorId,
                'limit' => $limit,
                'offset' => $offset
            ]
        );

        return [
            'discussion' => $discussion ? $this->formatDiscussion($discussion) : null,
            'posts' => array_map(fn(array $post) => $this->formatPost($post), $posts),
            'meta' => [
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => $limit > 0 ? (int) ceil($total / $limit) : 0,
                'has_next' => ($page * $limit) < $total,
                'has_prev' => $page > 1,
            ],
        ];
    }

    public function getPostReplies(int $postId, int $page = 1, int $limit = 20, ?int $authorId = null): array
    {
        $offset = ($page - 1) * $limit;
        $post = $this->findPostWithAuthor($postId, $authorId);
        $totalRows = $this->discussionPostModel->rawQuery(
            "SELECT COUNT(*) AS total
        FROM discussion_posts
        WHERE parent_post_id = :post_id
        AND deleted_at IS NULL",
            [
                'post_id' => $postId,
            ]
        );
        $total = (int) ($totalRows[0]['total'] ?? 0);

        $replies = $this->discussionPostModel->rawQuery(
            "SELECT 
            p.*,
            pp.first_name,
            pp.last_name,
            CASE
                WHEN dpl.id IS NULL THEN 0
                ELSE 1
            END AS is_liked
        FROM discussion_posts p
        LEFT JOIN program_profiles pp
            ON pp.id = p.author_id
        LEFT JOIN discussion_post_likes dpl
            ON dpl.post_id = p.id
            AND dpl.profile_id = :author_id
        WHERE 
            p.parent_post_id = :post_id
        AND
            p.deleted_at IS NULL
        ORDER BY p.created_at ASC
        LIMIT :limit OFFSET :offset",
            [
                'post_id' => $postId,
                'author_id' => $authorId,
                'limit' => $limit,
                'offset' => $offset
            ]
        );

        return [
            'post' => $post ? $this->formatPost($post) : null,
            'replies' => array_map(fn(array $reply) => $this->formatPost($reply), $replies),
            'meta' => [
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => $limit > 0 ? (int) ceil($total / $limit) : 0,
                'has_next' => ($page * $limit) < $total,
                'has_prev' => $page > 1,
            ],
        ];
    }

    private function findDiscussionWithAuthor(int $discussionId): ?array
    {
        $rows = $this->discussionModel->rawQuery(
            "SELECT
                d.*,
                p.first_name,
                p.last_name
            FROM discussions d
            LEFT JOIN program_profiles p
                ON p.id = d.author_id
            WHERE d.id = :discussion_id
            AND d.deleted_at IS NULL
            LIMIT 1",
            [
                'discussion_id' => $discussionId,
            ]
        );

        return $rows[0] ?? null;
    }

    private function findPostWithAuthor(int $postId, ?int $authorId = null): ?array
    {
        $rows = $this->discussionPostModel->rawQuery(
            "SELECT
                p.*,
                pp.first_name,
                pp.last_name,
                CASE
                    WHEN dpl.id IS NULL THEN 0
                    ELSE 1
                END AS is_liked
            FROM discussion_posts p
            LEFT JOIN program_profiles pp
                ON pp.id = p.author_id
            LEFT JOIN discussion_post_likes dpl
                ON dpl.post_id = p.id
                AND dpl.profile_id = :author_id
            WHERE p.id = :post_id
            AND p.deleted_at IS NULL
            LIMIT 1",
            [
                'post_id' => $postId,
                'author_id' => $authorId,
            ]
        );

        return $rows[0] ?? null;
    }

    private function formatDiscussion(array $row): array
    {
        $fullName = trim((string) ($row['first_name'] ?? '') . ' ' . (string) ($row['last_name'] ?? ''));

        return [
            'id' => (int) $row['id'],
            'cohort_id' => (int) $row['cohort_id'],
            'author_id' => (int) $row['author_id'],
            'author' => [
                'first_name' => $row['first_name'] ?? null,
                'last_name' => $row['last_name'] ?? null,
                'full_name' => $fullName !== '' ? $fullName : null,
            ],
            'title' => $row['title'],
            'body' => $row['body'],
            'images' => $row['images'] ? json_decode($row['images'], true) : [],
            'is_pinned' => (bool) $row['is_pinned'],
            'is_locked' => (bool) $row['is_locked'],
            'likes_count' => (int) ($row['likes_count'] ?? 0),
            'created_at' => $row['created_at'],
        ];
    }

    private function formatPost(array $row): array
    {
        $fullName = trim((string) ($row['first_name'] ?? '') . ' ' . (string) ($row['last_name'] ?? ''));

        return [
            'id' => (int) $row['id'],
            'parent_post_id' => isset($row['parent_post_id']) ? (int) $row['parent_post_id'] : null,
            'discussion_id' => (int) $row['discussion_id'],
            'author_id' => (int) $row['author_id'],
            'author' => [
                'first_name' => $row['first_name'] ?? null,
                'last_name' => $row['last_name'] ?? null,
                'full_name' => $fullName !== '' ? $fullName : null,
            ],
            'body' => $row['body'],
            'images' => $row['images'] ? json_decode($row['images'], true) : [],
            'depth' => isset($row['depth']) ? (int) $row['depth'] : 0,
            'reply_count' => (int) $row['reply_count'],
            'likes_count' => (int) $row['likes_count'],
            'is_liked' => (bool) ($row['is_liked'] ?? false),
            'created_at' => $row['created_at'],
        ];
    }

    private function isEnrolledInCohort(int $profileId, int $cohortId): bool
    {
        $enrollment = $this->cohortModel
            ->rawQuery(
                "SELECT 1 
                    FROM program_course_cohort_enrollments
                    WHERE 
                        cohort_id = :cohort_id 
                    AND 
                        profile_id = :profile_id",
                [
                    'cohort_id' => $cohortId,
                    'profile_id' => $profileId,
                ]
            );

        return !empty($enrollment);
    }
}
