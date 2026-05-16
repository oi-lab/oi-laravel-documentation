---
title: doc:import
description: Import documentation from Laravel packages
order: 5
---

# doc:import

The `doc:import` command discovers and imports documentation from installed Laravel packages. Packages can include their own documentation that gets merged into your site.

## Command Syntax

```bash
php artisan doc:import {--force} {--regenerate}
```

## What It Does

The command performs these operations:

1. **Scans vendor directory** - Searches for packages with documentation
2. **Detects package docs** - Looks for `docs/meta.json` files
3. **Shows available packages** - Displays a selection menu
4. **Copies documentation** - Imports selected package docs
5. **Optionally regenerates** - Updates navigation and search index

## How It Works

### Package Discovery

Scans `vendor/*/*/docs/meta.json` for packages with documentation. A valid package documentation structure:

```
vendor/package-vendor/package-name/
├── docs/
│   ├── meta.json                    # Required package metadata
│   ├── _index.md                    # Section homepage
│   ├── getting-started/
│   │   ├── meta.json
│   │   └── _index.md
│   └── ...
└── ...
```

### Package meta.json Format

Packages must have a properly formatted `docs/meta.json`:

```json
{
    "type": "package",
    "name": "vendor/package-name",
    "title": "Package Documentation",
    "description": "Description of the package",
    "order": 50
}
```

Required fields:
- `type` - Must be `"package"`
- `name` - Package identifier (vendor/name format)
- `title` - Display name
- `description` - Brief description
- `order` - Sort order in documentation menu

## Running the Command

Basic usage:

```bash
php artisan doc:import
```

Output shows available packages:

```
Scanning for package documentation...

Found 3 packages with documentation:

  [1] vendor/api-package
      A comprehensive API documentation package

  [2] vendor/ui-components
      UI component library documentation

  [3] vendor/database-tools
      Database tools and migrations

Select packages to import (comma-separated) [1,2,3]:
> 1,2
```

## Selection and Import

### Selecting Packages

Choose packages to import by number:

```
Select packages to import (comma-separated) [1,2,3]:
> 1
```

Select multiple:

```
> 1,2,3
```

Or all (press Enter with default):

```
> [1,2,3]
```

### Import Process

For each selected package:

```
Importing "vendor/api-package"...
✓ Documentation copied to docs/api-package/
✓ 5 pages imported
```

Documentation is copied to:

```
resources/markdown/docs/{slug}/
```

Where `{slug}` is derived from the package name:
- `vendor/api-package` → `docs/api-package/`
- `vendor/user-management` → `docs/user-management/`

## Options

### --force

Skip confirmation prompts and overwrite existing documentation:

```bash
php artisan doc:import --force
```

Use when:
- Updating documentation from packages
- Running in CI/CD pipelines
- Re-importing without confirmation

### --regenerate

Automatically regenerate navigation and search index after importing:

```bash
php artisan doc:import --regenerate
```

Or combine options:

```bash
php artisan doc:import --force --regenerate
```

This runs in sequence:
1. Imports selected packages
2. Runs `doc:gen-nav`
3. Runs `doc:gen-index`

Imported documentation is immediately accessible.

## Complete Workflow Example

### Step 1: Install Package

```bash
composer require vendor/api-package
```

### Step 2: Import Documentation

```bash
php artisan doc:import

# Output:
# Found 1 package with documentation
# 
# [1] vendor/api-package
#     A comprehensive API documentation package
#
# Select packages to import [1]:
# > 1

# Importing "vendor/api-package"...
# ✓ Documentation copied to docs/api-package/
# ✓ 5 pages imported
```

### Step 3: Regenerate (if not using --regenerate)

```bash
php artisan doc:gen-nav
php artisan doc:gen-index
```

### Step 4: View Documentation

Visit `/documentation/api-package` to see the imported docs.

## Package Documentation Structure

When creating a package with documentation:

### Package Directory Layout

```
vendor/my-package/
├── composer.json
├── src/
├── docs/
│   ├── meta.json                      # Root package metadata
│   ├── _index.md                      # Section homepage
│   ├── getting-started/
│   │   ├── meta.json                  # Section metadata
│   │   ├── _index.md
│   │   └── installation.md
│   ├── guides/
│   │   ├── meta.json
│   │   ├── _index.md
│   │   └── working-with-data.md
│   └── api/
│       ├── meta.json
│       ├── _index.md
│       └── endpoints.md
└── ...
```

### Package meta.json Example

```json
{
    "type": "package",
    "name": "vendor/my-package",
    "title": "My Package Documentation",
    "description": "Complete guide to using My Package",
    "version": "1.0.0",
    "order": 50
}
```

### Section meta.json Example

Each section in the package docs:

```json
{
    "type": "section",
    "title": "Getting Started",
    "description": "Installation and setup",
    "order": 1
}
```

## Creating a Package with Documentation

To include documentation in your own package:

### 1. Create docs directory

```bash
mkdir -p packages/my-package/docs
```

### 2. Add root meta.json

```json
{
    "type": "package",
    "name": "vendor/my-package",
    "title": "My Package",
    "description": "Documentation for My Package",
    "order": 50
}
```

### 3. Create sections

```bash
mkdir packages/my-package/docs/getting-started
echo '{"type":"section","title":"Getting Started","order":1}' > \
  packages/my-package/docs/getting-started/meta.json
```

### 4. Add pages

```markdown
---
title: Installation
order: 1
---

# Installation

Installation instructions...
```

### 5. Users can now import

```bash
php artisan doc:import
```

## Imported Documentation Location

After import, documentation appears at:

```
docs/
├── my-app-docs/           # Original documentation
│   └── ...
├── api-package/           # Imported from vendor/api-package
│   ├── meta.json
│   ├── _index.md
│   └── endpoints.md
└── ui-components/         # Imported from vendor/ui-components
    ├── meta.json
    └── ...
```

URL structure:
- Original: `/documentation/my-app-docs`
- Imported package 1: `/documentation/api-package`
- Imported package 2: `/documentation/ui-components`

## Updating Package Documentation

### Scenario 1: Update Package Version

```bash
# Update the package
composer update vendor/api-package

# Re-import with --force
php artisan doc:import --force --regenerate
```

This overwrites the previously imported docs with the new version.

### Scenario 2: Remove Package Documentation

Simply delete the imported folder:

```bash
rm -rf resources/markdown/docs/api-package

# Regenerate navigation
php artisan doc:gen-nav
php artisan doc:gen-index
```

## Troubleshooting

### "No packages with documentation found"

The scanned packages don't have a valid `docs/meta.json`. Check:

1. Package has `vendor/package-name/docs/meta.json`
2. File contains valid JSON with `"type": "package"`
3. All required fields are present

### Overwrite Confirmation

When importing already-imported packages:

```
"api-package" documentation already exists at docs/api-package/
Overwrite? [y/N]:
> y
```

Use `--force` to skip this:

```bash
php artisan doc:import --force
```

### Import Fails with Permission Error

Ensure write permissions to `resources/markdown/docs/`:

```bash
chmod -R u+w resources/markdown/docs/
```

### Documentation Not Appearing

After import without `--regenerate`, manually regenerate:

```bash
php artisan doc:gen-nav && php artisan doc:gen-index
```

## Best Practices

1. **Use --regenerate** - Ensures navigation is updated
2. **Verify package structure** - Before publishing a package
3. **Test on fresh install** - Verify package docs import correctly
4. **Document your packages** - Provide helpful docs to users
5. **Use meaningful order values** - Controls menu position

## CI/CD Integration

Automate documentation imports in pipelines:

```yaml
# GitHub Actions
- name: Import Package Documentation
  run: php artisan doc:import --force --regenerate
```

```bash
# Docker entry point
php artisan doc:import --force --regenerate
npm run build
```

## Related Commands

After importing:

```bash
# View imported documentation
# Visit /documentation/{package-slug}

# Manually regenerate if not using --regenerate
php artisan doc:gen-nav
php artisan doc:gen-index

# Create your own pages
php artisan doc:add-page
```

## Next Steps

After importing documentation:

1. [View imported docs](../getting-started/installation.md) - Visit the documentation
2. [Create your own pages](./doc-add-page.md) - Add custom documentation
3. [Customize components](../customization/react-components.md) - Style the documentation site

