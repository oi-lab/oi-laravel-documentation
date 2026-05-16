---
title: Importing Package Docs
description: Add documentation to your packages
order: 1
---

# Importing Package Docs

OI Laravel Documentation supports importing documentation from packages. If you're building a Laravel package, you can include documentation that users will automatically import with `php artisan doc:import`.

## Creating Package Documentation

To add documentation to your Laravel package:

### Step 1: Create docs Directory

In your package root, create the documentation directory:

```bash
mkdir -p docs
```

### Step 2: Create Package meta.json

Create `docs/meta.json` with package metadata:

```json
{
    "type": "package",
    "name": "vendor/package-name",
    "title": "Package Documentation Title",
    "description": "Description of your package",
    "version": "1.0.0",
    "order": 50
}
```

### Required Fields

| Field | Type | Required | Purpose |
|-------|------|----------|---------|
| `type` | string | Yes | Must be `"package"` |
| `name` | string | Yes | Package identifier (vendor/name) |
| `title` | string | Yes | Display name |
| `description` | string | Yes | Brief description |
| `version` | string | No | Package version |
| `order` | number | No | Sort order in docs menu |

### Step 3: Create Section Structure

Create sections within your documentation:

```
docs/
├── meta.json
├── _index.md                          # Package homepage
├── getting-started/
│   ├── meta.json
│   ├── _index.md
│   └── installation.md
├── configuration/
│   ├── meta.json
│   ├── _index.md
│   └── settings.md
└── guides/
    ├── meta.json
    ├── _index.md
    └── common-tasks.md
```

### Step 4: Create Section meta.json Files

Each section needs `meta.json`:

```json
{
    "type": "section",
    "title": "Getting Started",
    "description": "Installation and setup",
    "order": 1
}
```

### Step 5: Write Documentation Pages

Create markdown files with frontmatter:

```markdown
---
title: Installation
description: How to install the package
order: 1
---

# Installation

Install via Composer:

```bash
composer require vendor/package-name
```

## Installation Steps

1. Install the package
2. Run setup command
3. Configure settings
```

## Complete Package Example

Example package documentation structure:

```
vendor/api-client/
├── composer.json
├── src/
│   └── ApiClient.php
├── docs/
│   ├── meta.json
│   ├── _index.md
│   ├── getting-started/
│   │   ├── meta.json
│   │   ├── _index.md
│   │   └── installation.md
│   ├── api-reference/
│   │   ├── meta.json
│   │   ├── _index.md
│   │   ├── authentication.md
│   │   └── endpoints.md
│   └── guides/
│       ├── meta.json
│       ├── _index.md
│       └── advanced-usage.md
└── ...
```

### Package meta.json

```json
{
    "type": "package",
    "name": "vendor/api-client",
    "title": "API Client",
    "description": "Comprehensive PHP client for REST APIs",
    "version": "2.0.0",
    "order": 50
}
```

### Section meta.json

```json
{
    "type": "section",
    "title": "Getting Started",
    "description": "Installation and basic usage",
    "order": 1
}
```

### Page Example

```markdown
---
title: Installation
description: Install the API Client package
order: 1
---

# Installation

Install the package using Composer:

```bash
composer require vendor/api-client
```

## Configure API Key

Set your API key in `.env`:

```
API_CLIENT_KEY=your_api_key_here
```

## Next Steps

See [Authentication](../api-reference/authentication.md) for API key setup.
```

## Slug Generation

When users import your package, the documentation slug is derived from the package name:

| Package Name | Documentation Slug |
|--------------|-------------------|
| `vendor/api-client` | `api-client` |
| `vendor/auth-guard` | `auth-guard` |
| `laravel/horizon` | `horizon` |

Users access imported documentation at:

```
/documentation/{slug}/
```

Example: Package `vendor/api-client` is accessible at `/documentation/api-client/`

## Version-Specific Documentation

Handle different versions of your package:

```markdown
---
title: Configuration
description: Configure the package
version: ">=2.0"
order: 2
---

# Configuration (v2.0+)

This documentation applies to version 2.0 and above.

## Breaking Changes

If upgrading from v1.x, see [Upgrade Guide](../migration.md).
```

## Organizing Documentation

### By Feature

```
docs/
├── database/
├── caching/
├── authentication/
└── queues/
```

### By Audience

```
docs/
├── user-guide/
├── developer-guide/
├── api-reference/
└── admin-panel/
```

### By Topic Type

```
docs/
├── getting-started/
├── tutorials/
├── reference/
├── how-to/
└── troubleshooting/
```

## Best Practices

1. **Clear structure** - Organize logically for your users
2. **Minimal nesting** - Keep 2-3 levels maximum
3. **Complete documentation** - Include all features
4. **Examples** - Provide code examples
5. **Table of contents** - Help users navigate
6. **API reference** - Document public APIs
7. **Update regularly** - Keep docs in sync with code

## Documentation Standards

### File Naming

Use lowercase with hyphens:

```
✓ getting-started.md
✓ api-reference.md
✗ GettingStarted.md
✗ API_Reference.md
```

### Frontmatter Fields

Always include:

```yaml
---
title: Page Title
description: Brief description
order: 1
---
```

### Markdown Formatting

Use consistent heading structure:

```markdown
# Page Title (h1)

## Section 1 (h2)

### Subsection 1.1 (h3)

Some content here.
```

## Including Code Examples

Show configuration and usage:

```markdown
## Installation

```bash
composer require vendor/package
```

## Basic Usage

```php
use Vendor\Package\Client;

$client = new Client();
$response = $client->get('/endpoint');
```

## Configuration

Set in `config/package.php`:

```php
return [
    'key' => env('PACKAGE_KEY'),
    'debug' => env('APP_DEBUG'),
];
```
```

## Linking Between Sections

Use relative links:

```markdown
[Installation Guide](../getting-started/installation.md)
[API Reference](../api-reference/_index.md)
[Troubleshooting](../help/troubleshooting.md)
```

## Adding Images

Include images in your documentation:

```bash
# Create images directory
mkdir docs/images

# Add your images
# docs/images/setup-flow.png
# docs/images/api-diagram.png
```

Reference in markdown:

```markdown
![Setup Flow](./images/setup-flow.png)
![API Diagram](../images/api-diagram.png)
```

## Testing Documentation

Verify your documentation structure:

```bash
# Check meta.json is valid JSON
jq empty docs/meta.json

# Check all sections have meta.json
find docs -type d -exec test -f {}'/meta.json' \; -o -print

# Check all markdown files have frontmatter
find docs -name '*.md' -exec grep -l '^---' {} \;
```

## Documentation Updates

When releasing new versions:

1. **Update version** in `docs/meta.json`:
   ```json
   "version": "2.1.0"
   ```

2. **Add version notes** to relevant pages:
   ```markdown
   > **Added in v2.1**: This feature was introduced in version 2.1
   ```

3. **Update examples** to reflect current API

4. **Create migration guides** for breaking changes

## Integration with Release Process

Add documentation generation to your release workflow:

```bash
#!/bin/bash
# Release script

# Update version in docs
sed -i "s/\"version\": \".*\"/\"version\": \"$VERSION\"/" docs/meta.json

# Commit changes
git add docs/meta.json
git commit -m "docs: update version to $VERSION"

# Tag release
git tag v$VERSION
git push origin v$VERSION
```

## Discoverability

Improve documentation discovery:

1. **Write comprehensive README** - Link to docs
2. **Add docs link to composer.json**:
   ```json
   {
       "docs": "https://your-site.com/documentation/your-package"
   }
   ```
3. **Include documentation badge** in README:
   ```markdown
   [![Docs](https://img.shields.io/badge/docs-latest-green)](...)
   ```

## Accessibility

Ensure documentation is accessible:

1. **Use semantic HTML** - Proper heading hierarchy
2. **Alt text for images** - Describe all images
3. **Color contrast** - Sufficient contrast ratios
4. **Keyboard navigation** - Support keyboard users
5. **Descriptive links** - No "click here" links

## Performance

Optimize documentation performance:

1. **Optimize images** - Compress before including
2. **Limit code examples** - Keep examples focused
3. **Structure clearly** - Good navigation reduces load

## Publishing Package

When publishing to Packagist:

1. Create `docs/meta.json` with proper structure
2. Ensure all sections have required metadata
3. Test with `doc:import` command
4. Document in your README

Packagist users can then import with:

```bash
composer require vendor/package-name
php artisan doc:import
```

## Validation Checklist

Before publishing:

- [ ] `docs/meta.json` is valid JSON
- [ ] `type` is set to `"package"`
- [ ] All sections have `meta.json`
- [ ] All pages have frontmatter with title
- [ ] All links are relative paths
- [ ] All images are included in docs/
- [ ] Documentation is up-to-date with code
- [ ] Examples compile and run
- [ ] Grammar and spelling checked
- [ ] Headings are hierarchical (h1 only in frontmatter)

## Next Steps

1. [Import your package](./doc-import.md) - See how users import docs
2. [Write Markdown](../content/markdown.md) - Learn markdown syntax
3. [View Generator Commands](../commands/doc-gen-nav.md) - Understand generation

