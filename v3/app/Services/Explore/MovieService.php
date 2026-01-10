<?php

namespace V3\App\Services\Explore;

class MovieService
{
    private array $data;

    public function __construct()
    {
        $this->data = MoviesData::MOVIES;
    }

    public function getSampleMoviesPerCategory(int $limit = 10): array
    {
        $result = [];
        foreach ($this->data['categories'] as $category => $ids) {
            $movies = array_slice($ids, 0, $limit);
            $result[$category] = array_map(fn($id) => $this->data['movies'][$id] ?? null, $movies);
        }
        return $result;
    }

    public function getHotMovies(): array
    {
        return array_map(fn($id) => $this->data['movies'][$id] ?? null, $this->data['categories']['hot']);
    }

    public function getMoviesByCategory(string $category): array
    {
        if (!isset($this->data['categories'][$category])) return [];

        return array_map(fn($id) => $this->data['movies'][$id] ?? null, $this->data['categories'][$category]);
    }

    public function getMoviesByGenre(int $genre): array
    {
        if (!isset($this->data['genres'][$genre])) return [];

        return array_map(fn($id) => $this->data['movies'][$id] ?? null, $this->data['genres'][$genre]);
    }

    public function getAll(): array
    {
        return $this->data['movies'];
    }

    public function getGenres()
    {
        return $this->data['genre_names'];
    }

    public function getShorts()
    {
        return $this->data['shorts'];
    }
}
