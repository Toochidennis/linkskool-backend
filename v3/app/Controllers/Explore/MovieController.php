<?php

namespace V3\App\Controllers\Explore;

use V3\App\Services\Explore\MovieService;

class MovieController
{
    public function getMovies()
    {
        $service = new MovieService();
        $movies = $service->movies();
        echo json_encode(['success' => true, 'movies' => $movies]);
    }
}
