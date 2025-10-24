<?php return array (
  0 => 
  array (
    'method' => 'GET',
    'path' => '/public/books',
    'class' => 'V3\\App\\Controllers\\Explore\\BookController',
    'methodName' => 'index',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  1 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/exams',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtController',
    'methodName' => 'getAllExams',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  2 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/exams/{examTypeId:\\d+}/courses',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtController',
    'methodName' => 'getCoursesByExamType',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  3 => 
  array (
    'method' => 'GET',
    'path' => '/public/cbt/exams/{exam_id:\\d+}/questions',
    'class' => 'V3\\App\\Controllers\\Explore\\CbtController',
    'methodName' => 'getQuestions',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  4 => 
  array (
    'method' => 'GET',
    'path' => '/public/for-you',
    'class' => 'V3\\App\\Controllers\\Explore\\ForYouController',
    'methodName' => 'forYou',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  5 => 
  array (
    'method' => 'GET',
    'path' => '/public/games',
    'class' => 'V3\\App\\Controllers\\Explore\\GameController',
    'methodName' => 'index',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  6 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/all',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getAllMovies',
    'middleware' => 
    array (
    ),
  ),
  7 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/category/{cat}',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getByCategory',
    'middleware' => 
    array (
    ),
  ),
  8 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/genres/{id:\\d+}',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getByGenre',
    'middleware' => 
    array (
    ),
  ),
  9 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/genres',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getAllGenres',
    'middleware' => 
    array (
    ),
  ),
  10 => 
  array (
    'method' => 'GET',
    'path' => '/public/movies/shorts',
    'class' => 'V3\\App\\Controllers\\Explore\\MovieController',
    'methodName' => 'getAllShorts',
    'middleware' => 
    array (
    ),
  ),
  11 => 
  array (
    'method' => 'GET',
    'path' => '/public/news',
    'class' => 'V3\\App\\Controllers\\Explore\\NewsController',
    'methodName' => 'index',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
  12 => 
  array (
    'method' => 'GET',
    'path' => '/public/key-buddy/content',
    'class' => 'V3\\App\\Controllers\\Explore\\TemporalController',
    'methodName' => 'getContent',
    'middleware' => 
    array (
    ),
  ),
  13 => 
  array (
    'method' => 'GET',
    'path' => '/public/videos',
    'class' => 'V3\\App\\Controllers\\Explore\\VideoController',
    'methodName' => 'index',
    'middleware' => 
    array (
      0 => 'api',
    ),
  ),
);