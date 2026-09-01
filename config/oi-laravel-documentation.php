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
    'docs_path' => 'resources/markdown/docs',

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
    | Merge Output Directory
    |--------------------------------------------------------------------------
    |
    | The directory where `doc:merge` writes the single merged markdown file.
    | This is relative to the base_path() of your Laravel application and
    | defaults to the private storage disk so the file is not web-accessible.
    | The filename is derived from the project name (e.g. "my-package.md").
    |
    */
    'merge_output_directory' => 'storage/app/private/docs',

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

    /*
    |--------------------------------------------------------------------------
    | Rendering Configuration
    |--------------------------------------------------------------------------
    |
    | Configured during installation.
    |
    | - "markdown_engine": "client" renders markdown to React with
    |   ReactMarkdown, giving you syntax highlighting, Mermaid diagrams and
    |   copy buttons. "server" converts markdown to HTML in Laravel (via
    |   league/commonmark) and renders it with DocumentationHtmlContent
    |   instead of DocumentationMarkdownContent — simpler and SSR-friendly,
    |   but without the client-only interactive features.
    | - "ssr": whether the host application renders the Inertia app with
    |   server-side rendering (resources/js/ssr.tsx).
    | - "typeset": apply Shadcn UI's "typeset" typography class to the
    |   rendered content container instead of "typography". Requires
    |   resources/css/typeset.css to exist.
    |
    */
    'rendering' => [
        'markdown_engine' => 'client',
        'ssr' => false,
        'typeset' => false,
    ],
];
