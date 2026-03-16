<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Events\EventDispatcher;
use V3\App\Common\Utilities\FileHandler;
use V3\App\Events\Discussion\DiscussionCommentAdded;
use V3\App\Events\Discussion\DiscussionPostReplied;
use V3\App\Events\Discussion\DiscussionReplyReplied;
use V3\App\Events\Discussion\DiscussionStarted;
use V3\App\Models\Explore\Discussion;
use V3\App\Models\Explore\DiscussionPost;
use V3\App\Models\Explore\DiscussionPostLike;
use V3\App\Models\Explore\ProgramCourseCohort;

class CourseCohortDiscussionService
{
    protected Discussion $discussionModel;
    protected DiscussionPost $discussionPostModel;
    protected DiscussionPostLike $discussionPostLikeModel;
    protected ProgramCourseCohort $cohortModel;
    private FileHandler $fileHandler;

    private const MAX_DEPTH = 5;

    public function __construct(\PDO $pdo)
    {
        $this->discussionModel = new Discussion($pdo);
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
            'title' => $data['title'],
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
            'title' => $data['title'] ?? $discussion['title'],
            'body' => $data['body'] ?? $discussion['body'],
            'is_pinned' => $data['is_pinned'] ?? (bool) $discussion['is_pinned'],
            'is_locked' => $data['is_locked'] ?? (bool) $discussion['is_locked'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (isset($data['files']) && \is_array($data['files'])) {
            $payload['images'] = json_encode(
                array_merge(
                    $discussion['images'] ? json_decode($discussion['images'], true) : [],
                    $this->fileHandler->handleFiles(
                        $data['files'],
                        false,
                        "explore/programs/{$data['program_id']}/courses/{$data['course_id']}/cohorts/{$data['cohort_id']}/discussions/{$data['discussion_id']}"
                    )
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
                throw new \Exception("Parent post not found.");
            }

            if ($parentPost['discussion_id'] != $data['discussion_id']) {
                throw new \Exception("Parent post does not belong to the same discussion.");
            }

            $depth = $parentPost['depth'] + 1;

            if ($depth > self::MAX_DEPTH) {
                throw new \Exception("Maximum reply depth of " . self::MAX_DEPTH . " exceeded.");
            }
        }

        $discussion = $this->discussionModel
            ->select(['is_locked', 'cohort_id'])
            ->where('id', $data['discussion_id'])
            ->first();

        if ($discussion['is_locked']) {
            throw new \Exception("Discussion is locked.");
        }

        if ($discussion['cohort_id'] != $data['cohort_id']) {
            throw new \Exception("Invalid cohort for this post.");
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
            throw new \Exception("Post not found.");
        }

        if (!$this->isEnrolledInCohort($data['author_id'], $data['cohort_id'])) {
            throw new \Exception("User must be enrolled in the cohort to update the discussion post.");
        }

        $discussion = $this->discussionModel
            ->select(['is_locked', 'cohort_id'])
            ->where('id', $data['discussion_id'])
            ->first();

        if ($discussion['is_locked']) {
            throw new \Exception("Discussion is locked.");
        }

        if ($discussion['cohort_id'] != $data['cohort_id']) {
            throw new \Exception("Invalid cohort for this post.");
        }

        $payload = [
            'body' => $data['body'] ?? $post['body'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (isset($data['files']) && \is_array($data['files'])) {
            $payload['images'] = json_encode(
                array_merge(
                    $post['images'] ? json_decode($post['images'], true) : [],
                    $this->fileHandler->handleFiles(
                        $data['files'],
                        false,
                        "explore/programs/{$data['program_id']}/courses/{$data['course_id']}/cohorts/{$data['cohort_id']}/discussions/{$data['discussion_id']}/posts/{$data['post_id']}"
                    )
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
            throw new \Exception("User must be enrolled in the cohort to like a discussion post.");
        }
        $existingLike = $this->discussionPostLikeModel
            ->where('post_id', $data['post_id'])
            ->where('profile_id', $data['author_id'])
            ->first();

        if ($existingLike) {
            throw new \Exception("Post already liked by this user.");
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
            throw new \Exception("User must be enrolled in the cohort to unlike a discussion post.");
        }

        $existingLike = $this->discussionPostLikeModel
            ->where('post_id', $data['post_id'])
            ->where('profile_id', $data['author_id'])
            ->first();

        if (!$existingLike) {
            throw new \Exception("Like not found for this user and post.");
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

    public function deleteDiscussion(array $data): bool
    {
        if (!$this->isEnrolledInCohort($data['author_id'], $data['cohort_id'])) {
            throw new \Exception("User must be enrolled in the cohort to delete a discussion.");
        }

        $posts = $this->discussionPostModel
            ->where('discussion_id', $data['discussion_id'])
            ->get();

        foreach ($posts as $post) {
            $this->discussionPostLikeModel
                ->where('post_id', $post['id'])
                ->delete();
        }

        $this->discussionPostModel
            ->where('discussion_id', $data['discussion_id'])
            ->delete();

        return $this->discussionModel
            ->where('id', $data['discussion_id'])
            ->delete();
    }

    public function deleteDiscussionPost(array $data): bool
    {
        $post = $this->discussionPostModel
            ->where('id', $data['post_id'])
            ->first();

        if (!$post) {
            throw new \Exception("Post not found.");
        }

        if (!$this->isEnrolledInCohort($data['author_id'], $data['cohort_id'])) {
            throw new \Exception("User must be enrolled in the cohort to delete a discussion post.");
        }

        $this->discussionPostLikeModel
            ->where('post_id', $data['post_id'])
            ->delete();

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
            ->delete();
    }

    public function getDiscussions(int $page = 1, int $limit = 20): array
    {
        $totalRows = $this->discussionModel->rawQuery(
            "SELECT COUNT(*) AS total
            FROM discussions"
        );
        $total = (int) ($totalRows[0]['total'] ?? 0);

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
                    pp.first_name,
                    pp.last_name,
                    COUNT(p.id) AS posts_count
                FROM discussions d
                LEFT JOIN program_profiles pp
                    ON pp.id = d.author_id
                LEFT JOIN discussion_posts p
                    ON p.discussion_id = d.id
                GROUP BY d.id
                ORDER BY d.created_at DESC
                LIMIT :limit OFFSET :offset",
                [
                    'limit' => $limit,
                    'offset' => ($page - 1) * $limit
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
        $rows = $this->discussionModel->rawQuery(
            "SELECT
                d.*,
                p.first_name,
                p.last_name
            FROM discussions d
            LEFT JOIN program_profiles p
                ON p.id = d.author_id
            WHERE d.id = :discussion_id
            LIMIT 1",
            [
                'discussion_id' => $discussionId,
            ]
        );

        if (!$rows) {
            return null;
        }

        $row = $rows[0];
        $fullName = trim((string) ($row['first_name'] ?? '') . ' ' . (string) ($row['last_name'] ?? ''));

        return [
            'id' => $row['id'],
            'cohort_id' => $row['cohort_id'],
            'author_id' => $row['author_id'],
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
            'created_at' => $row['created_at'],
        ];
    }

    public function getDiscussionPosts(int $discussionId, int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        $totalRows = $this->discussionPostModel->rawQuery(
            "SELECT COUNT(*) AS total
        FROM discussion_posts
        WHERE discussion_id = :discussion_id
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
            pp.last_name
        FROM discussion_posts p
        LEFT JOIN program_profiles pp
            ON pp.id = p.author_id
        WHERE 
            p.discussion_id = :discussion_id
        AND 
            p.parent_post_id IS NULL
        ORDER BY p.created_at ASC
        LIMIT :limit OFFSET :offset",
            [
                'discussion_id' => $discussionId,
                'limit' => $limit,
                'offset' => $offset
            ]
        );

        return [
            'data' => array_map(function (array $post) {
                $fullName = trim((string) ($post['first_name'] ?? '') . ' ' . (string) ($post['last_name'] ?? ''));
                return [
                    'id' => (int)$post['id'],
                    'discussion_id' => (int)$post['discussion_id'],
                    'author_id' => (int)$post['author_id'],
                    'author' => [
                        'first_name' => $post['first_name'] ?? null,
                        'last_name' => $post['last_name'] ?? null,
                        'full_name' => $fullName !== '' ? $fullName : null,
                    ],
                    'body' => $post['body'],
                    'images' => $post['images'] ? json_decode($post['images'], true) : [],
                    'reply_count' => (int)$post['reply_count'],
                    'likes_count' => (int)$post['likes_count'],
                    'created_at' => $post['created_at'],
                ];
            }, $posts),
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

    public function getPostReplies(int $postId, int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        $totalRows = $this->discussionPostModel->rawQuery(
            "SELECT COUNT(*) AS total
        FROM discussion_posts
        WHERE parent_post_id = :post_id",
            [
                'post_id' => $postId,
            ]
        );
        $total = (int) ($totalRows[0]['total'] ?? 0);

        $replies = $this->discussionPostModel->rawQuery(
            "SELECT 
            p.*,
            pp.first_name,
            pp.last_name
        FROM discussion_posts p
        LEFT JOIN program_profiles pp
            ON pp.id = p.author_id
        WHERE 
            p.parent_post_id = :post_id
        ORDER BY p.created_at ASC
        LIMIT :limit OFFSET :offset",
            [
                'post_id' => $postId,
                'limit' => $limit,
                'offset' => $offset
            ]
        );

        return [
            'data' => array_map(function (array $reply) {
                $fullName = trim((string) ($reply['first_name'] ?? '') . ' ' . (string) ($reply['last_name'] ?? ''));

                return [
                    'id' => (int) $reply['id'],
                    'discussion_id' => (int) $reply['discussion_id'],
                    'author_id' => (int) $reply['author_id'],
                    'author' => [
                        'first_name' => $reply['first_name'] ?? null,
                        'last_name' => $reply['last_name'] ?? null,
                        'full_name' => $fullName !== '' ? $fullName : null,
                    ],
                    'body' => $reply['body'],
                    'images' => $reply['images'] ? json_decode($reply['images'], true) : [],
                    'reply_count' => (int) $reply['reply_count'],
                    'likes_count' => (int) $reply['likes_count'],
                    'created_at' => $reply['created_at'],
                ];
            }, $replies),
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
