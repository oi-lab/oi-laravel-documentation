<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Documentation Path
    |--------------------------------------------------------------------------
    |
    | The base path where your documentation files are stored.
    | This is relative to the base_path() of your Laravel application.
    |
    */
    'docs_path' => 'resources/docs',

    /*
    |--------------------------------------------------------------------------
    | Navigation File
    |--------------------------------------------------------------------------
    |
    | The path to the navigation JSON file that is auto-generated.
    | This is relative to the docs_path.
    |
    */
    'navigation_file' => 'navigation.json',

    /*
    |--------------------------------------------------------------------------
    | Search Index File
    |--------------------------------------------------------------------------
    |
    | The path to the search index JSON file that is auto-generated.
    | This is relative to the docs_path.
    |
    */
    'search_index_file' => 'search-index.json',

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the routing behavior for documentation pages.
    |
    */
    'route' => [
        'enabled' => true,
        'prefix' => 'documentation',
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    |
    | Configure search behavior.
    |
    */
    'search' => [
        'min_query_length' => 2,
        'excerpt_length' => 150,
        'excerpt_context' => 50,
    ],
];
