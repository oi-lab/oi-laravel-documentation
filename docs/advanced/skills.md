---
title: AI Assistant Skill
description: Install the oilab-laravel-docs skill for Claude Code
order: 3
---

# AI Assistant Skill

The package ships an AI assistant skill, `oilab-laravel-docs`, that teaches Claude Code how to write documentation in this package's format — directory structure, `meta.json`, frontmatter, supported markdown, link transformation, and the regenerate workflow.

Installing it lets your AI assistant author and organize documentation pages correctly without rediscovering the conventions each time.

## Install

Use the unified `oi:skills` command (provided by `oi-lab/oi-laravel-development`). It discovers the skills declared by every installed `oi-lab/*` package and lets you pick which ones to install, into the current project or your Claude Code user profile:

```bash
php artisan oi:skills
```

You'll be presented with a multiselect picker of available skills and asked whether to install into the current project or your Claude Code user profile.

> **Deprecated:** `php artisan doc:install-ai-skill` still works and is kept for backward compatibility, but is **deprecated** in favor of `oi:skills`. It now delegates to `oi:skills` internally.

### Install only this skill into the project

```bash
php artisan oi:skills oilab-laravel-docs --project
```

This copies the skill into:

- `.claude/skills/oilab-laravel-docs/`
- `.junie/skills/oilab-laravel-docs/`

It also adds an `=== oi-lab/oi-laravel-documentation rules ===` section to the project's `CLAUDE.md` (created if missing, updated in place if already present).

### Install only this skill into the user profile

```bash
php artisan oi:skills oilab-laravel-docs --global
```

This copies the skill into `~/.claude/skills/oilab-laravel-docs/`, making it available in **every** project, and adds the rules section to `~/.claude/CLAUDE.md`.

## What the skill contains

```
oilab-laravel-docs/
├── SKILL.md                    # Core workflow: locate docs root → structure → write → regenerate
├── references/
│   ├── structure.md            # Directory layout, meta.json, _index.md, ordering, naming
│   ├── frontmatter.md          # Frontmatter fields and YAML gotchas
│   └── markdown.md             # Supported markdown, code blocks, link transformation, search scoring
└── assets/
    ├── page-template.md        # Copy-ready page template
    ├── section-meta.json       # Section meta.json template
    └── root-meta.json          # Root (package) meta.json template
```

## When it activates

Claude Code loads the skill automatically when you ask it to write, edit, or organize documentation pages, sections, `meta.json`, or frontmatter — or when authoring importable package docs. You can also invoke it explicitly with `/oilab-laravel-docs`.

## Re-running

The `oi:skills` command is safe to re-run. The skill files are overwritten with the current version, and the `CLAUDE.md` rules section is replaced in place without duplicating it.
