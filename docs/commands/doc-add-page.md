---
title: doc:add-page
description: Interactively create new documentation pages
order: 4
---

# doc:add-page

The `doc:add-page` command provides an interactive interface to create new documentation pages. It guides you through selecting a location, entering metadata, and optionally regenerating navigation and search index.

## Command Syntax

```bash
php artisan doc:add-page {--regenerate}
```

## Basic Usage

Run the interactive wizard:

```bash
php artisan doc:add-page
```

The command will prompt you through each step.

## Interactive Steps

### Step 1: Select Section

Choose which section the new page belongs to:

```
Which section should this page be added to?

  [1] Getting Started
  [2] Configuration
  [3] Content
  [4] Commands
```

Select a number (default is 1):
```

The list shows all existing sections in your documentation.

**To create a page in a subsection:**
If you want a page in `guides/common-tasks/`, select the parent section and the file will be created there.

### Step 2: Enter Page Title

Provide the title for your new page:

```
Enter page title:
> Installation Guide
```

This becomes the `title` field in frontmatter.

### Step 3: Enter Page Description

Provide a brief description:

```
Enter page description (optional):
> Step-by-step guide to installing the package
```

This becomes the `description` field in frontmatter. Leave blank to skip.

### Step 4: Enter Filename

Specify the filename (without .md extension):

```
Enter filename (without .md):
> installation-guide
```

The file will be created as `installation-guide.md`.

**Naming conventions:**
- Use lowercase
- Use hyphens for word separation
- No spaces or special characters

### Step 5: Enter Page Order

Set the sort order for this page:

```
Enter page order (default: 999):
> 2
```

Pages with lower order numbers appear first in navigation. Leave blank for default.

## Generated File

After completion, the page is created with frontmatter:

```yaml
---
title: Installation Guide
description: Step-by-step guide to installing the package
order: 2
---

# Installation Guide

Start writing your documentation content here.
```

The file is created in the selected section directory.

## Output Example

```
Creating new documentation page...

✓ Page created: resources/markdown/docs/getting-started/installation-guide.md

Next steps:
  1. Edit the file to add your content
  2. Run: php artisan doc:gen-nav
  3. Run: php artisan doc:gen-index
  4. Visit: /documentation/getting-started/installation-guide
```

## Options

### --regenerate

Automatically regenerate navigation and search index after creating the page:

```bash
php artisan doc:add-page --regenerate
```

This runs in sequence:
1. Creates the page
2. Runs `doc:gen-nav`
3. Runs `doc:gen-index`

Output:
```
Creating new documentation page...
✓ Page created: resources/markdown/docs/getting-started/installation-guide.md
✓ Navigation regenerated
✓ Search index regenerated
```

The page is immediately accessible in navigation and search without running manual commands.

## Workflow Examples

### Create a Simple Page

```bash
php artisan doc:add-page

# Follow prompts:
# 1. Section: Getting Started
# 2. Title: Configuration Steps
# 3. Description: How to configure your application
# 4. Filename: configuration-steps
# 5. Order: 3
```

Page created at:
```
resources/markdown/docs/getting-started/configuration-steps.md
```

Manually regenerate:

```bash
php artisan doc:gen-nav
php artisan doc:gen-index
```

### Create and Immediately Deploy

```bash
php artisan doc:add-page --regenerate
```

Page is created and immediately searchable and navigable.

### Create Multiple Pages

```bash
# Page 1
php artisan doc:add-page --regenerate

# Page 2
php artisan doc:add-page --regenerate

# And so on...
```

Each page automatically regenerates navigation and index.

## Creating Pages in Subsections

To create a page in a subsection like `guides/common-tasks/`:

1. The subsection folder must already exist
2. Select the parent section when prompted
3. Edit the file path after creation if needed

Example workflow:

```bash
# Create section structure manually if needed
mkdir -p resources/markdown/docs/guides/common-tasks
echo '{"type":"section","title":"Common Tasks"}' > resources/markdown/docs/guides/common-tasks/meta.json

# Then create page
php artisan doc:add-page
# Select: Guides section
# Follow remaining prompts
```

Or manually create the file with proper frontmatter:

```markdown
---
title: Using Forms
description: How to handle forms in your application
section: Common Tasks
order: 1
---

# Using Forms

Your content here...
```

## Manual File Creation

You can also create pages manually without the command:

```bash
# Create the file
touch resources/markdown/docs/getting-started/my-page.md

# Add frontmatter and content
cat > resources/markdown/docs/getting-started/my-page.md << 'EOF'
---
title: My Page
description: Page description
order: 5
---

# My Page

Content here...
EOF
```

Then regenerate:

```bash
php artisan doc:gen-nav && php artisan doc:gen-index
```

## Best Practices

1. **Use --regenerate for convenience** - Saves steps and ensures consistency
2. **Descriptive filenames** - Should match the title
3. **Set order explicitly** - Makes navigation predictable
4. **Add descriptions** - Helps in search results
5. **Create sections first** - Ensure parent sections exist before creating pages
6. **Test the page** - Visit the documentation to verify it appears correctly

## Troubleshooting

### "Section not found"

The selected section doesn't exist. Create it first:

```bash
mkdir resources/markdown/docs/my-section
echo '{"type":"section","title":"My Section","order":5}' > resources/markdown/docs/my-section/meta.json
```

Then run `doc:add-page` again.

### File already exists

The filename you specified already exists:

```
✗ File already exists: resources/markdown/docs/getting-started/installation.md
```

Solution: Use a different filename.

### Page not appearing after creation

Without `--regenerate`, you must manually run:

```bash
php artisan doc:gen-nav
php artisan doc:gen-index
```

Or use the flag next time:

```bash
php artisan doc:add-page --regenerate
```

### Invalid frontmatter generated

Check that your inputs are valid:
- Title should be non-empty
- Description can be empty
- Order should be a number

Re-run the command if inputs were incorrect.

## Editing Created Pages

After creation, edit the markdown file directly:

```bash
# Edit the file
nano resources/markdown/docs/getting-started/installation-guide.md

# After editing, regenerate if needed
php artisan doc:gen-nav && php artisan doc:gen-index
```

## Integration with Workflows

Use in automated workflows:

```bash
#!/bin/bash
# Create multiple pages
php artisan doc:add-page --regenerate << 'EOF'
1
Getting Started
Getting started with the package
getting-started
1
EOF

php artisan doc:add-page --regenerate << 'EOF'
1
Next Steps
What to do after installation
next-steps
2
EOF
```

## Related Commands

After creating pages, you might need:

```bash
# Regenerate navigation manually
php artisan doc:gen-nav

# Regenerate search index manually
php artisan doc:gen-index

# Add pages from packages
php artisan doc:import
```

## Next Steps

After creating pages:

1. [Edit your page](../content/markdown.md) - Add markdown content
2. [View in navigation](./doc-gen-nav.md) - Regenerate navigation
3. [Make searchable](./doc-gen-index.md) - Regenerate search index

