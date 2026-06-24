<?php

return [

    // Durée de mise en cache des réponses des APIs externes (secondes).
    'cache_ttl' => (int) env('EXTERNAL_CACHE_TTL', 86400),

    'openlibrary' => [
        'base_url' => env('OPENLIBRARY_BASE_URL', 'https://openlibrary.org'),
    ],

    'tmdb' => [
        'api_key' => env('TMDB_API_KEY'),
        'base_url' => env('TMDB_BASE_URL', 'https://api.themoviedb.org/3'),
        'image_base_url' => env('TMDB_IMAGE_BASE_URL', 'https://image.tmdb.org/t/p/w500'),
    ],

    'rawg' => [
        'api_key' => env('RAWG_API_KEY'),
        'base_url' => env('RAWG_BASE_URL', 'https://api.rawg.io/api'),
    ],

];
