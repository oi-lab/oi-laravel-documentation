---
title: Installation
description: Step-by-step installation and setup guide
order: 2
---

# Installation

Follow these steps to install and configure OI Laravel Documentation in your Laravel application.

## Step 1: Install the Package

Begin by requiring the package via Composer:

```bash
composer require oi-lab/oi-laravel-documentation
```

Composer will download the package and add it to your dependencies.

## Step 2: Run the Installation Wizard

The package includes an interactive installation wizard that guides you through configuration:

```bash
php artisan doc:install
```

This wizard will:

1. **Publish the configuration file** to `config/oi-documentation.php`
2. **Prompt for middleware settings** - Choose how to control access to your documentation (public, authenticated, or custom)
3. **Publish route definitions** - Adds documentation routes to your application
4. **Create the documentation directory** - Sets up `resources/markdown/docs` with sample content
5. **Publish React components** - Extracts customizable UI components to your project
6. **Check npm dependencies** - Verifies required npm packages
7. **Install ShadCN Sonner** - Adds toast notifications for better UX

## Step 3: Register Routes

During installation, a route file is published. Ensure your `bootstrap/app.php` includes the documentation routes:

```php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ...
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ...
    })
    ->create();
```

The documentation routes will be automatically included in the web routes file.

## Step 4: Build Assets

Rebuild your frontend assets to include the documentation components:

```bash
npm run build
```

Or for development with hot module reloading:

```bash
npm run dev
```

## Step 5: Access Your Documentation

Visit your documentation site at:

```
http://localhost:8000/documentation
```

You should see the documentation interface with the sample content provided by the wizard.

## NPM Packages

The installation wizard automatically handles npm package management. The following packages are installed:

- **@radix-ui/react-*** - Headless UI components from Radix UI
- **class-variance-authority** - CVA for component styling
- **clsx** - Utility for conditional classnames
- **lucide-react** - Icon library
- **react-markdown** - Markdown rendering in React
- **react-syntax-highlighter** - Code syntax highlighting
- **shiki** - Professional syntax highlighting engine
- **sonner** - Toast notifications

These are installed at their latest compatible versions.

## Troubleshooting Installation

### Assets Not Showing

If you don't see the documentation styling or it looks broken, run:

```bash
npm run build
```

### Routes Not Found

If you get a 404 when visiting `/documentation`, ensure:
1. The route file was published
2. `bootstrap/app.php` is loading your web routes
3. You've run `npm run build`

### Missing Components

If components fail to render, verify the React component files are in `resources/js/components/documentation/` by running the wizard again with the `--force` flag:

```bash
php artisan doc:install --force
```

This will overwrite existing component files if they're corrupted.

## Next Steps

Now that installation is complete, explore:
- [Configuration](../configuration/configuration.md) - Customize paths and settings
- [Access Control](../configuration/access-control.md) - Manage who can view documentation
- [Writing Content](../content/_index.md) - Learn how to create documentation pages
