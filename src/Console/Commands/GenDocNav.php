<?php

namespace OiLab\LaravelDocumentation\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

class GenDocNav extends Command
{
    protected $signature = 'doc:gen-nav';

    protected $description = 'Generate documentation navigation from meta.json files and markdown frontmatter';

    public function handle(): int
    {
        $this->info('Generating documentation navigation from meta.json files...');

        $docsPath = base_path(config('oi-laravel-documentation.docs_path', 'resources/markdown/docs'));

        if (! File::exists($docsPath)) {
            $this->error("Documentation directory not found: {$docsPath}");

            return self::FAILURE;
        }

        $navigation = $this->buildNavigationStructure($docsPath);

        $this->saveNavigation($navigation);

        $this->info('✅ Documentation navigation generated successfully!');
        $this->line("📁 Output: {$this->getOutputPath()}");

        return self::SUCCESS;
    }

    private function buildNavigationStructure(string $docsPath): array
    {
        $sections = [];

        $directories = File::directories($docsPath);

        foreach ($directories as $directory) {
            $section = $this->processSection($directory);
            if ($section) {
                $sections[] = $section;
            }
        }

        $rootFiles = $this->getMarkdownFilesInDirectory($docsPath, false);
        if (! empty($rootFiles)) {
            $rootItems = $this->processMarkdownFiles($rootFiles, $docsPath);
            if (! empty($rootItems)) {
                $sections[] = [
                    'title' => 'Getting Started',
                    'slug' => 'root',
                    'items' => $rootItems,
                    'subsections' => [],
                ];
            }
        }

        usort($sections, fn ($a, $b) => ($a['order'] ?? 999) <=> ($b['order'] ?? 999));

        $sections = array_map(function ($section) {
            unset($section['order']);

            return $section;
        }, $sections);

        return ['sections' => $sections];
    }

    private function processSection(string $sectionPath): ?array
    {
        $sectionName = basename($sectionPath);
        $metaPath = $sectionPath.'/meta.json';

        $meta = $this->readMetaFile($metaPath);

        if (! $meta) {
            $this->warn("Skipping {$sectionName}: No meta.json found");

            return null;
        }

        $section = [
            'title' => $meta['title'] ?? ucwords(str_replace('-', ' ', $sectionName)),
            'slug' => $sectionName,
            'order' => $meta['order'] ?? 999,
            'description' => $meta['description'] ?? '',
            'items' => [],
            'subsections' => [],
        ];

        $sectionFiles = $this->getMarkdownFilesInDirectory($sectionPath, false);
        $section['items'] = $this->processMarkdownFiles($sectionFiles, base_path(config('oi-laravel-documentation.docs_path', 'resources/markdown/docs')));

        $subdirectories = File::directories($sectionPath);
        foreach ($subdirectories as $subdirectory) {
            $subsection = $this->processSubsection($subdirectory, $sectionPath);
            if ($subsection) {
                $section['subsections'][] = $subsection;
            }
        }

        usort($section['subsections'], fn ($a, $b) => ($a['order'] ?? 999) <=> ($b['order'] ?? 999));

        $section['subsections'] = array_map(function ($subsection) {
            unset($subsection['order']);

            return $subsection;
        }, $section['subsections']);

        return $section;
    }

    private function readMetaFile(string $path): ?array
    {
        if (! File::exists($path)) {
            return null;
        }

        try {
            $content = File::get($path);

            return json_decode($content, true);
        } catch (Exception $e) {
            $this->error("Error reading meta file {$path}: {$e->getMessage()}");

            return null;
        }
    }

    private function getMarkdownFilesInDirectory(string $directory, bool $recursive = true): array
    {
        $files = $recursive ? File::allFiles($directory) : File::files($directory);

        return array_map(
            fn ($file) => $file->getPathname(),
            array_filter($files, fn ($file) => $file->getExtension() === 'md')
        );
    }

    private function processMarkdownFiles(array $files, string $basePath): array
    {
        $items = [];

        foreach ($files as $file) {
            $item = $this->processMarkdownFile($file, $basePath);
            if ($item) {
                $items[] = $item;
            }
        }

        usort($items, fn ($a, $b) => ($a['order'] ?? 999) <=> ($b['order'] ?? 999));

        return array_map(function ($item) {
            unset($item['order']);

            return $item;
        }, $items);
    }

    private function processMarkdownFile(string $filePath, string $basePath): ?array
    {
        $content = File::get($filePath);
        $frontmatter = $this->extractFrontmatter($content);

        if (! $frontmatter) {
            $this->warn("Skipping {$filePath}: No frontmatter found");

            return null;
        }

        $relativePath = str_replace($basePath.'/', '', $filePath);
        $fileName = basename($filePath);

        $slug = str_replace('.md', '', $relativePath);

        if (str_ends_with($slug, '/_index')) {
            $slug = substr($slug, 0, -7);
        }

        $order = $frontmatter['order'] ?? $this->extractOrderFromFilename($fileName);

        return [
            'title' => $frontmatter['title'] ?? ucwords(str_replace(['-', '_'], ' ', basename($fileName, '.md'))),
            'file' => $fileName,
            'slug' => $slug,
            'path' => $relativePath,
            'order' => $order,
            'description' => $frontmatter['description'] ?? '',
        ];
    }

    private function extractFrontmatter(string $content): ?array
    {
        if (! str_starts_with($content, '---')) {
            return null;
        }

        $parts = preg_split('/^---$/m', $content, 3);

        if (count($parts) < 3) {
            return null;
        }

        try {
            return Yaml::parse($parts[1]);
        } catch (Exception $e) {
            return null;
        }
    }

    private function extractOrderFromFilename(string $filename): int
    {
        if (str_starts_with($filename, '_index')) {
            return 1;
        }

        if (preg_match('/^(\d+)[-_]/', $filename, $matches)) {
            return (int) $matches[1];
        }

        return 999;
    }

    private function processSubsection(string $subsectionPath, string $basePath): ?array
    {
        $subsectionName = basename($subsectionPath);
        $metaPath = $subsectionPath.'/meta.json';

        $meta = $this->readMetaFile($metaPath);

        if (! $meta) {
            $this->warn("Skipping subsection {$subsectionName}: No meta.json found");

            return null;
        }

        $subsection = [
            'title' => $meta['title'] ?? ucwords(str_replace('-', ' ', $subsectionName)),
            'slug' => $subsectionName,
            'order' => $meta['order'] ?? 999,
            'items' => [],
        ];

        $markdownFiles = $this->getMarkdownFilesInDirectory($subsectionPath);

        $subsection['items'] = $this->processMarkdownFiles($markdownFiles, base_path(config('oi-laravel-documentation.docs_path', 'resources/markdown/docs')));

        return $subsection;
    }

    private function saveNavigation(array $navigation): void
    {
        $json = json_encode($navigation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        File::put($this->getOutputPath(), $json);
    }

    private function getOutputPath(): string
    {
        return base_path(config('oi-laravel-documentation.docs_path', 'resources/markdown/docs').'/'.config('oi-laravel-documentation.navigation_file', 'navigation.json'));
    }
}
