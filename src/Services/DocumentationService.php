<?php

namespace OiLab\LaravelDocumentation\Services;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Symfony\Component\Yaml\Yaml;

class DocumentationService
{
    private ?GithubFlavoredMarkdownConverter $markdownConverter = null;

    private function getDocsPath(): string
    {
        return base_path(config('oi-laravel-documentation.docs_path', 'resources/markdown/docs'));
    }

    private function getNavigationFile(): string
    {
        return $this->getDocsPath().'/'.config('oi-laravel-documentation.navigation_file', 'navigation.json');
    }

    public function getDocument(string $slug): ?array
    {
        $navigation = $this->getNavigation();

        $item = $this->findDocumentBySlug($navigation['sections'], $slug);

        if ($item) {
            return $this->loadDocument($item['path'] ?? $item['file']);
        }

        return null;
    }

    public function getNavigation(): array
    {
        $path = $this->getNavigationFile();

        if (! File::exists($path)) {
            return ['sections' => []];
        }

        $content = File::get($path);

        return json_decode($content, true) ?? ['sections' => []];
    }

    private function findDocumentBySlug(array $sections, string $slug): ?array
    {
        foreach ($sections as $section) {
            foreach ($section['items'] ?? [] as $item) {
                if ($item['slug'] === $slug) {
                    return $item;
                }
            }

            if (! empty($section['subsections'])) {
                $found = $this->findDocumentBySlug($section['subsections'], $slug);
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }

    private function loadDocument(string $filePath): ?array
    {
        $path = $this->getDocsPath().'/'.$filePath;

        if (! File::exists($path)) {
            return null;
        }

        $content = File::get($path);

        $frontmatter = $this->extractFrontmatter($content);
        $markdown = $this->extractMarkdown($content);
        $markdown = $this->transformMarkdownLinks($markdown, $filePath);
        $tableOfContents = $this->extractTableOfContents($markdown);

        $document = [
            'frontmatter' => $frontmatter,
            'markdown' => $markdown,
            'tableOfContents' => $tableOfContents,
        ];

        if (config('oi-laravel-documentation.rendering.markdown_engine', 'client') === 'server') {
            $document['html'] = $this->convertMarkdownToHtml($markdown);
        }

        return $document;
    }

    /**
     * Convert markdown to HTML for the "server" rendering engine.
     *
     * Documentation content is authored by the application's own developers
     * (not user input), so raw HTML embedded in the markdown (e.g. `<icon>`
     * tags) is passed through unescaped, mirroring the client-side renderer's
     * `rehype-raw` behavior.
     */
    private function convertMarkdownToHtml(string $markdown): string
    {
        $this->markdownConverter ??= new GithubFlavoredMarkdownConverter([
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
        ]);

        return (string) $this->markdownConverter->convert($markdown);
    }

    public function extractFrontmatter(string $content): ?array
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

    public function extractMarkdown(string $content): string
    {
        if (! str_starts_with($content, '---')) {
            return $content;
        }

        $parts = preg_split('/^---$/m', $content, 3);

        if (count($parts) < 3) {
            return $content;
        }

        return trim($parts[2]);
    }

    private function transformMarkdownLinks(string $markdown, string $currentFilePath): string
    {
        $pathToSlugMap = $this->buildPathToSlugMap();

        $currentDir = dirname($currentFilePath);

        $pattern = '/\[([^\]]+)\]\(([^)]+\.md)\)/';

        return preg_replace_callback($pattern, function ($matches) use ($currentDir, $pathToSlugMap) {
            $linkText = $matches[1];
            $linkPath = $matches[2];

            if (str_starts_with($linkPath, 'http') || str_starts_with($linkPath, '#')) {
                return $matches[0];
            }

            $resolvedPath = $this->resolveRelativePath($currentDir, $linkPath);

            $slug = $pathToSlugMap[$resolvedPath] ?? null;

            if ($slug) {
                $prefix = config('oi-laravel-documentation.route.prefix', 'documentation');

                return "[{$linkText}](/{$prefix}/{$slug})";
            }

            return $matches[0];
        }, $markdown);
    }

    private function buildPathToSlugMap(): array
    {
        $navigation = $this->getNavigation();
        $map = [];

        $this->extractPathsFromSections($navigation['sections'], $map);

        return $map;
    }

    private function extractPathsFromSections(array $sections, array &$map): void
    {
        foreach ($sections as $section) {
            foreach ($section['items'] ?? [] as $item) {
                if (isset($item['path']) && isset($item['slug'])) {
                    $map[$item['path']] = $item['slug'];
                }
            }

            if (! empty($section['subsections'])) {
                $this->extractPathsFromSections($section['subsections'], $map);
            }
        }
    }

    private function resolveRelativePath(string $currentDir, string $relativePath): string
    {
        $currentDir = trim($currentDir, './');

        $parts = explode('/', $relativePath);
        $dirParts = $currentDir ? explode('/', $currentDir) : [];

        foreach ($parts as $part) {
            if ($part === '..') {
                array_pop($dirParts);
            } elseif ($part !== '.' && $part !== '') {
                $dirParts[] = $part;
            }
        }

        return implode('/', $dirParts);
    }

    public function extractTableOfContents(string $markdown): array
    {
        $toc = [];

        $markdownWithoutCodeBlocks = $this->removeCodeBlocks($markdown);

        preg_match_all('/^(#{1,6})\s+(.+)$/m', $markdownWithoutCodeBlocks, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $level = strlen($match[1]);
            $title = trim($match[2]);

            $toc[] = [
                'level' => $level,
                'title' => $title,
                'slug' => Str::of(str_replace('/', '-', $title))->slug(),
            ];
        }

        return $toc;
    }

    private function removeCodeBlocks(string $markdown): string
    {
        return preg_replace('/```[\s\S]*?```/m', '', $markdown);
    }

    public function getAdjacentPages(string $slug): array
    {
        $navigation = $this->getNavigation();
        $allPages = $this->flattenAllPages($navigation['sections']);

        $currentIndex = array_search($slug, array_column($allPages, 'slug'));

        if ($currentIndex === false) {
            return ['previous' => null, 'next' => null];
        }

        return [
            'previous' => $currentIndex > 0 ? $allPages[$currentIndex - 1] : null,
            'next' => $currentIndex < count($allPages) - 1 ? $allPages[$currentIndex + 1] : null,
        ];
    }

    private function flattenAllPages(array $sections): array
    {
        $pages = [];

        foreach ($sections as $section) {
            foreach ($section['items'] ?? [] as $item) {
                $pages[] = $item;
            }

            if (! empty($section['subsections'])) {
                $pages = array_merge($pages, $this->flattenAllPages($section['subsections']));
            }
        }

        return $pages;
    }
}
