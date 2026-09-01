# Changelog

All notable changes to `oi-lab/oi-laravel-documentation` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Installer-configurable rendering options under a new `rendering` config block: `markdown_engine` (`client` via ReactMarkdown, or `server` via a new `league/commonmark`-powered conversion in `DocumentationService`), `ssr` (whether the host app renders with Inertia SSR), and `typeset` (apply Shadcn UI's `typeset` typography class instead of `typography`). `doc:install` now prompts for all three and persists them to the config file. `DocumentationService::getDocument()` includes an `html` field when the server engine is selected, and the new `DocumentationHtmlContent` component (always published alongside `DocumentationMarkdownContent`) renders it — `show.tsx` switches between the two at runtime based on whether `document.html` is present. Both components read their container class from a new shared `DOCUMENTATION_TYPOGRAPHY_CLASS` constant (`resources/js/lib/documentation-typography.ts`), which `doc:install` sets to `typography` or `typeset`.
- `index.tsx` and `show.tsx` page stubs now declare their breadcrumbs via a static `.layout` property (Inertia persistent layout convention) instead of wrapping their JSX in `DocumentationLayout`, and import route helpers from `@/routes/dashboard` and `@/routes/documentation`.
- Mermaid diagram support in the documentation renderer. Fenced ` ```mermaid ` code blocks are rendered to SVG (with automatic light/dark theming) instead of being syntax-highlighted as text. `mermaid` is now a required JavaScript package, and the `oilab-laravel-docs` AI skill mandates Mermaid as the only diagram syntax.
- Extended markdown authoring in `documentation-markdown-content.tsx`: callout blocks (`i>` info / `x>` danger) and inline `<icon name="…" />` (lucide-react), plus fixed table-column widths via a `::width=<length>` header directive. These are powered by new remark plugins (`remark-callouts`, `remark-table-column`) shipped in `stubs/js/lib/`, which the `doc:install` wizard now copies to `resources/js/lib/`. The renderer passes a hardened `rehype-sanitize` schema that whitelists the `<icon>` tag, the `data-callout` attribute, and per-column `style` widths.

### Removed
- `stubs/js/lib/remark-soft-breaks.ts` — the soft-breaks remark plugin, which forced every single newline to a `<br>`, is no longer shipped or wired into the renderer.

## [1.0.11] - 2026-06-16

### Added
- `doc:merge` command and `DocumentationMergeService` that consolidate all documentation pages into a single markdown file with hierarchical headings and a generated table of contents. Heading levels are shifted per nesting depth, the output directory is configurable (`merge` option in `config/oi-laravel-documentation.php`) and overridable via a command-line argument, with error handling for a missing docs directory. Covered by `MergeDocsTest`.

## [1.0.10] - 2026-06-15

### Added
- Bundled `oilab-laravel-docs` AI assistant skill (shipped in `resources/skills/`) teaching the package's documentation conventions — structure, `meta.json`, frontmatter, markdown, link transformation, and the regenerate workflow. The `doc:install-ai-skill` command installs it into the current project (`.claude/skills`, `.junie/skills`) or the user profile (`~/.claude/skills`), and adds an `oi-lab/oi-laravel-documentation` rules section to the matching `CLAUDE.md`. See `docs/advanced/skills.md`.

### Deprecated
- `doc:install-ai-skill` in favor of the unified `php artisan oi:skills` command (provided by `oi-lab/oi-laravel-development`), which discovers and installs the AI assistant skills declared by all installed `oi-lab/*` packages. The legacy command still works and delegates to `oi:skills`.

## [1.0.9] - 2026-06-14

### Changed
- Overhauled `documentation-markdown-content.tsx`: responsive heading spacing and sizes, anchor copy-links now visible on `md`+ screens, and `data-slot` attributes added across headings, code blocks, inline code, links, blockquotes, tables, images and the wrapper for easier styling. `CodeBlock` now selects a Shiki theme (`github-light` / `github-dark`), extracts and applies the `pre` background colour, wraps `pre` blocks for better layout, and improves fallback rendering; images gained a `max-width`.

## [1.0.8] - 2026-05-16

### Added
- Comprehensive bundled documentation under `docs/` — getting-started, configuration, content, customization and advanced guides, plus command references (`doc:install`, `doc:add-page`, `doc:gen-nav`, `doc:gen-index`, `doc:import`) and `DocumentationService` internals, each with `meta.json` section metadata.

## [1.0.7] - 2026-05-16

### Added
- `doc:import` command that imports documentation bundled inside a Composer package (its `docs/` folder) into the host application.

## [1.0.6] - 2026-05-12

### Added
- `doc:add-page` command — an interactive wizard to scaffold a new documentation page (section, title, description, filename, order), with an optional `--regenerate` flag.

### Changed
- Documentation content now lives under `resources/markdown/docs` by default (`docs_path`).

## [1.0.5] - 2026-05-12

### Added
- `components_path` configuration option (default `resources/js/components/documentation`) to control where the published React components are installed. The wizard rewrites the `@/` imports inside the published files to match a custom path.
- The `doc:install` wizard now detects the project's JavaScript package manager (from the `packageManager` field in `package.json` or the lockfile) and lets you choose between `pnpm`, `npm` and `yarn`. The detected one is preselected. Dependency installs use `pnpm add` / `npm install` / `yarn add`, and ShadCN runs through `pnpm dlx` / `npx` / `yarn dlx`.
- The wizard now asks how the documentation should be accessible — public, authenticated only (`auth` middleware), or restricted by one or more custom middleware — and writes the choice to `route.middleware` in the published config file.
- New `documentation-header.tsx` and `documentation-footer.tsx` components, extracted from the layout to mirror the structure of the Laravel React starter kit layouts.
- `class-variance-authority` added to the list of required JavaScript packages.
- README hero image (`assets/github-preview.png`) and status badges (Packagist version, downloads, CI, license).

### Changed
- Replaced `heading.tsx`, `heading-large.tsx`, `heading-small.tsx` and `heading-xsmall.tsx` with a single `documentation-heading.tsx` component exposing a `size` variant (`xs`, `sm`, `default`, `lg`) built with `cva`, the same way ShadCN UI components are.
- React components are now installed under `resources/js/components/documentation/` by default (configurable, see above) instead of `resources/js/components/`.
- `documentation-layout.tsx` is now a thin wrapper composing `documentation-header.tsx` and `documentation-footer.tsx`.

### Removed
- `heading.tsx`, `heading-large.tsx`, `heading-small.tsx`, `heading-xsmall.tsx` (superseded by `documentation-heading.tsx`).

## [1.0.4] - 2026-04-28

### Changed
- Allow `symfony/yaml` `^8.0` in `composer.json`.

## [1.0.3] - 2026-04-28

### Added
- Added `.gitignore` and documentation updates.

### Changed
- Bump minimum PHP version to 8.3.

## [1.0.2] - 2026-04-28

### Changed
- README updates: license, credits, support.

## [1.0.1] - 2026-04-28

### Added
- Support for Illuminate 13 and Testbench 10.

## [1.0.0] - 2025-11-09

### Added
- Initial release: markdown-based documentation with hierarchical navigation, full-text search, auto-generated navigation, YAML frontmatter, table of contents, link transformation, adjacent page navigation, pre-built Inertia.js + React components, Shiki syntax highlighting, ShadCN UI support and an interactive installation wizard.
