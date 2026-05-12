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
    | Components Path
    |--------------------------------------------------------------------------
    |
    | The directory (relative to the base path) where the published React
    | components for the documentation UI are installed. Keep this under
    | "resources/js/" so the components can keep using the "@/" import alias.
    |
    */
    'components_path' => 'resources/js/components/documentation',

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
