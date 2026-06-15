# Frontmatter

Every markdown page (including `_index.md`) must start with YAML frontmatter
wrapped in `---` delimiters, followed by a blank line.

```markdown
---
title: Advanced Configuration
description: Configure advanced settings for production environments
order: 5
---

# Advanced Configuration

Content starts here...
```

Rules: the block must be at the very top of the file, valid YAML, wrapped in
exactly `---` on their own lines, and followed by a blank line before content.

## Fields

| Field | Required | Type | Notes |
|-------|----------|------|-------|
| `title` | **Yes** | string | Shown in navigation and page header. A page with no title is dropped from navigation. |
| `description` | No | string | 1–2 sentences. Appears in search results and listings — strongly recommended for searchability. |
| `section` | No | string | Override the auto-inferred section (normally derived from folder). Only set when overriding. |
| `order` | No | number | Sort within the section (lower first). Default `999`. |

Extra custom fields (`author`, `last-updated`, etc.) are allowed and preserved
but ignored by the package.

## H1 vs frontmatter title

The frontmatter `title` drives navigation. In the body, start headings at `##`
or keep a single `#` matching the title — never use multiple `#` H1s. H1 is
conceptually the page title.

## YAML gotchas (these cause silent parse failures)

Quote any string containing special characters. Common breakers:

```yaml
✓ title: "Install & configure your app"     ✗ title: Install & configure
✓ title: "Why Colons: A Guide"              ✗ title: Why Colons: A Guide
✓ title: "What's the best way?"             ✗ title: What's the best way?
✓ description: "Using # symbols"            ✗ description: Using # symbols
```

Quote values that look like YAML booleans/keywords, or they parse as `true`/`false`:

```yaml
✓ section: "default"   ✓ title: "true"   ✓ description: "yes"
✗ section: default     ✗ title: true     ✗ description: yes
```

`order` is a number, not a string or array:

```yaml
✓ order: 1
✗ order: "1"          # works but pointless
✗ order: [1, 2, 3]    # parse error
```

Rule of thumb: when in doubt, quote string values; never quote `order`.

## Numbered filenames set order automatically

```
01-introduction.md   → order 1
02-installation.md   → order 2
```

The prefix sets order even without an `order` field. Use either explicit
`order` or numeric prefixes consistently — don't mix within a section.
