<?php return array (
  0 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/all',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getAllMovies',
    'middleware' => 
    array (
    ),
  ),
  1 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/category/{cat}',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getByCategory',
    'middleware' => 
    array (
    ),
  ),
  2 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/genres/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getByGenre',
    'middleware' => 
    array (
    ),
  ),
  3 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/genres',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getAllGenres',
    'middleware' => 
    array (
    ),
  ),
  4 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/shorts',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getAllShorts',
    'middleware' => 
    array (
    ),
  ),
  5 => 
  array (
    'method' => 'GET',
    'path' => '/public/key-buddy/content',
    'class' => 'V3\\App\\Controllers\\Explore\\TemporalController',
    'methodName' => 'getContent',
    'middleware' => 
    array (
    ),
  ),
);