---
title: DocumentationService
description: Programmatic access to documentation
order: 2
---

# DocumentationService

The `DocumentationService` provides programmatic access to your documentation. Use it to build custom tools, integrations, or documentation features beyond the built-in interface.

## Service Overview

The service handles:
- Retrieving individual documents
- Loading navigation structure
- Finding adjacent pages
- Parsing frontmatter
- Extracting table of contents
- Accessing raw markdown content

## Dependency Injection

Inject `DocumentationService` into your classes:

```php
use OiLab\OiLaravelDocumentation\Services\DocumentationService;

class MyController
{
    public function __construct(
        private DocumentationService $documentation
    ) {}

    public function getPage()
    {
        $page = $this->documentation->getDocument('installation');
        // ...
    }
}
```

## Method Reference

### getDocument(slug: string): DocumentPage

Retrieve a single document by slug:

```php
$page = $this->documentation->getDocument('getting-started');

// Returns DocumentPage object
// - id: string
// - title: string
// - description: string
// - section: string
// - url: string
// - content: string (raw markdown)
// - html: string (rendered HTML)
// - frontmatter: array
// - headings: array<Heading>
```

**Example:**

```php
$page = $this->documentation->getDocument('installation');

echo $page->title;           // "Installation"
echo $page->description;     // "Step-by-step guide..."
echo $page->section;         // "Getting Started"
echo $page->url;             // "/documentation/installation"
echo $page->content;         // Raw markdown content
echo $page->html;            // Rendered HTML
```

### getNavigation(): NavigationStructure

Get the complete navigation structure:

```php
$navigation = $this->documentation->getNavigation();

// Returns NavigationStructure with sections and pages
// Each section contains:
// - title: string
// - slug: string
// - description: string
// - order: int
// - pages: array<Page>
```

**Example:**

```php
$navigation = $this->documentation->getNavigation();

foreach ($navigation->sections as $section) {
    echo $section->title;    // "Getting Started"
    echo $section->slug;     // "getting-started"
    echo $section->order;    // 1
    
    foreach ($section->pages as $page) {
        echo $page->title;   // "Installation"
        echo $page->url;     // "/documentation/getting-started/installation"
    }
}
```

### getAdjacentPages(slug: string): ?AdjacentPages

Find the previous and next pages in navigation order:

```php
$adjacent = $this->documentation->getAdjacentPages('installation');

// Returns AdjacentPages object
// - previous: ?Page
// - next: ?Page
```

**Example:**

```php
$adjacent = $this->documentation->getAdjacentPages('installation');

if ($adjacent->previous) {
    echo "Previous: " . $adjacent->previous->title;
    echo "URL: " . $adjacent->previous->url;
}

if ($adjacent->next) {
    echo "Next: " . $adjacent->next->title;
    echo "URL: " . $adjacent->next->url;
}
```

### extractFrontmatter(content: string): array

Parse YAML frontmatter from markdown:

```php
$frontmatter = $this->documentation->extractFrontmatter($markdown);

// Returns array with:
// - title: string
// - description: ?string
// - section: ?string
// - order: ?int
// - (other custom fields)
```

**Example:**

```php
$markdown = <<<'MARKDOWN'
---
title: My Page
description: Page description
order: 1
author: John Doe
---

# Content
MARKDOWN;

$frontmatter = $this->documentation->extractFrontmatter($markdown);

echo $frontmatter['title'];       // "My Page"
echo $frontmatter['description']; // "Page description"
echo $frontmatter['order'];       // 1
echo $frontmatter['author'];      // "John Doe"
```

### extractMarkdown(content: string): string

Extract markdown content after frontmatter:

```php
$markdown = $this->documentation->extractMarkdown($content);

// Returns markdown without frontmatter
```

**Example:**

```php
$fullContent = <<<'CONTENT'
---
title: My Page
---

# Content

This is the content.
CONTENT;

$markdownOnly = $this->documentation->extractMarkdown($fullContent);

echo $markdownOnly;
// Output:
// # Content
// This is the content.
```

### extractTableOfContents(markdown: string): array<Heading>

Extract headings to build a table of contents:

```php
$headings = $this->documentation->extractTableOfContents($markdown);

// Returns array of Heading objects
// Each Heading:
// - level: int (1-6)
// - title: string
// - anchor: string (URL-safe slug)
```

**Example:**

```php
$markdown = <<<'MD'
# Main Title
## Section 1
### Subsection 1.1
## Section 2
MD;

$headings = $this->documentation->extractTableOfContents($markdown);

foreach ($headings as $heading) {
    $indent = str_repeat('  ', $heading->level - 1);
    echo $indent . $heading->title . "\n";
}
// Output:
// Main Title
//   Section 1
//     Subsection 1.1
//   Section 2
```

## Usage Examples

### Example 1: Custom Documentation Controller

Create a custom controller using the service:

```php
// app/Http/Controllers/DocumentationController.php

namespace App\Http\Controllers;

use OiLab\OiLaravelDocumentation\Services\DocumentationService;
use Inertia\Inertia;

class DocumentationController extends Controller
{
    public function __construct(
        private DocumentationService $documentation
    ) {}

    public function show(string $slug)
    {
        $page = $this->documentation->getDocument($slug);
        $navigation = $this->documentation->getNavigation();
        $adjacent = $this->documentation->getAdjacentPages($slug);

        return Inertia::render('Documentation/Show', [
            'page' => $page,
            'navigation' => $navigation,
            'previousPage' => $adjacent->previous,
            'nextPage' => $adjacent->next,
        ]);
    }
}
```

### Example 2: Search Indexing

Build a custom search index:

```php
// app/Console/Commands/RebuildSearchIndex.php

namespace App\Console\Commands;

use OiLab\OiLaravelDocumentation\Services\DocumentationService;
use Illuminate\Console\Command;

class RebuildSearchIndex extends Command
{
    protected $signature = 'search:rebuild';

    public function handle(DocumentationService $documentation)
    {
        $navigation = $documentation->getNavigation();
        $index = [];

        foreach ($navigation->sections as $section) {
            foreach ($section->pages as $page) {
                $document = $documentation->getDocument($page->slug);
                
                $index[] = [
                    'id' => $document->id,
                    'title' => $document->title,
                    'content' => $document->content,
                    'url' => $document->url,
                    'section' => $document->section,
                ];
            }
        }

        // Store index in database or search engine
        SearchIndex::truncate();
        SearchIndex::insert($index);

        $this->info('Search index rebuilt with ' . count($index) . ' documents');
    }
}
```

### Example 3: Documentation Statistics

Generate documentation statistics:

```php
class DocumentationStatsCommand extends Command
{
    public function handle(DocumentationService $documentation)
    {
        $navigation = $documentation->getNavigation();
        
        $stats = [
            'sections' => 0,
            'pages' => 0,
            'words' => 0,
            'code_blocks' => 0,
        ];

        foreach ($navigation->sections as $section) {
            $stats['sections']++;
            
            foreach ($section->pages as $page) {
                $stats['pages']++;
                
                $document = $documentation->getDocument($page->slug);
                
                // Count words
                $stats['words'] += str_word_count($document->content);
                
                // Count code blocks
                $stats['code_blocks'] += substr_count(
                    $document->content,
                    '```'
                ) / 2;
            }
        }

        $this->table(
            ['Metric', 'Count'],
            [
                ['Sections', $stats['sections']],
                ['Pages', $stats['pages']],
                ['Total Words', number_format($stats['words'])],
                ['Code Blocks', $stats['code_blocks']],
            ]
        );
    }
}
```

### Example 4: Documentation Export

Export documentation as JSON:

```php
class ExportDocumentationCommand extends Command
{
    public function handle(DocumentationService $documentation)
    {
        $navigation = $documentation->getNavigation();
        $exported = [];

        foreach ($navigation->sections as $section) {
            $sectionData = [
                'title' => $section->title,
                'slug' => $section->slug,
                'pages' => [],
            ];

            foreach ($section->pages as $page) {
                $document = $documentation->getDocument($page->slug);
                
                $sectionData['pages'][] = [
                    'title' => $document->title,
                    'slug' => $page->slug,
                    'url' => $document->url,
                    'content' => $document->html,
                    'headings' => array_map(
                        fn($h) => [
                            'level' => $h->level,
                            'title' => $h->title,
                        ],
                        $document->headings
                    ),
                ];
            }

            $exported[] = $sectionData;
        }

        file_put_contents(
            storage_path('exports/documentation.json'),
            json_encode($exported, JSON_PRETTY_PRINT)
        );

        $this->info('Documentation exported to storage/exports/documentation.json');
    }
}
```

### Example 5: Documentation Validation

Validate documentation integrity:

```php
class ValidateDocumentationCommand extends Command
{
    public function handle(DocumentationService $documentation)
    {
        $navigation = $documentation->getNavigation();
        $errors = [];

        foreach ($navigation->sections as $section) {
            foreach ($section->pages as $page) {
                try {
                    $document = $documentation->getDocument($page->slug);
                    
                    // Check required fields
                    if (!$document->title) {
                        $errors[] = "Page {$page->slug} missing title";
                    }
                    
                    // Check frontmatter
                    if (empty($document->frontmatter)) {
                        $errors[] = "Page {$page->slug} missing frontmatter";
                    }
                    
                    // Check for broken links
                    $brokenLinks = $this->findBrokenLinks($document->content);
                    if (!empty($brokenLinks)) {
                        $errors[] = "Page {$page->slug} has broken links: " . 
                                    implode(', ', $brokenLinks);
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error processing {$page->slug}: " . $e->getMessage();
                }
            }
        }

        if (empty($errors)) {
            $this->info('Documentation validation passed');
        } else {
            $this->error('Found ' . count($errors) . ' issues:');
            foreach ($errors as $error) {
                $this->line("  - $error");
            }
        }
    }

    private function findBrokenLinks(string $content): array
    {
        // Implementation to find broken links
        return [];
    }
}
```

### Example 6: Table of Contents Generation

Generate a page's table of contents:

```php
public function generateTableOfContents(DocumentationService $documentation)
{
    $document = $documentation->getDocument('my-page');
    $toc = $documentation->extractTableOfContents($document->content);
    
    $html = '<ul>';
    foreach ($toc as $heading) {
        $indent = str_repeat('  ', $heading->level - 1);
        $html .= "$indent<li>";
        $html .= "<a href=\"#{$heading->anchor}\">{$heading->title}</a>";
        $html .= "</li>\n";
    }
    $html .= '</ul>';
    
    return $html;
}
```

## Data Structures

### DocumentPage

```php
class DocumentPage
{
    public string $id;
    public string $title;
    public ?string $description;
    public ?string $section;
    public string $url;
    public string $content;        // Raw markdown
    public string $html;           // Rendered HTML
    public array $frontmatter;     // Parsed YAML
    public array $headings;        // Heading objects
}
```

### NavigationStructure

```php
class NavigationStructure
{
    public array $sections;  // Section objects
}

class Section
{
    public string $title;
    public string $slug;
    public string $description;
    public int $order;
    public array $pages;  // Page objects
}

class Page
{
    public string $title;
    public string $slug;
    public string $url;
    public ?string $description;
}
```

### Heading

```php
class Heading
{
    public int $level;       // 1-6
    public string $title;
    public string $anchor;   // URL-safe slug
}
```

## Error Handling

Handle exceptions when using the service:

```php
try {
    $page = $this->documentation->getDocument('nonexistent');
} catch (DocumentNotFoundException $e) {
    // Handle missing document
    return response()->view('errors.404', [], 404);
} catch (\Exception $e) {
    // Handle other errors
    report($e);
    return response()->view('errors.500', [], 500);
}
```

## Performance Considerations

1. **Cache navigation** - The navigation structure rarely changes
2. **Cache documents** - Cache frequently accessed pages
3. **Lazy load** - Load documents only when needed

```php
// Cache navigation for 1 hour
$navigation = cache()->remember(
    'documentation.navigation',
    now()->addHour(),
    fn() => $this->documentation->getNavigation()
);
```

## Next Steps

1. [View Available Commands](../commands/doc-gen-nav.md) - Command reference
2. [Customize Components](../customization/react-components.md) - UI customization
3. [Getting Started](../getting-started/installation.md) - Installation guide

