<?php

namespace V3\App\Services\Explore;

use V3\App\Models\Explore\Faqs;

class FaqsService
{
    private Faqs $faqs;

    public function __construct(\PDO $pdo)
    {
        $this->faqs = new Faqs($pdo);
    }

    public function addFaq(array $data): int
    {
        return $this->faqs->insert($data);
    }

    public function updateFaq(int $id, array $data): bool
    {
        return (bool) $this->faqs
            ->where('id', $id)
            ->update([
                'author_name' => $data['author_name'],
                'author_id' => $data['author_id'],
                'question' => $data['question'],
                'answer' => $data['answer'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function deleteFaq(int $id): bool
    {
        return (bool) $this->faqs
            ->where('id', $id)
            ->delete();
    }

    public function getAllFaqs(): array
    {
        return $this->faqs
            ->orderBy('id', 'DESC')
            ->get();
    }
}
