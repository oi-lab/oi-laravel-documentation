---
title: doc:gen-nav
description: Generate navigation structure from documentation files
order: 2
---

# doc:gen-nav

The `doc:gen-nav` command scans your documentation directory and generates a navigation structure based on file organization and metadata.

## Command Syntax

```bash
php artisan doc:gen-nav
```

## What It Does

The command performs these operations:

1. **Scans the documentation directory** - Recursively finds all markdown files
2. **Reads meta.json files** - Extracts section metadata (title, description, order)
3. **Parses frontmatter** - Extracts page titles and metadata from markdown
4. **Builds navigation tree** - Organizes pages into hierarchical sections
5. **Generates navigation.json** - Outputs the navigation structure file

## Running the Command

Basic usage:

```bash
php artisan doc:gen-nav
```

Output:
```
Scanning documentation directory...
Found 3 sections, 12 pages
✓ Navigation generated: resources/markdown/docs/navigation.json
```

## Generated navigation.json

The command creates `resources/markdown/docs/navigation.json`:

```json
{
    "sections": [
        {
            "title": "Getting Started",
            "slug": "getting-started",
            "description": "Installation and setup guide",
            "order": 1,
            "pages": [
                {
                    "title": "Introduction",
                    "slug": "getting-started",
                    "description": "Welcome to OI Laravel Documentation",
                    "url": "/documentation/getting-started"
                },
                {
                    "title": "Installation",
                    "slug": "installation",
                    "description": "Step-by-step installation and setup guide",
                    "url": "/documentation/getting-started/installation"
                }
            ]
        },
        {
            "title": "Configuration",
            "slug": "configuration",
            "description": "Routes, paths, and search settings",
            "order": 2,
            "pages": [
                {
                    "title": "Configuration",
                    "slug": "configuration",
                    "description": "Configure paths, routes, and search settings",
                    "url": "/documentation/configuration"
                }
            ]
        }
    ]
}
```

## Navigation Structure

### Sections

Each section represents a directory in your docs folder:

```json
{
    "title": "Getting Started",           // From meta.json title
    "slug": "getting-started",            // Auto-derived from folder name
    "description": "Installation guide",  // From meta.json description
    "order": 1,                          // From meta.json order
    "pages": [...]                       // Array of pages in section
}
```

### Pages

Pages within sections:

```json
{
    "title": "Installation",              // From markdown frontmatter
    "slug": "installation",               // Auto-derived from filename
    "description": "Setup instructions",  // From markdown frontmatter
    "url": "/documentation/getting-started/installation"  // Full route
}
```

## When to Regenerate

Run this command when you:

1. **Add new sections** - Create a new folder with markdown files
2. **Remove sections** - Delete a section folder
3. **Add pages** - Create new markdown files
4. **Delete pages** - Remove markdown files
5. **Change meta.json** - Update section metadata
6. **Rename files/folders** - Change file or folder names
7. **Update frontmatter** - Change page titles or descriptions
8. **Change order values** - Modify sorting in meta.json

## Example Workflow

### Adding a New Page

1. Create the markdown file:
   ```bash
   touch resources/markdown/docs/getting-started/troubleshooting.md
   ```

2. Add content with frontmatter:
   ```yaml
   ---
   title: Troubleshooting
   description: Common issues and solutions
   order: 3
   ---
   ```

3. Regenerate navigation:
   ```bash
   php artisan doc:gen-nav
   ```

4. The page now appears in navigation and is accessible

### Adding a New Section

1. Create the directory:
   ```bash
   mkdir resources/markdown/docs/advanced
   ```

2. Create meta.json:
   ```json
   {
       "type": "section",
       "title": "Advanced",
       "description": "Advanced topics",
       "order": 5
   }
   ```

3. Create _index.md:
   ```yaml
   ---
   title: Advanced
   description: Advanced topics and techniques
   order: 1
   ---
   ```

4. Regenerate:
   ```bash
   php artisan doc:gen-nav
   ```

## Navigation Output Location

The command outputs to:

```
resources/markdown/docs/navigation.json
```

This location is configured in `config/oi-documentation.php`:

```php
'navigation_file' => 'navigation.json',
```

Change the path if needed:

```php
'navigation_file' => 'custom-nav.json',  // resources/markdown/docs/custom-nav.json
```

## Nested Sections

The command handles nested sections:

```
docs/
├── guides/
│   ├── meta.json
│   ├── _index.md
│   ├── common-tasks/
│   │   ├── meta.json
│   │   ├── _index.md
│   │   └── installation.md
│   └── advanced/
│       ├── meta.json
│       ├── _index.md
│       └── optimization.md
```

Generates nested structure in navigation.json.

## Troubleshooting

### "Section has no meta.json"

Warning: A directory lacks meta.json:

```
⚠ Section "guides" has no meta.json
  Create docs/guides/meta.json to enable this section
```

Solution: Add meta.json:

```bash
echo '{"type":"section","title":"Guides","order":3}' > docs/guides/meta.json
```

Then regenerate:

```bash
php artisan doc:gen-nav
```

### Missing Pages in Navigation

Pages might be hidden if:
- Section folder lacks meta.json
- Page lacks a title in frontmatter
- File isn't in a valid section

Check the console output for warnings.

### Wrong Order

Ensure order values are numbers in meta.json:

```json
✓ "order": 1        // Correct

✗ "order": "1"      // Incorrect (string)
```

### Navigation.json Is Empty

Verify:
1. Documentation files exist
2. Sections have meta.json
3. Pages have frontmatter with title

Run with debug output:

```bash
php artisan doc:gen-nav --verbose
```

## Automation

Run automatically in CI/CD pipelines:

```bash
# GitHub Actions
- name: Generate Documentation Navigation
  run: php artisan doc:gen-nav
```

Or as a git hook:

```bash
#!/bin/bash
# .git/hooks/post-merge
php artisan doc:gen-nav
```

## Performance

For large documentation sets:

- Command completes in milliseconds
- No performance overhead
- Safe to run frequently

## Important Notes

**Do not manually edit navigation.json** — It's regenerated every time the command runs. Any manual changes will be overwritten.

Use meta.json and frontmatter to control navigation structure, not the JSON file directly.

## Integration with Other Commands

Related commands that work with navigation:

```bash
# Generate search index (uses navigation)
php artisan doc:gen-index

# Add a new page (can regenerate)
php artisan doc:add-page --regenerate

# Both steps
php artisan doc:gen-nav && php artisan doc:gen-index
```

## Next Steps

After generating navigation:

1. [Generate Search Index](./doc-gen-index.md) - Index pages for searching
2. [View Documentation](../getting-started/installation.md) - Visit the docs
3. [Add Pages](./doc-add-page.md) - Create more documentation

