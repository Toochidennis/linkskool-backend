<?php

namespace V3\App\Controllers\Explore;

use V3\App\Services\Explore\MovieService;
use V3\App\Utilities\ResponseHandler;

class MovieController
{
    private MovieService $movieService;
    private array $response = ['success' => true];

    public function __construct()
    {
        $this->movieService = new MovieService();
    }

    public function getAllMovies()
    {
        $this->response['data'] = $this->movieService->getAll();
        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getHot()
    {
        $this->response['data'] = $this->movieService->getHotMovies();
        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getByCategory(array $vars)
    {
        $this->response['data'] = $this->movieService->getMoviesByCategory(strtolower(($vars['cat'])));
        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getByGenre(array $vars)
    {
        $this->response['data'] = $this->movieService->getMoviesByGenre($vars['id']);
        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getSampleFromAllCategories()
    {
        $this->response['data'] = $this->movieService->getSampleMoviesPerCategory(15);
        ResponseHandler::sendJsonResponse($this->response);
    }

    public function getAllGenres()
    {
        $this->response['data'] = $this->movieService->getGenres();
        ResponseHandler::sendJsonResponse($this->response);
    }
}
