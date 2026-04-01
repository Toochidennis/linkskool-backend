<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Events\EventDispatcher;
use V3\App\Common\Utilities\FileHandler;
use V3\App\Events\News\NewsPosted;
use V3\App\Models\Explore\News;
use V3\App\Models\Explore\NewsCategory;
use V3\App\Models\Explore\NewsCategoryPivot;

class NewsService
{
    private News $newsModel;
    private FileHandler $fileHandler;
    private NewsCategory $newsCategoryModel;
    private NewsCategoryPivot $newsCategoryPivotModel;

    public function __construct(private \PDO $pdo)
    {
        $this->newsModel = new News($this->pdo);
        $this->fileHandler = new FileHandler();
        $this->newsCategoryModel = new NewsCategory($this->pdo);
        $this->newsCategoryPivotModel = new NewsCategoryPivot($this->pdo);
    }

    public function addNews(array $data): bool|int
    {
        $imageMap = [];

        // Handle $_FILES structure: images[name][], images[tmp_name][], etc.
        $fileCount = \count($data['images']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            $tmpName = $data['images']['tmp_name'][$i];
            $fileName = strtolower(trim(basename($data['images']['name'][$i])));
            $error = (int)$data['images']['error'][$i];

            // Only process files with no upload errors
            if ($error === 0 && is_uploaded_file($tmpName)) {
                $fileContent = file_get_contents($tmpName);
                $base64 = base64_encode($fileContent);

                $imageMap[] = [
                    'file_name' => $fileName,
                    'old_file_name' => '',
                    'type' => 'image',
                    'file' => $base64
                ];
            }
        }

        $processedImages = $this->fileHandler->handleFiles(
            $imageMap,
            false,
            $this->buildNewsGroupPath($data)
        );

        $payload = [
            'title' => $data['title'],
            'content' => $data['content'],
            'date_posted' => $data['date_posted'] ?? date('Y-m-d H:i:s'),
            'category_ids' => json_encode($data['category_ids']),
            'images' => json_encode($processedImages),
            'author_id' => $data['author_id'],
            'author_name' => $data['author_name'],
            'status' => $data['status'],
            'deadline' => $data['deadline'] ?? null,
        ];

        $newsId = $this->newsModel->insert($payload);

        foreach ($data['category_ids'] as $categoryId) {
            $this->newsCategoryPivotModel->insert([
                'news_id' => $newsId,
                'category_id' => $categoryId
            ]);
        }

        if (($data['notify'] ?? false)) {
            $this->notifyNews($newsId, $payload['author_id']);
        }

        return $newsId ?: false;
    }

    public function updateNewsStatus(int $newsId, string $status): bool
    {
        $existingNews = $this->newsModel
            ->where('id', $newsId)
            ->first();

        $updated = $this->newsModel
            ->where('id', $newsId)
            ->update(['status' => $status]);

        return $updated;
    }

    public function notifyNews(int $newsId, int $notifiedBy): bool
    {
        $existingNews = $this->newsModel
            ->where('id', $newsId)
            ->first();

        if (!$existingNews || $existingNews['status'] !== 'published') {
            return false;
        }

        EventDispatcher::dispatch(new NewsPosted($newsId));
        $this->newsModel
            ->where('id', $newsId)
            ->update([
                'notified_at' => date('Y-m-d H:i:s'),
                'notified_by' => $notifiedBy,
            ]);

        return true;
    }

    public function updateNews(array $data): bool
    {
        $newImages = $this->uploadNewImages($data);
        $previousStatus = null;

        [$finalImages, $filesToDelete] = $this->resolveImages(
            (int)$data['id'],
            $data['old_images'] ?? null,
            $newImages
        );

        try {
            $this->pdo->beginTransaction();
            $existingNews = $this->newsModel
                ->where('id', $data['id'])
                ->first();
            $previousStatus = $existingNews['status'] ?? null;

            $this->newsCategoryPivotModel
                ->where('news_id', $data['id'])
                ->delete();

            foreach ($data['category_ids'] as $categoryId) {
                $this->newsCategoryPivotModel->insert([
                    'news_id' => $data['id'],
                    'category_id' => $categoryId
                ]);
            }

            $payload = [
                'title' => $data['title'],
                'content' => $data['content'],
                'category_ids' => json_encode($data['category_ids']),
                'images' => json_encode($finalImages),
                'author_id' => $data['author_id'],
                'author_name' => $data['author_name'],
                'status' => $data['status'],
                'deadline' => $data['deadline'] ?? null,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $this->newsModel
                ->where('id', $data['id'])
                ->update($payload);

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }

        foreach ($filesToDelete as $file) {
            $this->fileHandler->deleteOldFile($file);
        }

        return true;
    }

    public function getNewsById(int $newsId): bool|array
    {
        return $this->newsModel
            ->where('id', $newsId)
            ->first();
    }

    public function getNewsAdmin(array $filters): array
    {
        $news =  $this->newsModel
            ->orderBy(['date_posted' => 'DESC', 'created_at' => 'DESC'])
            ->paginate($filters['page'] ?? 1, $filters['limit'] ?? 15);

        foreach ($news['data'] as &$item) {
            $categories = json_decode($item['category_ids'], true);
            $item['categories'] = $this->newsCategoryModel
                ->in('id', $categories)
                ->get();
            $item['images'] = json_decode($item['images'], true);
        }

        return $news;
    }

    public function deleteNews(int $newsId): bool
    {
        $oldNews = $this->newsModel
            ->where('id', $newsId)
            ->first();

        if ($oldNews && !empty($oldNews['images'])) {
            $images = json_decode($oldNews['images'], true);
            foreach ($images as $image) {
                $this->fileHandler->deleteOldFile($image['file_name']);
            }
        }

        return $this->newsModel
            ->where('id', $newsId)
            ->delete();
    }

    public function getNews(array $filters)
    {
        $news = $this->newsModel
            ->where('status', 'published')
            ->orderBy(['date_posted' => 'DESC', 'created_at' => 'DESC'])
            ->paginate($filters['page'] ?? 1, $filters['limit'] ?? 25);

        $formattedNews = array_map(function ($item) {
            $item['images'] = json_decode($item['images'], true);
            unset($item['category_ids']);
            return $item;
        }, $news['data']);

        $newsIds = array_column($news['data'], 'id');

        $rawCategories = $this->newsCategoryModel
            ->select(['explore_news_categories.name', 'explore_news_category_pivot.news_id'])
            ->join('explore_news_category_pivot', 'explore_news_category_pivot.category_id = explore_news_categories.id')
            ->in('explore_news_category_pivot.news_id', $newsIds)
            ->get();

        $categoryMap = [];

        foreach ($rawCategories as $row) {
            $categoryMap[$row['news_id']][] = $row['name'];
        }

        $latestIds = array_slice(
            array_column($news['data'], 'id'),
            0,
            10
        );

        $latestCategories = [];

        foreach ($latestIds as $id) {
            $latestCategories = array_merge(
                $latestCategories,
                $categoryMap[$id] ?? []
            );
        }

        $latestCategories = array_unique($latestCategories);

        $relatedIds = [];
        $relatedSet = []; // prevent duplicates

        foreach ($categoryMap as $newsId => $cats) {
            if (\in_array($newsId, $latestIds, true)) {
                continue;
            }

            if (!empty(array_intersect($cats, $latestCategories))) {
                $relatedSet[$newsId] = true; // use map as a set
            }
        }

        $relatedIds = array_slice(
            array_keys($relatedSet),
            0,
            5
        );


        $used = \array_merge($latestIds, $relatedIds);

        $remaining = array_values(
            array_diff(array_column($news['data'], 'id'), $used)
        );

        $recommendedIds = [
            ...\array_slice($latestIds, 0, 2),
            ...\array_slice($remaining, 0, 3)
        ];

        $recommendedIds = \array_values(\array_unique($recommendedIds));

        $categories = [];

        foreach ($categoryMap as $newsId => $cats) {
            foreach ($cats as $cat) {
                $categories[$cat][] = $newsId;
            }
        }

        return [
            'data' => [
                'groups' => [
                    'latest' => $latestIds,
                    'related' => $relatedIds,
                    'recommended' => $recommendedIds,
                ],
                'categories' => $categories,
                'news' => $formattedNews,
            ],
            'meta' => $news['meta']
        ];
    }

    public function resolveImages(
        int $newsId,
        ?array $oldImages,
        array $newImages
    ): array {
        $existingImages = $this->newsModel
            ->select(['images'])
            ->where('id', $newsId)
            ->first();

        $existingImages = json_decode($existingImages['images'] ?? '[]', true);

        $remaining = [];
        $toDelete  = [];

        foreach ($existingImages as $img) {
            $deleted = false;

            foreach ($oldImages ?? [] as $old) {
                if (
                    $old['file_name'] === $img['file_name'] &&
                    ($old['is_deleted'] ?? false) === true
                ) {
                    $toDelete[] = $img['file_name'];
                    $deleted = true;
                    break;
                }
            }

            if (!$deleted) {
                $remaining[] = $img;
            }
        }

        // merge with newly uploaded images
        $final = array_merge($remaining, $newImages);

        return [$final, $toDelete];
    }

    public function uploadNewImages(array $images): array
    {
        $newImages = [];

        $fileCount = \count($images['images']['name'] ?? []);
        for ($i = 0; $i < $fileCount; $i++) {
            if (
                (int)$images['images']['error'][$i] === 0 &&
                is_uploaded_file($images['images']['tmp_name'][$i])
            ) {
                $newImages[] = [
                    'file_name' => strtolower(trim(basename($images['images']['name'][$i]))),
                    'type' => 'image',
                    'file' => base64_encode(
                        file_get_contents($images['images']['tmp_name'][$i])
                    )
                ];
            }
        }

        $uploadedImages = $newImages
            ? $this->fileHandler->handleFiles(
                $newImages,
                false,
                $this->buildNewsGroupPath($images)
            )
            : [];

        return $uploadedImages;
    }

    private function buildNewsGroupPath(array $data): string
    {
        $id = (int)($data['id'] ?? 0);
        $title = $this->toSlug((string)($data['title'] ?? 'news'));

        if ($id > 0) {
            return "explore/news/{$id}/{$title}";
        }

        return "explore/news/{$title}";
    }

    private function toSlug(string $text): string
    {
        return strtolower(trim((string)preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
    }
}
