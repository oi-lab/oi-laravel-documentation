<?php

namespace OiLab\LaravelDocumentation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InstallDocumentation extends Command
{
    private const DEFAULT_COMPONENTS_PATH = 'resources/js/components/documentation';

    private const DEFAULT_COMPONENTS_ALIAS = '@/components/documentation';

    protected $signature = 'doc:install {--force : Overwrite existing files}';

    protected $description = 'Install documentation structure and files';

    private array $requiredNpmPackages = [
        '@inertiajs/react' => '^2.0.0',
        'react-markdown' => '^9.0.0',
        'remark-gfm' => '^4.0.0',
        'rehype-raw' => '^7.0.0',
        'rehype-sanitize' => '^6.0.0',
        'slugify' => '^1.6.6',
        'shiki' => '^1.0.0',
        'mermaid' => '^11.0.0',
        'lucide-react' => '^0.460.0',
        'usehooks-ts' => '^3.1.1',
        'class-variance-authority' => '^0.7.0',
    ];

    private array $shadcnComponents = [
        'sonner',
    ];

    private ?string $packageManager = null;

    public function handle(): int
    {
        $this->info('╔════════════════════════════════════════════════════════╗');
        $this->info('║  OiLab Laravel Documentation - Installation Wizard     ║');
        $this->info('╚════════════════════════════════════════════════════════╝');
        $this->newLine();

        $force = $this->option('force');

        $this->info('This wizard will guide you through the installation process.');
        $this->newLine();

        $steps = [
            'config' => 'Configuration file',
            'routes' => 'Routes file',
            'docs' => 'Documentation directory',
            'components' => 'React components',
            'shadcn' => 'ShadCN UI components',
        ];

        $selectedSteps = [];

        foreach ($steps as $key => $label) {
            if ($this->shouldInstallStep($key, $force)) {
                $selectedSteps[$key] = $label;
            }
        }

        if (empty($selectedSteps)) {
            $this->info('✅ All components are already installed.');
            $this->newLine();
            $this->line('Use --force to reinstall.');

            return self::SUCCESS;
        }

        $this->info('The following components will be installed:');
        foreach ($selectedSteps as $label) {
            $this->line("  • {$label}");
        }
        $this->newLine();

        if (! $this->confirm('Do you want to proceed?', true)) {
            $this->warn('Installation cancelled.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Installing components...');
        $this->newLine();

        if (isset($selectedSteps['config'])) {
            $this->publishConfig($force);
            $this->configureRouteAccess();
            $this->configureRenderingOptions();
        }

        if (isset($selectedSteps['routes'])) {
            $this->publishRoutes($force);
        }

        if (isset($selectedSteps['docs'])) {
            $this->createDocumentationDirectory($force);
        }

        if (isset($selectedSteps['components'])) {
            $this->installReactComponents($force);
        }

        $this->checkAndInstallNpmPackages();

        if (isset($selectedSteps['shadcn'])) {
            $this->installShadcnComponents();
        }

        $this->newLine();
        $this->info('╔════════════════════════════════════════════════════════╗');
        $this->info('║  Installation Complete!                                ║');
        $this->info('╚════════════════════════════════════════════════════════╝');
        $this->newLine();
        $docsPath = config('oi-laravel-documentation.docs_path', 'resources/markdown/docs');

        $this->info('📝 Next steps:');
        $this->line("  1. Add markdown files to {$docsPath}/ (or run: php artisan doc:add-page)");
        $this->line('  2. Run: php artisan doc:gen-nav');
        $this->line('  3. Run: php artisan doc:gen-index');
        $this->line('  4. Visit /documentation in your browser');
        $this->newLine();
        $this->info('📚 Documentation: https://github.com/oi-lab/oi-laravel-documentation');

        return self::SUCCESS;
    }

    private function shouldInstallStep(string $step, bool $force): bool
    {
        if ($force) {
            return true;
        }

        return match ($step) {
            'config' => ! File::exists(config_path('oi-laravel-documentation.php')),
            'routes' => ! File::exists(base_path('routes/documentation.php')),
            'docs' => ! File::exists(base_path(config('oi-laravel-documentation.docs_path', 'resources/markdown/docs'))),
            'components' => ! $this->areComponentsInstalled(),
            'shadcn' => ! $this->areShadcnComponentsInstalled(),
            default => false,
        };
    }

    private function areComponentsInstalled(): bool
    {
        $componentsPath = base_path($this->componentsPath().'/documentation-markdown-content.tsx');
        $htmlContentPath = base_path($this->componentsPath().'/documentation-html-content.tsx');
        $headingPath = base_path($this->componentsPath().'/documentation-heading.tsx');
        $layoutPath = resource_path('js/layouts/documentation-layout.tsx');
        $indexPage = resource_path('js/pages/documentation/index.tsx');
        $remarkPlugin = resource_path('js/lib/remark-callouts.ts');
        $typographyLib = resource_path('js/lib/documentation-typography.ts');

        return File::exists($componentsPath)
            && File::exists($htmlContentPath)
            && File::exists($headingPath)
            && File::exists($layoutPath)
            && File::exists($indexPage)
            && File::exists($remarkPlugin)
            && File::exists($typographyLib);
    }

    private function componentsPath(): string
    {
        $path = (string) config('oi-laravel-documentation.components_path', self::DEFAULT_COMPONENTS_PATH);

        return trim(str_replace('\\', '/', $path), '/');
    }

    private function componentsAlias(): string
    {
        $path = $this->componentsPath();

        if (Str::startsWith($path, 'resources/js/')) {
            return '@/'.Str::after($path, 'resources/js/');
        }

        return $path;
    }

    /**
     * @param  array<string, string>  $replacements  Extra literal str_replace pairs applied after the alias rewrite.
     */
    private function writeStubFile(string $sourcePath, string $targetPath, array $replacements = []): void
    {
        $contents = File::get($sourcePath);

        $alias = $this->componentsAlias();

        if ($alias !== self::DEFAULT_COMPONENTS_ALIAS) {
            $contents = str_replace(
                self::DEFAULT_COMPONENTS_ALIAS.'/',
                rtrim($alias, '/').'/',
                $contents
            );
        }

        if (! empty($replacements)) {
            $contents = str_replace(array_keys($replacements), array_values($replacements), $contents);
        }

        File::put($targetPath, $contents);
    }

    /**
     * @return array<string, string>
     */
    private function typographyClassReplacements(): array
    {
        if (! config('oi-laravel-documentation.rendering.typeset', false)) {
            return [];
        }

        return [
            "export const DOCUMENTATION_TYPOGRAPHY_CLASS = 'typography';"
                => "export const DOCUMENTATION_TYPOGRAPHY_CLASS = 'typeset';",
        ];
    }

    private function publishConfig(bool $force): void
    {
        $configPath = config_path('oi-laravel-documentation.php');

        if (File::exists($configPath) && ! $force) {
            $this->warn('⚠ Config file already exists.');

            return;
        }

        $this->call('vendor:publish', [
            '--tag' => 'oi-documentation-config',
            '--force' => $force,
        ]);

        $this->info('✓ Published configuration file');
    }

    private function configureRouteAccess(): void
    {
        $configPath = config_path('oi-laravel-documentation.php');

        if (! File::exists($configPath)) {
            return;
        }

        $this->newLine();

        $public = 'Public - anyone can access the documentation';
        $auth = 'Authenticated users only (adds the "auth" middleware)';
        $custom = 'Restricted by a custom middleware';

        $choice = $this->choice(
            'How should the documentation be accessible?',
            [$public, $auth, $custom],
            $public
        );

        $middleware = ['web'];

        if ($choice === $auth) {
            $middleware[] = 'auth';
        } elseif ($choice === $custom) {
            $answer = (string) $this->ask('Enter the middleware name(s), comma separated', 'auth');

            foreach (explode(',', $answer) as $name) {
                $name = trim($name);

                if ($name !== '' && ! in_array($name, $middleware, true)) {
                    $middleware[] = $name;
                }
            }
        }

        $this->applyRouteMiddleware($configPath, $middleware);
    }

    /**
     * @param  list<string>  $middleware
     */
    private function applyRouteMiddleware(string $configPath, array $middleware): void
    {
        $contents = File::get($configPath);

        $replacement = "'middleware' => ['".implode("', '", $middleware)."'],";

        $updated = preg_replace(
            "/'middleware'\s*=>\s*\[[^\]]*\],/",
            $replacement,
            $contents,
            1,
            $count
        );

        if ($count > 0 && $updated !== null) {
            File::put($configPath, $updated);
            $this->info('✓ Documentation route middleware set to: ['.implode(', ', $middleware).']');

            return;
        }

        $this->warn('⚠ Could not update the route middleware automatically.');
        $this->line("  Set 'route.middleware' to [".implode(', ', $middleware)."] in config/oi-laravel-documentation.php manually.");
    }

    private function configureRenderingOptions(): void
    {
        $configPath = config_path('oi-laravel-documentation.php');

        if (! File::exists($configPath)) {
            return;
        }

        $this->newLine();

        $client = 'Client-side - convert markdown to React with ReactMarkdown (syntax highlighting, Mermaid diagrams, copy buttons)';
        $server = 'Server-side - convert markdown to HTML in Laravel and render it as-is (simpler, SSR-friendly, fewer interactive features)';

        $engineChoice = $this->choice(
            'Where should markdown be converted to HTML?',
            [$client, $server],
            $client
        );

        $engine = $engineChoice === $server ? 'server' : 'client';

        $this->updateConfigValue($configPath, 'markdown_engine', "'markdown_engine' => '{$engine}',");
        $this->info("✓ Markdown rendering engine set to: {$engine}");

        $ssr = $this->confirm('Does your application render the Inertia app with SSR (resources/js/ssr.tsx)?', false);
        $this->updateConfigValue($configPath, 'ssr', "'ssr' => ".($ssr ? 'true' : 'false').',');

        if ($ssr && ! File::exists(resource_path('js/ssr.tsx'))) {
            $this->warn('⚠ resources/js/ssr.tsx was not found. Set up Inertia SSR before relying on it.');
        }

        $typeset = $this->confirm("Apply Shadcn UI's \"typeset\" typography class to the documentation content?", false);
        $this->updateConfigValue($configPath, 'typeset', "'typeset' => ".($typeset ? 'true' : 'false').',');

        if ($typeset && ! File::exists(resource_path('css/typeset.css'))) {
            $this->warn('⚠ resources/css/typeset.css was not found.');
            $this->line('  Add it (e.g. via the Shadcn UI typography component) so the "typeset" class resolves to styles.');
        }
    }

    private function updateConfigValue(string $configPath, string $key, string $replacement): void
    {
        $contents = File::get($configPath);

        $updated = preg_replace(
            "/'".preg_quote($key, '/')."'\s*=>\s*(?:'[^']*'|true|false),/",
            $replacement,
            $contents,
            1,
            $count
        );

        if ($count > 0 && $updated !== null) {
            File::put($configPath, $updated);

            return;
        }

        $this->warn("⚠ Could not update '{$key}' automatically.");
        $this->line('  Set it manually in config/oi-laravel-documentation.php.');
    }

    private function publishRoutes(bool $force): void
    {
        $routesPath = base_path('routes/documentation.php');

        if (File::exists($routesPath) && ! $force) {
            $this->warn('⚠ Routes file already exists.');

            return;
        }

        $this->call('vendor:publish', [
            '--tag' => 'oi-documentation-routes',
            '--force' => $force,
        ]);

        $this->info('✓ Published routes file');
        $this->newLine();
        $this->line('  Don\'t forget to load routes in bootstrap/app.php:');
        $this->line('  ->withRouting(');
        $this->line('      web: __DIR__.\'/../routes/web.php\',');
        $this->line('      commands: __DIR__.\'/../routes/console.php\',');
        $this->line('      health: \'/up\',');
        $this->line('      then: function () {');
        $this->line('          Route::middleware(\'web\')');
        $this->line('              ->group(base_path(\'routes/documentation.php\'));');
        $this->line('      },');
        $this->line('  )');
    }

    private function createDocumentationDirectory(bool $force): void
    {
        $docsPath = base_path(config('oi-laravel-documentation.docs_path', 'resources/markdown/docs'));

        if (File::exists($docsPath) && ! $force) {
            $this->warn("⚠ Documentation directory already exists at: {$docsPath}");

            return;
        }

        File::ensureDirectoryExists($docsPath);

        $this->createSampleDocumentation($docsPath);

        $this->info("✓ Created documentation directory at: {$docsPath}");
    }

    private function createSampleDocumentation(string $docsPath): void
    {
        File::put($docsPath.'/meta.json', json_encode([
            'title' => 'Documentation',
            'description' => 'Complete guide to your application',
            'version' => '1.0.0',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $gettingStartedPath = $docsPath.'/getting-started';
        File::ensureDirectoryExists($gettingStartedPath);

        File::put($gettingStartedPath.'/meta.json', json_encode([
            'title' => 'Getting Started',
            'description' => 'Introduction and basic concepts',
            'order' => 1,
            'type' => 'section',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        File::put($gettingStartedPath.'/_index.md', $this->getIntroductionStub());
        File::put($gettingStartedPath.'/installation.md', $this->getInstallationStub());
    }

    private function installReactComponents(bool $force): void
    {
        $componentsPath = $this->componentsPath();

        $this->info("Installing React components into {$componentsPath}/ ...");

        if (! Str::startsWith($componentsPath, 'resources/js/')) {
            $this->warn("⚠ components_path is outside resources/js/ - the \"@/\" imports inside the published files may need manual fixing.");
        }

        $stubsRoot = __DIR__.'/../../../stubs/js';

        $groups = [
            [
                'source' => "{$stubsRoot}/components",
                'target' => base_path($componentsPath),
                'label' => $componentsPath,
                'files' => [
                    'documentation-markdown-content.tsx',
                    'documentation-html-content.tsx',
                    'documentation-navigation.tsx',
                    'documentation-search.tsx',
                    'documentation-toc.tsx',
                    'documentation-heading.tsx',
                    'documentation-header.tsx',
                    'documentation-footer.tsx',
                    'sign.tsx',
                ],
            ],
            [
                'source' => "{$stubsRoot}/lib",
                'target' => resource_path('js/lib'),
                'label' => 'resources/js/lib',
                'files' => [
                    'documentation-typography.ts',
                    'remark-callouts.ts',
                    'remark-table-column.ts',
                ],
                'replacements' => [
                    'documentation-typography.ts' => $this->typographyClassReplacements(),
                ],
            ],
            [
                'source' => "{$stubsRoot}/hooks",
                'target' => resource_path('js/hooks'),
                'label' => 'resources/js/hooks',
                'files' => [
                    'use-flash-messages.tsx',
                ],
            ],
            [
                'source' => "{$stubsRoot}/layouts",
                'target' => resource_path('js/layouts'),
                'label' => 'resources/js/layouts',
                'files' => [
                    'documentation-layout.tsx',
                ],
            ],
            [
                'source' => "{$stubsRoot}/pages",
                'target' => resource_path('js/pages/documentation'),
                'label' => 'resources/js/pages/documentation',
                'files' => [
                    'index.tsx',
                    'show.tsx',
                ],
            ],
        ];

        foreach ($groups as $group) {
            File::ensureDirectoryExists($group['target']);

            foreach ($group['files'] as $file) {
                $sourcePath = "{$group['source']}/{$file}";
                $targetPath = "{$group['target']}/{$file}";
                $fileReplacements = $group['replacements'][$file] ?? [];
                $relLabel = "{$group['label']}/{$file}";

                if (File::exists($targetPath) && ! $force) {
                    if ($this->confirm("  File {$relLabel} already exists. Overwrite?", false)) {
                        $this->writeStubFile($sourcePath, $targetPath, $fileReplacements);
                        $this->line("  ↻ Overwritten: {$relLabel}");
                    } else {
                        $this->line("  ⊘ Skipped: {$relLabel}");
                    }
                } else {
                    $this->writeStubFile($sourcePath, $targetPath, $fileReplacements);
                    $this->line("  ✓ Installed: {$relLabel}");
                }
            }
        }
    }

    private function resolvePackageManager(): string
    {
        if ($this->packageManager !== null) {
            return $this->packageManager;
        }

        $detected = $this->detectPackageManager();

        $choices = ['pnpm', 'npm', 'yarn'];

        $this->packageManager = $this->choice(
            'Which package manager do you want to use?',
            $choices,
            $detected
        );

        return $this->packageManager;
    }

    private function detectPackageManager(): string
    {
        $packageJsonPath = base_path('package.json');

        if (File::exists($packageJsonPath)) {
            $packageJson = json_decode(File::get($packageJsonPath), true) ?: [];

            if (! empty($packageJson['packageManager']) && is_string($packageJson['packageManager'])) {
                $name = strtolower(explode('@', $packageJson['packageManager'])[0]);

                if (in_array($name, ['pnpm', 'npm', 'yarn'], true)) {
                    return $name;
                }
            }
        }

        return match (true) {
            File::exists(base_path('pnpm-lock.yaml')) => 'pnpm',
            File::exists(base_path('yarn.lock')) => 'yarn',
            File::exists(base_path('package-lock.json')) => 'npm',
            default => 'npm',
        };
    }

    private function packageInstallCommand(string $packages): string
    {
        return match ($this->resolvePackageManager()) {
            'pnpm' => "pnpm add {$packages}",
            'yarn' => "yarn add {$packages}",
            default => "npm install {$packages}",
        };
    }

    private function packageDlxCommand(string $command): string
    {
        return match ($this->resolvePackageManager()) {
            'pnpm' => "pnpm dlx {$command}",
            'yarn' => "yarn dlx {$command}",
            default => "npx {$command}",
        };
    }

    private function checkAndInstallNpmPackages(): void
    {
        $this->newLine();
        $this->info('Checking npm dependencies...');

        $packageJsonPath = base_path('package.json');

        if (! File::exists($packageJsonPath)) {
            $this->warn('⚠ package.json not found. Skipping npm dependency check.');

            return;
        }

        $packageJson = json_decode(File::get($packageJsonPath), true);
        $dependencies = array_merge(
            $packageJson['dependencies'] ?? [],
            $packageJson['devDependencies'] ?? []
        );

        $missingPackages = [];

        foreach ($this->requiredNpmPackages as $package => $version) {
            if (! isset($dependencies[$package])) {
                $missingPackages[$package] = $version;
            }
        }

        if (empty($missingPackages)) {
            $this->info('✓ All required npm packages are installed');

            return;
        }

        $this->warn('⚠ Missing npm packages detected:');
        foreach ($missingPackages as $package => $version) {
            $this->line("  • {$package}@{$version}");
        }
        $this->newLine();

        $packages = collect($missingPackages)
            ->map(fn ($version, $package) => "{$package}@{$version}")
            ->join(' ');

        if ($this->confirm('Would you like to install them now?', true)) {
            $command = $this->packageInstallCommand($packages);

            $this->info('Installing npm packages...');
            $this->newLine();

            $this->line("Running: {$command}");
            $this->newLine();

            passthru($command, $exitCode);

            if ($exitCode === 0) {
                $this->newLine();
                $this->info('✓ npm packages installed successfully');
            } else {
                $this->newLine();
                $this->error('✗ Failed to install npm packages');
                $this->line('  You can install them manually with:');
                $this->line("  {$command}");
            }
        } else {
            $this->line('You can install them later with:');
            $this->line('  '.$this->packageInstallCommand($packages));
        }
    }

    private function getIntroductionStub(): string
    {
        return <<<'MD'
---
title: Introduction
description: Welcome to the documentation
section: getting-started
order: 1
---

# Getting Started

Welcome to your application documentation!

## What is this?

This is a modern documentation system built with Laravel. It supports:

- **Markdown-based content** - Write your documentation in simple markdown
- **Hierarchical navigation** - Organize content in sections and subsections
- **Full-text search** - Find what you need quickly
- **Auto-generated navigation** - Navigation is built automatically from your file structure

## What's Next?

Check out the [Installation](installation.md) guide to learn how to set up your environment.
MD;
    }

    private function getInstallationStub(): string
    {
        return <<<'MD'
---
title: Installation
description: How to install and configure the application
section: getting-started
order: 2
---

# Installation

This guide will help you install and configure the application.

## Prerequisites

Before you begin, ensure you have the following installed:

- PHP 8.2 or higher
- Composer
- Node.js and npm
- A database (MySQL, PostgreSQL, SQLite)

## Installation Steps

### 1. Clone the repository

```bash
git clone https://github.com/your-repo/your-app.git
cd your-app
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Run migrations

```bash
php artisan migrate
```

### 5. Build assets

```bash
npm run build
```

## Verification

To verify your installation is working:

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.
MD;
    }

    private function areShadcnComponentsInstalled(): bool
    {
        $allInstalled = true;

        foreach ($this->shadcnComponents as $component) {
            $componentPath = resource_path("js/components/ui/{$component}.tsx");
            if (! File::exists($componentPath)) {
                $allInstalled = false;
                break;
            }
        }

        return $allInstalled;
    }

    private function installShadcnComponents(): void
    {
        $this->newLine();
        $this->info('Installing ShadCN UI components...');
        $this->newLine();

        // Check if shadcn CLI is available
        exec($this->packageDlxCommand('shadcn@latest --version').' 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            $this->warn('⚠ Unable to verify shadcn CLI. Make sure you have Node.js and npm installed.');
            $this->newLine();
        }

        $componentsToInstall = [];

        foreach ($this->shadcnComponents as $component) {
            $componentPath = resource_path("js/components/ui/{$component}.tsx");

            if (! File::exists($componentPath)) {
                $componentsToInstall[] = $component;
            } else {
                $this->line("  ✓ {$component} already installed");
            }
        }

        if (empty($componentsToInstall)) {
            $this->info('✓ All ShadCN UI components are already installed');

            return;
        }

        $this->newLine();
        $this->info('The following ShadCN UI components will be installed:');
        foreach ($componentsToInstall as $component) {
            $this->line("  • {$component}");
        }
        $this->newLine();

        if (! $this->confirm('Would you like to install them now?', true)) {
            $this->line('You can install them later with:');
            foreach ($componentsToInstall as $component) {
                $this->line('  '.$this->packageDlxCommand("shadcn@latest add {$component}"));
            }

            return;
        }

        foreach ($componentsToInstall as $component) {
            $this->info("Installing {$component}...");

            $command = $this->packageDlxCommand("shadcn@latest add {$component} --yes --overwrite");
            $this->line("Running: {$command}");
            $this->newLine();

            passthru($command, $exitCode);

            if ($exitCode === 0) {
                $this->info("  ✓ {$component} installed successfully");
            } else {
                $this->error("  ✗ Failed to install {$component}");
                $this->line('  You can install it manually with:');
                $this->line('  '.$this->packageDlxCommand("shadcn@latest add {$component}"));
            }

            $this->newLine();
        }
    }
}
