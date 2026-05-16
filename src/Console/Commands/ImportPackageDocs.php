<?php

namespace OiLab\LaravelDocumentation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;

class ImportPackageDocs extends Command
{
    protected $signature = 'doc:import {--force : Overwrite existing package documentation without asking}';

    protected $description = 'Import documentation from installed packages that expose a docs/ folder';

    public function handle(): int
    {
        $this->info('Scanning vendor/ for packages with documentation...');
        $this->newLine();

        $packages = $this->discoverPackages();

        if (empty($packages)) {
            $this->warn('No packages with documentation found.');
            $this->line('Packages must include a docs/meta.json file with "type": "package".');

            return self::SUCCESS;
        }

        $options = [];
        foreach ($packages as $package) {
            $label = $package['title'];
            if ($package['description'] !== '') {
                $label .= ' — '.$package['description'];
            }
            $options[$package['name']] = $label;
        }

        $selected = multiselect(
            label: 'Select packages to import',
            options: $options,
            hint: 'Space to select, Enter to confirm',
        );

        if (empty($selected)) {
            $this->warn('No packages selected.');

            return self::SUCCESS;
        }

        $force = (bool) $this->option('force');
        $docsPath = base_path(config('oi-laravel-documentation.docs_path', 'resources/markdown/docs'));

        foreach ($selected as $packageName) {
            $this->importPackage($packages[$packageName], $docsPath, $force);
        }

        $this->newLine();

        if (confirm('Regenerate navigation and search index?', true)) {
            $this->call('doc:gen-nav');
            $this->call('doc:gen-index');
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, array{name: string, title: string, description: string, slug: string, path: string}>
     */
    private function discoverPackages(): array
    {
        $vendorPath = base_path('vendor');
        $packages = [];

        foreach (glob("{$vendorPath}/*/*/docs/meta.json") ?: [] as $metaFile) {
            $content = File::get($metaFile);
            $meta = json_decode($content, true);

            if (! is_array($meta) || ($meta['type'] ?? '') !== 'package') {
                continue;
            }

            $name = $meta['name'] ?? null;

            if (! is_string($name) || $name === '') {
                continue;
            }

            $packages[$name] = [
                'name' => $name,
                'title' => $meta['title'] ?? $name,
                'description' => $meta['description'] ?? '',
                'slug' => $meta['slug'] ?? $this->slugFromName($name),
                'path' => dirname($metaFile),
            ];
        }

        return $packages;
    }

    private function slugFromName(string $name): string
    {
        return basename(str_replace('/', DIRECTORY_SEPARATOR, $name));
    }

    /**
     * @param  array{name: string, title: string, description: string, slug: string, path: string}  $package
     */
    private function importPackage(array $package, string $docsPath, bool $force): void
    {
        $targetPath = $docsPath.'/'.$package['slug'];

        if (File::exists($targetPath) && ! $force) {
            if (! confirm("  \"{$package['title']}\" already exists at docs/{$package['slug']}/. Overwrite?", false)) {
                $this->line("  ⊘ Skipped: {$package['title']}");

                return;
            }
        }

        if (File::exists($targetPath)) {
            File::deleteDirectory($targetPath);
        }

        File::copyDirectory($package['path'], $targetPath);

        $this->line("  ✓ Imported: {$package['title']} → docs/{$package['slug']}/");
    }
}
