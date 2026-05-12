# Changelog

All notable changes to `oi-lab/oi-laravel-documentation` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- The `doc:install` wizard now detects the project's JavaScript package manager (from the `packageManager` field in `package.json` or the lockfile) and lets you choose between `pnpm`, `npm` and `yarn`. The detected one is preselected. Dependency installs use `pnpm add` / `npm install` / `yarn add`, and ShadCN runs through `pnpm dlx` / `npx` / `yarn dlx`.
- New `components_path` configuration option (default `resources/js/components/documentation`) to control where the published React components are installed. The wizard rewrites the `@/` imports inside the published files to match a custom path.
- The wizard now asks how the documentation should be accessible — public, authenticated only (`auth` middleware), or restricted by one or more custom middleware — and writes the choice to `route.middleware` in the published config file.
- New `documentation-header.tsx` and `documentation-footer.tsx` components, extracted from the layout to mirror the structure of the Laravel React starter kit layouts.
- `class-variance-authority` added to the list of required JavaScript packages.

### Changed
- Replaced `heading.tsx`, `heading-large.tsx`, `heading-small.tsx` and `heading-xsmall.tsx` with a single `documentation-heading.tsx` component exposing a `size` variant (`xs`, `sm`, `default`, `lg`) built with `cva`, the same way ShadCN UI components are.
- React components are now installed under `resources/js/components/documentation/` by default (configurable, see above) instead of `resources/js/components/`.
- `documentation-layout.tsx` is now a thin wrapper composing `documentation-header.tsx` and `documentation-footer.tsx`.

### Removed
- `heading.tsx`, `heading-large.tsx`, `heading-small.tsx`, `heading-xsmall.tsx` (superseded by `documentation-heading.tsx`).

## [1.0.4] - 2025

### Changed
- Allow `symfony/yaml` `^8.0` in `composer.json`.

## [1.0.3] - 2025

### Changed
- Bump minimum PHP version to 8.3.

### Added
- Added `.gitignore` and documentation updates.

## [1.0.2] - 2025

### Changed
- README updates: license, credits, support.

## [1.0.1] - 2025

### Added
- Support for Illuminate 13 and Testbench 10.

## [1.0.0] - 2025

### Added
- Initial release: markdown-based documentation with hierarchical navigation, full-text search, auto-generated navigation, YAML frontmatter, table of contents, link transformation, adjacent page navigation, pre-built Inertia.js + React components, Shiki syntax highlighting, ShadCN UI support and an interactive installation wizard.
