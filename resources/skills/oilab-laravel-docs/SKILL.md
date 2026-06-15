---
name: oilab-laravel-docs
description: "Write and organize documentation for the oi-lab/oi-laravel-documentation package — markdown pages, meta.json, frontmatter, navigation, and search. Use when adding or editing documentation in a Laravel project that uses this package (docs under resources/markdown/docs/), or when authoring importable docs inside a Composer package's docs/ folder. Triggers on: doc:add-page, doc:gen-nav, doc:gen-index, doc:import, meta.json sections, documentation frontmatter, or writing docs for this package."
---

# Authoring OI Laravel Documentation

The `oi-lab/oi-laravel-documentation` package renders a documentation site from
markdown files plus `meta.json` metadata. Navigation and search are
**auto-generated** from the directory structure — there is no manual nav config.
This skill covers writing correct, well-structured, searchable content for it.

## When to use

- Adding or editing documentation pages in a project that uses this package.
- Creating sections / restructuring the docs tree.
- Authoring docs **inside a Composer package** so they can be imported into a
  host app via `doc:import`.

## Step 1 — Locate the docs root

Two modes (format is identical, only the root differs):

- **App docs**: read `docs_path` in `config/oi-laravel-documentation.php`
  (default `resources/markdown/docs/`). If the config isn't published, the
  default applies.
- **Package docs** (importable): the package's own `docs/` folder, with a root
  `meta.json` of `type: "package"` whose `name` becomes the import slug.

Confirm the package is present (`composer show oi-lab/oi-laravel-documentation`
or a `docs_path` config). If neither exists, ask the user which mode they want.

## Step 2 — Understand the structure before writing

```
<docs root>/
├── meta.json          # root, type:"package" (required once; not in nav)
├── navigation.json    # AUTO-GENERATED — never hand-edit
├── search-index.json  # AUTO-GENERATED — never hand-edit
└── <section>/
    ├── meta.json      # type:"section" (required, else section is skipped)
    ├── _index.md      # section homepage → /documentation/<section>
    └── <page>.md      # → /documentation/<section>/<page>
```

Every folder (section or nested subsection) needs a `meta.json`. URLs derive
from folder/file names. Read **references/structure.md** for the full layout,
meta.json field tables, nesting, ordering, and naming rules.

## Step 3 — Write the content

Each page is markdown with required YAML frontmatter:

```markdown
---
title: Installation
description: Step-by-step guide to installing the package
order: 2
---

# Installation

Content here. Link internally with relative `.md` paths: [intro](./_index.md).
```

- **Frontmatter**: `title` is required; `description` and `order` strongly
  recommended (description feeds search). Quote strings with special chars.
  Full rules + YAML gotchas → **references/frontmatter.md**.
- **Markdown**: CommonMark + GFM, Shiki code blocks (always tag the language),
  automatic relative-`.md` link transformation. Search scores titles/headings
  higher than body — write descriptive titles and headings. Details →
  **references/markdown.md**.

Templates to copy in `assets/`: `page-template.md`, `section-meta.json`,
`root-meta.json`.

### Adding a new section

1. Create the folder.
2. Add `meta.json` (`type: "section"`, `title`, `order`) — copy `assets/section-meta.json`.
3. Add `_index.md` as the section homepage.
4. Add pages.

### Creating a page via the artisan command (app mode, optional)

`php artisan doc:add-page` runs an interactive wizard (section → title →
description → filename → order). Add `--regenerate` to rebuild nav + index
automatically. Manual file creation + the regenerate step below is equally valid
and usually faster when scripting multiple pages.

## Step 4 — Regenerate (app mode only)

After **any** structural or content change to app docs, regenerate the
generated JSON:

```bash
php artisan doc:gen-nav    # rebuilds navigation.json from folders + meta.json
php artisan doc:gen-index  # rebuilds search-index.json from page content
# or both:
php artisan doc:gen-nav && php artisan doc:gen-index
```

Run `doc:gen-nav` after adding/removing/renaming files or folders or editing
`meta.json`/frontmatter; run `doc:gen-index` after content changes. New pages
will not appear in navigation or search until these run.

> Package-mode docs are **not** regenerated in the package — the host app runs
> `doc:gen-nav`/`doc:gen-index` after importing them with `doc:import`.

If the user reports the change isn't visible in the browser, frontend assets may
need rebuilding (`npm run build` / `npm run dev`) — ask before assuming.

## Common mistakes to avoid

- Hand-editing `navigation.json` / `search-index.json` (overwritten on regen).
- Missing `meta.json` in a section folder (section silently skipped).
- Missing frontmatter `title` (page dropped from navigation).
- Unquoted YAML values with `:`, `#`, `?`, `&`, or boolean-like words.
- Capitalized or underscored folder/file names (leak into URLs).
- Forgetting to regenerate after changes (app mode).
- Internal links without the `./` prefix or `.md` extension (not transformed).
