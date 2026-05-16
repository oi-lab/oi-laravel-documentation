---
title: meta.json Files
description: Understanding metadata configuration
order: 2
---

# meta.json Files

The `meta.json` files provide metadata that controls how the documentation package discovers, organizes, and displays your content. There are three types: root, section, and subsection.

## Root meta.json

Located at `docs/meta.json`, this defines the entire documentation package:

```json
{
    "type": "package",
    "name": "oi-lab/oi-laravel-documentation",
    "title": "OI Laravel Documentation",
    "description": "Markdown-based documentation with navigation, search, and React components",
    "version": "1.0.0"
}
```

### Root meta.json Fields

| Field | Type | Required | Purpose |
|-------|------|----------|---------|
| `type` | string | Yes | Must be `"package"` |
| `name` | string | Yes | Package identifier (vendor/package format) |
| `title` | string | Yes | Display name for the documentation |
| `description` | string | Yes | Short description of the documentation |
| `version` | string | No | Documentation version number |

### Root Properties

- The root `meta.json` is not displayed in navigation
- Used for package identification and documentation imports
- `name` field determines the slug when importing via `doc:import`
- Only one root `meta.json` should exist per documentation set

### Example Root meta.json

```json
{
    "type": "package",
    "name": "laravel/framework",
    "title": "Laravel Framework",
    "description": "The Laravel application framework",
    "version": "11.0.0"
}
```

## Section meta.json

Located in each section folder (`getting-started/meta.json`, `configuration/meta.json`, etc.), these define navigation sections:

```json
{
    "type": "section",
    "title": "Getting Started",
    "description": "Installation and setup guide",
    "order": 1
}
```

### Section meta.json Fields

| Field | Type | Required | Purpose |
|-------|------|----------|---------|
| `type` | string | Yes | Must be `"section"` |
| `title` | string | Yes | Section display name |
| `description` | string | No | Description shown in navigation |
| `order` | number | No | Sort order (default: 999) |

### Section Properties

- Every section folder must have a `meta.json`
- Without it, the section is skipped with a warning
- The `title` is displayed in the navigation menu
- The `order` field controls how sections sort

### Missing meta.json Warning

If a section folder lacks `meta.json`, you'll see:

```
⚠ Section "guides" has no meta.json
  Create docs/guides/meta.json to enable this section
```

To fix:

```json
{
    "type": "section",
    "title": "Guides",
    "order": 3
}
```

## Subsection meta.json

Nested folders within sections also require `meta.json` with the same format:

```
docs/
├── meta.json                          # Root package
├── getting-started/
│   ├── meta.json                      # Section
│   └── _index.md
└── guides/
    ├── meta.json                      # Section
    ├── _index.md
    ├── common-tasks/
    │   ├── meta.json                  # Subsection
    │   ├── _index.md
    │   └── working-with-data.md
    └── advanced/
        ├── meta.json                  # Subsection
        ├── _index.md
        └── optimization.md
```

Subsections use the same format as sections:

```json
{
    "type": "section",
    "title": "Common Tasks",
    "description": "Step-by-step guides for everyday tasks",
    "order": 1
}
```

## The order Field

The `order` field controls sorting in navigation. Lower numbers appear first:

```json
{
    "type": "section",
    "title": "Getting Started",
    "order": 1              // Appears first
}
```

```json
{
    "type": "section",
    "title": "Configuration",
    "order": 2              // Appears second
}
```

```json
{
    "type": "section",
    "title": "Advanced",
    "order": 10             // Appears last
}
```

### Default Ordering

If `order` is omitted, sections default to order `999`:

```json
{
    "type": "section",
    "title": "Uncategorized"
    // order defaults to 999
}
```

Sections with explicit order appear before those with default order.

### Ordering Example

Given these sections:

```json
// getting-started/meta.json
{"type": "section", "title": "Getting Started", "order": 1}

// configuration/meta.json
{"type": "section", "title": "Configuration", "order": 2}

// faq/meta.json
{"type": "section", "title": "FAQ"}  // order defaults to 999

// advanced/meta.json
{"type": "section", "title": "Advanced", "order": 10}
```

Navigation order:
1. Getting Started (order 1)
2. Configuration (order 2)
3. Advanced (order 10)
4. FAQ (order 999)

## Description Field

The `description` field provides context in navigation or listings:

```json
{
    "type": "section",
    "title": "Getting Started",
    "description": "Installation and initial setup guide",
    "order": 1
}
```

This description may appear:
- In section listings
- As a preview/tooltip
- In generated navigation.json
- In documentation frontmatter

## Validation

The package validates `meta.json` files. Common validation errors:

### Missing Required Fields

```json
{
    "title": "Getting Started"
    // Missing: type
}
```

Error: `meta.json is missing required field: type`

### Invalid Type

```json
{
    "type": "invalid",
    "title": "Getting Started"
}
```

Error: `type must be "package" or "section"`

### Invalid Order

```json
{
    "type": "section",
    "title": "Getting Started",
    "order": "first"    // Should be number
}
```

Error: `order must be a number`

## Best Practices

### 1. Consistent Formatting

Keep your JSON properly formatted and consistent:

```json
{
    "type": "section",
    "title": "Getting Started",
    "description": "Installation and setup guide",
    "order": 1
}
```

### 2. Descriptive Titles

Use clear, descriptive section titles:

```json
✓ {"title": "Getting Started"}
✓ {"title": "API Reference"}
✗ {"title": "Section 1"}
✗ {"title": "Stuff"}
```

### 3. Meaningful Descriptions

Add helpful descriptions that guide users:

```json
✓ {"description": "Installation and initial configuration steps"}
✗ {"description": "About this section"}
✗ {"description": "Info"}
```

### 4. Logical Ordering

Use consistent numbering schemes:

```json
// Option 1: Sequential
{"order": 1}, {"order": 2}, {"order": 3}

// Option 2: Gaps (easier to insert later)
{"order": 10}, {"order": 20}, {"order": 30}
```

### 5. Every Section Needs meta.json

Don't skip creating `meta.json` files. It prevents warnings and ensures proper organization:

```bash
# After creating a new section
mkdir docs/tutorials
echo '{"type":"section","title":"Tutorials","order":5}' > docs/tutorials/meta.json
```

## Troubleshooting meta.json

### Section Not Appearing in Navigation

Check:
1. Does the folder have `meta.json`?
2. Is `type` set to `"section"`?
3. Is `title` a non-empty string?

### Invalid JSON Error

Validate your JSON:

```bash
# Using jq (if installed)
jq empty docs/guides/meta.json

# Or use an online validator
# https://jsonlint.com
```

### Sections Out of Order

Check `order` values in all `meta.json` files:

```bash
grep -r '"order"' docs/*/meta.json
```

### Documentation Shows as Section Not Found

Regenerate navigation:

```bash
php artisan doc:gen-nav
```

This rebuilds `navigation.json` from your `meta.json` files.
