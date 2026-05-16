---
title: doc:gen-index
description: Generate search index from documentation content
order: 3
---

# doc:gen-index

The `doc:gen-index` command generates a full-text search index from your documentation. It extracts titles, descriptions, headings, and content to enable fast, relevant searching.

## Command Syntax

```bash
php artisan doc:gen-index
```

## What It Does

The command performs these operations:

1. **Loads navigation structure** - Reads the generated navigation.json
2. **Iterates all pages** - Processes every page in the documentation
3. **Extracts content** - Pulls title, description, content, and headings
4. **Calculates scores** - Assigns relevance weights to different elements
5. **Generates index** - Creates search-index.json with all searchable content

## Running the Command

Basic usage:

```bash
php artisan doc:gen-index
```

Output:
```
Generating search index...
Processing documentation pages...
✓ 12 pages indexed
✓ Search index generated: resources/markdown/docs/search-index.json
```

## Generated search-index.json

The command creates `resources/markdown/docs/search-index.json`:

```json
{
    "documents": [
        {
            "id": "getting-started",
            "title": "Getting Started",
            "section": "Getting Started",
            "description": "Installation and setup guide",
            "url": "/documentation/getting-started",
            "content": "OI Laravel Documentation is a comprehensive package...",
            "headings": [
                "What You Get",
                "Requirements",
                "Quick Start"
            ],
            "score": 0
        },
        {
            "id": "installation",
            "title": "Installation",
            "section": "Getting Started",
            "description": "Step-by-step installation and setup guide",
            "url": "/documentation/getting-started/installation",
            "content": "Follow these steps to install and configure...",
            "headings": [
                "Step 1: Install the Package",
                "Step 2: Run the Installation Wizard",
                "Step 3: Register Routes"
            ],
            "score": 0
        }
    ]
}
```

## Search Index Structure

### Document Fields

Each searchable document in the index contains:

| Field | Type | Purpose |
|-------|------|---------|
| `id` | string | Unique page identifier |
| `title` | string | Page title from frontmatter |
| `section` | string | Section name |
| `description` | string | Page description |
| `url` | string | Full documentation URL |
| `content` | string | Plain text page content |
| `headings` | array | All heading text from page |
| `score` | number | Search relevance (calculated at query time) |

## Search Scoring Algorithm

When a user searches, the package scores documents based on where matches occur. Higher scores appear first:

```
Title match       → +10 points per match
Description match → +5 points per match
Heading match     → +3 points per match
Content match     → +1 point per match
```

### Scoring Example

Searching for "installation":

```
Page: "Installation Guide"
- Title contains "installation" → +10
- Description "...installation steps..." → +5
- Heading "Step 1: Installation" → +3
- Content mentions "installation" 8 times → +8

Total Score: 26 points
```

Another page with "installation" only in content (5 mentions):

```
Total Score: 5 points
```

The first page ranks higher due to the title and description matches.

## When to Regenerate

Run this command when you:

1. **Change page content** - Update markdown content
2. **Update page titles** - Change frontmatter title
3. **Modify descriptions** - Update frontmatter description
4. **Add new pages** - Create new documentation files
5. **Delete pages** - Remove documentation files
6. **Change headings** - Update heading text in content

Typically after any content changes.

## Workflow: Updating Documentation

1. Edit markdown file:
   ```bash
   # Edit docs/guides/getting-started.md
   ```

2. Regenerate search index:
   ```bash
   php artisan doc:gen-index
   ```

3. Changes are now searchable immediately

## Search Index Output Location

The command outputs to:

```
resources/markdown/docs/search-index.json
```

Configured in `config/oi-documentation.php`:

```php
'search_index_file' => 'search-index.json',
```

Change if needed:

```php
'search_index_file' => 'custom-index.json',
```

## Search Behavior

### Minimum Query Length

Controlled by configuration:

```php
'search' => [
    'min_query_length' => 2,  // Require at least 2 characters
],
```

Set in `config/oi-documentation.php`.

### Excerpt Generation

When returning results, excerpts are generated with context:

```php
'search' => [
    'excerpt_length' => 150,     // Show 150 characters
    'excerpt_context' => 50,     // 50 chars before/after match
],
```

Example result:
```
"...installation wizard that guides you through configuration:

1. **Publish the configuration file** to `config/oi-documentation.php`
2. **Prompt for middleware settings**..."
```

## Troubleshooting

### Search Index is Empty

Verify:
1. Navigation has been generated:
   ```bash
   php artisan doc:gen-nav
   ```

2. Markdown files exist with valid frontmatter:
   ```yaml
   ---
   title: Page Title
   ---
   ```

3. Try regenerating with verbose output:
   ```bash
   php artisan doc:gen-index --verbose
   ```

### Missing Pages in Search

Check:
1. Page has a title in frontmatter
2. Page is in navigation (run `doc:gen-nav` first)
3. Run `doc:gen-index` again

### Stale Search Results

The index is static. After content changes, regenerate:

```bash
php artisan doc:gen-index
```

Or combine commands:

```bash
php artisan doc:gen-nav && php artisan doc:gen-index
```

### Search Not Finding Content

Ensure:
1. Search query is at least 2 characters
2. Index has been regenerated recently
3. Content contains the search terms

Check `config/oi-documentation.php` search settings.

## Automation

Run in CI/CD pipelines:

```bash
# Build documentation in CI
php artisan doc:gen-nav
php artisan doc:gen-index
```

Or create a single command:

```bash
# Make a custom command
php artisan make:command RegenerateDocs
```

```php
// app/Console/Commands/RegenerateDocs.php
public function handle()
{
    $this->call('doc:gen-nav');
    $this->call('doc:gen-index');
    $this->info('Documentation regenerated!');
}
```

Then use:

```bash
php artisan regenerate-docs
```

## Integration with Content Generation

When using `doc:add-page --regenerate`:

```bash
php artisan doc:add-page --regenerate
```

This automatically regenerates both navigation and search index.

## Performance

For large documentation sets:

- Index generation: Fast (< 1 second for 1000+ pages)
- Index size: ~1-2MB for typical documentation
- Search performance: Instant (client-side)

## Important Notes

**Never manually edit search-index.json** — It's regenerated by the command. Manual edits are lost on next regeneration.

Use markdown content and frontmatter to control what's searchable.

## Complete Workflow

For a complete documentation update:

```bash
# 1. Add/edit markdown files
# 2. Regenerate navigation
php artisan doc:gen-nav

# 3. Regenerate search index
php artisan doc:gen-index

# 4. Rebuild front-end assets
npm run build

# 5. Visit documentation
# Changes are now live!
```

Or use a combined command:

```bash
php artisan doc:gen-nav && php artisan doc:gen-index && npm run build
```

## Next Steps

After generating the search index:

1. [Test Search](../customization/search.md) - Test the search functionality
2. [View Documentation](../getting-started/installation.md) - Visit your docs
3. [Monitor Updates](../commands/doc-import.md) - Keep docs current

