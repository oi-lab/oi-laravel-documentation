# OiLab Laravel Documentation

A Laravel package for managing markdown-based documentation with hierarchical navigation and full-text search.

## Features

- **Markdown-based** - Write documentation in simple markdown files
- **Hierarchical navigation** - Organize content with sections and subsections
- **Full-text search** - Search across all documentation with scoring
- **Auto-generated navigation** - Build navigation from file structure and frontmatter
- **YAML frontmatter** - Add metadata to documentation pages
- **Table of contents** - Automatically extract headings
- **Link transformation** - Convert relative markdown links to route URLs
- **Adjacent page navigation** - Previous/Next page links
- **React components** - Pre-built Inertia.js + React components for documentation UI
- **Syntax highlighting** - Code blocks with Shiki syntax highlighting
- **Interactive installation** - User-friendly installation wizard

## Requirements

- PHP 8.2 or higher
- Laravel 11.x or 12.x
- Inertia.js with React
- Node.js and npm

## Installation

### 1. Install via Composer

```bash
composer require oi-lab/oi-laravel-documentation
```

### 2. Run the Installation Wizard

```bash
php artisan doc:install
```

The interactive installation wizard will:
- ✓ Publish the configuration file to `config/oi-documentation.php`
- ✓ Publish routes to `routes/documentation.php`
- ✓ Create `resources/docs/` with sample documentation
- ✓ Install React components (layouts, pages, components)
- ✓ Detect and install missing npm packages
- ✓ Guide you through the setup process

**Installation Options:**
- Use `--force` to overwrite existing files
- Skip individual steps if already installed
- Choose which files to overwrite when conflicts occur

## Configuration

The configuration file `config/oi-documentation.php` allows you to customize:

```php
return [
    // Path to documentation files
    'docs_path' => 'resources/docs',

    // Navigation and search index filenames
    'navigation_file' => 'navigation.json',
    'search_index_file' => 'search-index.json',

    // Route configuration
    'route' => [
        'enabled' => true,
        'prefix' => 'documentation',
        'middleware' => ['web'],
    ],

    // Search settings
    'search' => [
        'min_query_length' => 2,
        'excerpt_length' => 150,
        'excerpt_context' => 50,
    ],
];
```

## Documentation Structure

### Directory Layout

```
resources/docs/
├── meta.json                    # Root metadata
├── navigation.json              # Auto-generated navigation
├── search-index.json            # Auto-generated search index
└── getting-started/
    ├── meta.json                # Section metadata
    ├── _index.md                # Section introduction
    └── installation.md          # Page
```

### Section meta.json

Each section directory should contain a `meta.json` file:

```json
{
    "title": "Getting Started",
    "description": "Introduction and basic concepts",
    "order": 1,
    "type": "section"
}
```

### Markdown Frontmatter

Each markdown file should have YAML frontmatter:

```markdown
---
title: Installation
description: How to install and configure
section: getting-started
order: 2
---

# Installation

Your content here...
```

### 3. Register Routes

Add the documentation routes to your `bootstrap/app.php`:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
    then: function () {
        Route::middleware('web')
            ->group(base_path('routes/documentation.php'));
    },
)
```

### 4. Build Frontend Assets

After installation, build your frontend assets:

```bash
npm run build
# or for development
npm run dev
```

## NPM Dependencies

The package requires these npm packages (automatically installed by the wizard):

```json
{
  "@inertiajs/react": "^2.0.0",
  "react-markdown": "^9.0.0",
  "remark-gfm": "^4.0.0",
  "rehype-raw": "^7.0.0",
  "rehype-sanitize": "^6.0.0",
  "slugify": "^1.6.6",
  "shiki": "^1.0.0",
  "lucide-react": "^0.460.0"
}
```

## Commands

### Generate Navigation

Generate the navigation structure from your documentation files:

```bash
php artisan doc:gen-nav
```

This scans `resources/docs/` and creates `navigation.json`.

### Generate Search Index

Generate the search index for full-text search:

```bash
php artisan doc:gen-index
```

This creates `search-index.json` from all documentation pages.

### Install Documentation

Install the package with the interactive wizard:

```bash
php artisan doc:install

# Force overwrite existing files
php artisan doc:install --force
```

## Routes

The package registers these routes (with configurable prefix):

```php
GET /documentation              # Index page
GET /documentation/search       # Search endpoint
GET /documentation/{slug}       # Show documentation page
```

## Usage in Controllers

Inject the `DocumentationService`:

```php
use OiLab\LaravelDocumentation\Services\DocumentationService;

class CustomController extends Controller
{
    public function __construct(
        public DocumentationService $documentationService
    ) {}

    public function index()
    {
        $navigation = $this->documentationService->getNavigation();
        $document = $this->documentationService->getDocument('getting-started');
        $adjacent = $this->documentationService->getAdjacentPages('getting-started');

        // ...
    }
}
```

## React Components

The installation wizard automatically installs these React components:

### Components (`resources/js/components/`)
- `documentation-markdown-content.tsx` - Renders markdown with syntax highlighting
- `documentation-navigation.tsx` - Hierarchical navigation sidebar
- `documentation-search.tsx` - Search interface with live results
- `documentation-toc.tsx` - Table of contents sidebar
- `heading.tsx`, `heading-large.tsx`, `heading-small.tsx`, `heading-xsmall.tsx` - Typography components

### Layouts (`resources/js/layouts/`)
- `documentation-layout.tsx` - Main documentation layout wrapper

### Pages (`resources/js/pages/documentation/`)
- `index.tsx` - Documentation homepage
- `show.tsx` - Documentation page viewer

### Props Available

**Index page:**
```typescript
{
    navigation: NavigationStructure
}
```

**Show page:**
```typescript
{
    document: {
        frontmatter: Record<string, any>,
        markdown: string,
        tableOfContents: Array<{level: number, title: string, slug: string}>
    },
    navigation: NavigationStructure,
    slug: string,
    previousPage: PageItem | null,
    nextPage: PageItem | null
}
```

**Note:** The components require shadcn/ui components. Make sure you have the following components installed:
- `button`
- `input`
- `separator`

## Workflow

1. Write markdown files in `resources/docs/`
2. Add `meta.json` files for sections
3. Run `php artisan doc:gen-nav` to generate navigation
4. Run `php artisan doc:gen-index` to generate search index
5. Access documentation at `/documentation`

## Link Transformation

The package automatically transforms relative markdown links:

```markdown
[Installation](installation.md)
```

Becomes:

```markdown
[Installation](/documentation/getting-started/installation)
```

## Search

The search endpoint scores results based on:

- Title match: +10 points
- Description match: +5 points
- Heading match: +3 points
- Content match: +1 point

Results are sorted by score and include contextual excerpts.

## License

MIT

## Credits

Created by OiLab
