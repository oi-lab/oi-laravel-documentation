# Markdown & Link Transformation

Content is CommonMark + GitHub-flavored markdown, rendered via `react-markdown`
+ `remark-gfm`, with Shiki syntax highlighting for code blocks.

## What works

- Headings `#`–`######` (use `##` and below in body; reserve H1 for the title)
- Bold `**x**`, italic `*x*`, strikethrough `~~x~~`
- Inline code `` `x` `` and fenced code blocks (always tag the language)
- Ordered / unordered / nested lists
- Task lists `- [x]` / `- [ ]`
- Blockquotes `>` (nestable)
- Tables (GFM, with `:` alignment)
- Horizontal rules `---`
- Images `![alt](path)`
- Limited inline HTML (`<strong>`, `<em>`) — avoid complex HTML
- HTML comments `<!-- ... -->` (preserved, not rendered)

## Code blocks

Always specify a language so Shiki highlights it. Shiki supports 100+ languages
(php, typescript, javascript, bash, json, yaml, python, sql, …).

````markdown
```php
$config = config('oi-laravel-documentation');
echo $config['docs_path'];
```
````

Untagged blocks render as plain text with no highlighting.

## Link transformation (important)

Relative `.md` links are automatically rewritten to documentation routes.
**Use relative `.md` paths for internal links** — not pre-built URLs.

```markdown
[Installation](./installation.md)          → /documentation/getting-started/installation
[Configuration](../configuration/_index.md) → /documentation/configuration
[Endpoints](/documentation/api/endpoints)   → kept as-is (already absolute)
[Laravel](https://laravel.com)              → kept as-is (external)
```

- Internal page links: relative path **with** the `.md` extension and a leading
  `./` or `../`. A bare `installation.md` (no `./`) is not transformed reliably.
- Link to a section homepage via its `_index.md`.
- Anchors within a page: `[Jump](#installation)` linking to `## Installation`.

## Images

```markdown
![Screenshot](./images/screenshot.png)   # relative to the file
![Logo](/img/logo.svg)                    # from the public/ directory
![Remote](https://example.com/x.png)      # absolute URL
```

## Search relevance (write with this in mind)

The search index scores matches by location, so put key terms where they count:

| Where the term appears | Points per match |
|------------------------|------------------|
| `title` | +10 |
| `description` | +5 |
| Heading (`##`, `###`, …) | +3 |
| Body content | +1 |

Practical takeaway: give pages a precise `title`, a keyword-rich `description`,
and clear descriptive headings — not just body prose.

## Authoring best practices

1. Proper heading hierarchy — don't skip levels (`##` → `####` is wrong).
2. One conceptual H1 per page (the title); start body sections at `##`.
3. Descriptive link text — `[installation guide](...)`, not `[click here](...)`.
4. Always tag code fences with a language.
5. Use tables sparingly (hard to read on mobile).
6. Separate blocks with blank lines.
7. Escape literal markdown chars with `\` when needed.
