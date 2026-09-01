---
title: doc:install
description: Interactive installation wizard
order: 1
---

# doc:install

The `doc:install` command is an interactive wizard that configures OI Laravel Documentation in your Laravel application. It handles all necessary setup steps automatically.

## Command Syntax

```bash
php artisan doc:install {--force}
```

## Basic Usage

Run the installation wizard:

```bash
php artisan doc:install
```

The wizard will guide you through each step with prompts.

## Installation Steps

### Step 1: Publish Configuration

Publishes the configuration file to `config/oi-documentation.php`:

```
Publishing configuration...
✓ Configuration published to config/oi-documentation.php
```

The configuration file includes all available options for customizing the package.

### Step 2: Middleware Selection

Prompts you to choose access control:

```
How should documentation access be controlled?

  [1] Public - Anyone can view documentation
  [2] Authenticated - Only logged-in users can view
  [3] Custom - Specify custom middleware
```

**Option 1 - Public:**
- No authentication required
- Anyone can access documentation
- Middleware: `['web']`

**Option 2 - Authenticated:**
- Only logged-in users can view
- Redirects unauthenticated users to login
- Middleware: `['web', 'auth']`

**Option 3 - Custom:**
- Define your own middleware stack
- For role-based or specialized access control
- Prompts you to enter middleware names

Select an option (default is 1):
```

Example custom middleware input:
```
Select middleware [web]:
web, auth, verified
```

### Step 2b: Rendering Options

Prompts you to choose how markdown is rendered and whether to opt into SSR and Shadcn's typography class:

```
Where should markdown be converted to HTML?

  [1] Client-side - convert markdown to React with ReactMarkdown (syntax highlighting, Mermaid diagrams, copy buttons)
  [2] Server-side - convert markdown to HTML in Laravel and render it as-is (simpler, SSR-friendly, fewer interactive features)

Does your application render the Inertia app with SSR (resources/js/ssr.tsx)? (yes/no)

Apply Shadcn UI's "typeset" typography class to the documentation content? (yes/no)
```

These choices are saved to `rendering.markdown_engine`, `rendering.ssr`, and `rendering.typeset` in the config file (see [Configuration](../configuration/configuration.md)). The React components step (below) always publishes both `DocumentationMarkdownContent` and `DocumentationHtmlContent` — `show.tsx` switches between them at runtime based on `rendering.markdown_engine` — and sets the shared `DOCUMENTATION_TYPOGRAPHY_CLASS` constant (`resources/js/lib/documentation-typography.ts`) to `typography` or `typeset` to match your answer.

### Step 3: Publish Routes

Publishes route definitions:

```
Publishing routes...
✓ Routes published to routes/documentation.php
```

The route file is included in your web routes and can be customized if needed.

### Step 4: Create Documentation Directory

Creates the documentation file structure:

```
Creating documentation directory structure...
✓ Documentation directory created at resources/markdown/docs/
✓ Sample content created
```

Creates:
- `resources/markdown/docs/meta.json` - Root documentation metadata
- `resources/markdown/docs/getting-started/` - Getting started section
- Sample markdown files with documentation examples

### Step 5: Publish React Components

Extracts customizable React components:

```
Publishing React components...
✓ Components published to resources/js/components/documentation/
```

Components include:
- `DocumentationLayout.tsx` - Optional standalone page shell (header/footer)
- `DocumentationNavigation.tsx` - Sidebar navigation
- `DocumentationSearch.tsx` - Search interface
- `DocumentationMarkdownContent.tsx` and `DocumentationHtmlContent.tsx` - Content renderers; `show.tsx` picks one at runtime based on `rendering.markdown_engine`
- And more...

All components are fully customizable since they're in your project.

### Step 6: Check and Install NPM Dependencies

Verifies required npm packages:

```
Checking npm dependencies...
✓ Required packages found or installing...
```

Automatically installs if missing:
- `react-markdown`
- `react-syntax-highlighter`
- `shiki`
- `lucide-react`
- `@radix-ui/react-*`
- And dependencies

### Step 7: Install ShadCN Sonner

Installs toast notification library:

```
Installing ShadCN Sonner for toast notifications...
✓ Sonner installed
```

Provides toast notifications for user feedback.

### Step 8: Final Verification

```
✓ Installation complete!

Your documentation is ready at:
  http://localhost:8000/documentation

Next steps:
  1. npm run build (or npm run dev)
  2. Visit the URL above
  3. Edit docs in resources/markdown/docs/
```

## Options

### --force

Force reinstallation, overwriting existing files:

```bash
php artisan doc:install --force
```

Use this when:
- Fixing corrupted component files
- Updating to a new package version
- Re-running installation after manual changes

**Warning:** This will overwrite existing component files and configuration. Back up customizations first.

## Troubleshooting Installation

### "Configuration file already exists"

The configuration was already published. Skip this step or use `--force` to overwrite:

```bash
php artisan doc:install --force
```

### "NPM package installation failed"

Install manually:

```bash
npm install react-markdown react-syntax-highlighter shiki lucide-react
```

### "Components not loading"

Rebuild assets:

```bash
npm run build
```

Or with hot reload:

```bash
npm run dev
```

### "Documentation URL not found"

Ensure:
1. Routes are registered in `bootstrap/app.php`
2. NPM build is complete
3. Middleware configuration is correct

Regenerate navigation:

```bash
php artisan doc:gen-nav
```

### Route prefix changed by wizard

The prefix set during installation is saved to config. To change it:

```php
// config/oi-documentation.php
'route' => [
    'prefix' => 'documentation', // Change this
],
```

Then regenerate:

```bash
php artisan doc:gen-nav
```

## Post-Installation

After installation completes:

1. **Build assets:**
   ```bash
   npm run build
   ```

2. **Visit documentation:**
   ```
   http://localhost:8000/documentation
   ```

3. **Start editing:**
   - Edit files in `resources/markdown/docs/`
   - Add new sections and pages
   - Regenerate navigation as needed

4. **Optional: Customize components:**
   - Edit React components in `resources/js/components/documentation/`
   - Update styling and layout
   - Customize colors and themes

## Generated Files

The wizard creates/publishes these files:

```
config/oi-documentation.php                    # Configuration file
routes/documentation.php                        # Route definitions
resources/markdown/docs/                        # Documentation directory
  ├── meta.json                                 # Root metadata
  ├── getting-started/
  │   ├── meta.json
  │   └── _index.md
  └── ...
resources/js/components/documentation/          # React components
  ├── DocumentationLayout.tsx
  ├── DocumentationNavigation.tsx
  ├── DocumentationSearch.tsx
  └── ...
```

## Re-running Installation

Run the wizard again to:
- Update configuration
- Change middleware settings
- Reinstall components
- Update dependencies

Use `--force` to skip confirmation prompts:

```bash
php artisan doc:install --force
```

## Next Steps

After installation:

1. [Configure Routes and Access](../configuration/access-control.md)
2. [Write Your First Page](../content/_index.md)
3. [Customize Components](../customization/react-components.md)
4. [Generate Navigation](./doc-gen-nav.md)
