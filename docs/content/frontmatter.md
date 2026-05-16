---
title: Frontmatter
description: YAML frontmatter fields and formatting
order: 3
---

# Frontmatter

Every markdown file must include YAML frontmatter at the top. This metadata controls how pages appear in navigation, search results, and other features.

## Frontmatter Syntax

Frontmatter is YAML wrapped in `---` delimiters:

```markdown
---
title: My Page Title
description: A brief description
order: 1
---

# Content starts here
This is your markdown content.
```

The frontmatter block must be:
- At the very beginning of the file (before any content)
- Wrapped in exactly three hyphens on separate lines: `---`
- Valid YAML syntax
- Followed by at least one blank line before content

## Frontmatter Fields

### title (required)

The page title displayed in navigation and page headers:

```yaml
---
title: Installation Guide
---
```

### description (optional)

A brief description shown in search results and listings:

```yaml
---
title: Installation Guide
description: Step-by-step guide to install the package
---
```

Use 1-2 sentences. This appears in:
- Search result previews
- Section metadata
- Documentation listings

### section (optional)

Explicitly specify which section this page belongs to:

```yaml
---
title: Installation
section: Getting Started
---
```

When omitted, the section is auto-inferred from the folder structure:
- `docs/getting-started/installation.md` → section: "Getting Started"

Use this field only when you need to override the auto-detection.

### order (optional)

Control sort order within a section:

```yaml
---
title: Installation
order: 2
---
```

Pages sort by:
1. Explicit `order` value (lower first)
2. Filename number prefix (if present)
3. Alphabetical by title (default)

Default order is `999` if omitted.

### Special: order from Filename

Extract order automatically from numbered filenames:

```
01-getting-started.md      → order: 1
02-installation.md         → order: 2
03-configuration.md        → order: 3
```

The number is extracted even without frontmatter `order` field.

## Complete Frontmatter Example

Here's a complete example with all fields:

```yaml
---
title: Advanced Configuration
description: Configure advanced settings for production environments
section: Configuration
order: 5
---

# Advanced Configuration

Your markdown content here...
```

## The _index.md Pattern

The `_index.md` file in a section folder becomes the section's homepage:

```yaml
---
title: Getting Started
description: Begin your journey with our documentation
order: 1
---

# Getting Started

Welcome! This page is the homepage for the Getting Started section.
```

When you visit `/documentation/getting-started`, you're viewing this file.

## YAML Formatting Guidelines

### String Values

Use quotes when strings contain special characters:

```yaml
✓ title: "Getting Started"
✓ description: "Install & configure your app"
✓ title: "Why We Use Colons: A Guide"

✗ title: Getting Started    # OK if no special chars
✗ description: Install & configure  # Fails: & needs quotes
```

### Special Characters

Escape special YAML characters with quotes:

```yaml
# Correct
title: "Using # Symbols in Code"
description: "Questions? Check the FAQ"
order: 1

# Incorrect - will cause parsing errors
title: Using # Symbols in Code
description: Questions? Check the FAQ
```

### Reserved YAML Words

Quote values that match YAML keywords:

```yaml
✓ section: "default"
✓ title: "true"
✓ description: "yes"

✗ section: default          # Parsed as boolean
✗ title: true              # Parsed as boolean
✗ description: yes         # Parsed as boolean
```

### Numbers vs Strings

Order should be a number, not a string:

```yaml
✓ order: 1        # Number
✓ order: 10

✗ order: "1"      # String (still works but unnecessary)
```

## Frontmatter Extraction

The package extracts frontmatter using a YAML parser. The extracted values are:

- `title` - Page title (string)
- `description` - Page description (string)
- `section` - Section name (string)
- `order` - Sort order (number)

Additional fields are ignored but preserved in the source file. You can add custom fields:

```yaml
---
title: Installation
description: How to install
order: 1
author: John Doe
last-updated: 2024-01-15
custom-field: custom-value
---
```

The custom fields won't be used by the package but can be useful for tracking.

## Validation and Errors

### Missing Required Title

Error: Pages must have a title

```yaml
---
description: No title here
---
```

Solution: Add a title field:

```yaml
---
title: Page Title
description: No title here
---
```

### Invalid YAML

Error: Invalid YAML in frontmatter

```yaml
---
title: My Page
order: [1, 2, 3]  # Wrong: array instead of number
---
```

Solution: Use correct types:

```yaml
---
title: My Page
order: 1
---
```

### Unclosed Frontmatter

Error: Frontmatter must be wrapped in --- delimiters

```markdown
---
title: My Page
description: Missing closing delimiter

# Content starts here
```

Solution: Add closing delimiter:

```markdown
---
title: My Page
description: Missing closing delimiter
---

# Content starts here
```

## Real-World Examples

### Simple Getting Started Page

```yaml
---
title: Installation
description: Install the package using Composer
order: 1
---

# Installation

Install via Composer:

```bash
composer require oi-lab/oi-laravel-documentation
```
```

### API Reference Page

```yaml
---
title: create() Method
section: API Reference
description: Create a new documentation instance
order: 2
---

# create() Method

Creates a new documentation instance.

```php
$docs = Documentation::create($config);
```
```

### FAQ Section Homepage

```yaml
---
title: Frequently Asked Questions
description: Common questions and answers
order: 10
---

# Frequently Asked Questions

Find answers to common questions below.
```

### Numbered Filename Example

```yaml
---
# 01-introduction.md
title: Introduction
description: Getting started with our product
---
```

The `01-` prefix sets order automatically even without `order: 1`.

## Best Practices

1. **Always include title** - Required field, must be present
2. **Use meaningful descriptions** - Helps with search and navigation
3. **Set order explicitly** - Makes sorting predictable
4. **Use quotes liberally** - Prevents YAML parsing errors
5. **Keep titles concise** - 2-5 words typically
6. **Descriptions 1-2 sentences** - Provide context without being verbose
7. **Don't include HTML** - Frontmatter is metadata only
8. **Maintain consistency** - Use similar title styles across sections

## Troubleshooting

### Page Not Appearing in Navigation

Check:
1. Does the file have valid frontmatter?
2. Does it have a `title`?
3. Does the section have a `meta.json`?

Solution: Regenerate navigation:

```bash
php artisan doc:gen-nav
```

### Wrong Section Assignment

If the page appears in the wrong section:

```yaml
# Problem: System inferred wrong section
---
title: Installation
---

# Solution: Explicitly set section
---
title: Installation
section: Getting Started
---
```

### Frontmatter Not Parsing

Ensure valid YAML formatting:

```bash
# Validate with a YAML linter
# https://www.yamllint.com/
```

Or test locally:

```php
$yaml = <<<'YAML'
title: My Page
order: 1
YAML;

$parsed = yaml_parse($yaml);
```

### Characters Appearing as Escape Sequences

If you see `\#` or `\?` in titles:

```yaml
✗ title: What's the best way?  # Fails
✓ title: "What's the best way?"  # Works
```

Always quote titles with special characters.
