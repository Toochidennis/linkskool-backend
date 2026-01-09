<?php

namespace V3\App\Services\Explore;

use V3\App\Common\Utilities\FileHandler;
use V3\App\Models\Explore\News;
use V3\App\Models\Explore\NewsCategory;
use V3\App\Models\Explore\NewsCategoryPivot;

class NewsService
{
    private News $newsModel;
    private FileHandler $fileHandler;
    private NewsCategory $newsCategoryModel;
    private NewsCategoryPivot $newsCategoryPivotModel;

    public function __construct(\PDO $pdo)
    {
        $this->newsModel = new News($pdo);
        $this->fileHandler = new FileHandler();
        $this->newsCategoryModel = new NewsCategory($pdo);
        $this->newsCategoryPivotModel = new NewsCategoryPivot($pdo);
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

        $processedImages = $this->fileHandler->handleFiles($imageMap);

        $payload = [
            'title' => $data['title'],
            'content' => $data['content'],
            'date_posted' => $data['date_posted'] ?? date('Y-m-d H:i:s'),
            'category_ids' => json_encode($data['category_ids']),
            'images' => json_encode($processedImages),
            'author_id' => $data['author_id'],
            'author_name' => $data['author_name'],
            'status' => $data['status'],
        ];

        $newsId = $this->newsModel->insert($payload);

        foreach ($data['category_ids'] as $categoryId) {
            $this->newsCategoryPivotModel->insert([
                'news_id' => $newsId,
                'category_id' => $categoryId
            ]);
        }

        return $newsId ?: false;
    }

    public function updateNewsStatus(int $newsId, string $status): bool
    {
        return $this->newsModel
            ->where('id', $newsId)
            ->update(['status' => $status]);
    }

    public function updateNews(int $newsId, array $data): bool
    {
        $payload = [];
        if(isset($data['images'])){

        }



        return $this->newsModel
            ->where('id', $newsId)
            ->update($payload);
    }

    public function getNewsById(int $newsId): bool|array
    {
        return $this->newsModel
            ->where('id', $newsId)
            ->first();
    }

    public function getNewsAdmin(): array
    {
        $result =  $this->newsModel->get();

        foreach ($result as &$news) {
            $categories = json_decode($news['category_ids'], true);
            $news['categories'] = $this->newsCategoryModel
                ->in('id', $categories)
                ->get();
            $news['images'] = json_decode($news['images'], true);
        }

        return $result;
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
            ->orderBy('date_posted', 'DESC')
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
            5
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
}
