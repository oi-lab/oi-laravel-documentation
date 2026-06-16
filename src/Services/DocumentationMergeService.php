<?php

namespace OiLab\LaravelDocumentation\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class DocumentationMergeService
{
    private const MAX_HEADING_LEVEL = 6;

    public function __construct(private DocumentationService $documentation) {}

    /**
     * Build a single merged markdown document from the documentation tree.
     *
     * @return array{title: string, slug: string, markdown: string, documentCount: int}
     */
    public function merge(?string $docsPath = null): array
    {
        $docsPath = $docsPath ?? base_path(config('oi-laravel-documentation.docs_path', 'resources/markdown/docs'));

        if (! File::isDirectory($docsPath)) {
            throw new RuntimeException("Documentation directory not found: {$docsPath}");
        }

        $tree = $this->buildTree($docsPath);

        return [
            'title' => $tree['title'],
            'slug' => $tree['slug'],
            'markdown' => $this->render($tree),
            'documentCount' => $this->countDocuments($tree),
        ];
    }

    /**
     * @return array{type: string, title: string, description: string, slug: string, children: array<int, array<string, mixed>>}
     */
    private function buildTree(string $docsPath): array
    {
        $meta = $this->readMeta($docsPath.'/meta.json') ?? [];

        $title = $meta['title'] ?? ($meta['name'] ?? 'Documentation');
        $slug = isset($meta['name'])
            ? Str::slug(basename((string) $meta['name']))
            : Str::slug($title);

        $children = [];

        foreach ($this->markdownFiles($docsPath) as $file) {
            $children[] = $this->buildDocument($file);
        }

        foreach (File::directories($docsPath) as $directory) {
            $section = $this->buildSection($directory);
            if ($section) {
                $children[] = $section;
            }
        }

        $children = $this->sortByOrder($children);

        return [
            'type' => 'project',
            'title' => $title,
            'description' => (string) ($meta['description'] ?? ''),
            'slug' => $slug !== '' ? $slug : 'documentation',
            'children' => $children,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildSection(string $directory): ?array
    {
        $meta = $this->readMeta($directory.'/meta.json');

        if (! $meta) {
            return null;
        }

        $documents = [];
        foreach ($this->markdownFiles($directory) as $file) {
            $documents[] = $this->buildDocument($file);
        }
        $documents = $this->sortByOrder($documents);

        $subsections = [];
        foreach (File::directories($directory) as $subdirectory) {
            $subsection = $this->buildSection($subdirectory);
            if ($subsection) {
                $subsection['type'] = 'subsection';
                $subsections[] = $subsection;
            }
        }
        $subsections = $this->sortByOrder($subsections);

        return [
            'type' => 'section',
            'title' => $meta['title'] ?? Str::headline(basename($directory)),
            'description' => (string) ($meta['description'] ?? ''),
            'order' => $meta['order'] ?? 999,
            'children' => array_merge($documents, $subsections),
        ];
    }

    /**
     * @return array{type: string, title: string, description: string, order: int, body: string}
     */
    private function buildDocument(string $file): array
    {
        $content = File::get($file);

        $frontmatter = $this->documentation->extractFrontmatter($content) ?? [];
        $body = $this->documentation->extractMarkdown($content);
        $filename = basename($file);

        return [
            'type' => 'document',
            'title' => $frontmatter['title'] ?? Str::headline(pathinfo($filename, PATHINFO_FILENAME)),
            'description' => (string) ($frontmatter['description'] ?? ''),
            'order' => (int) ($frontmatter['order'] ?? $this->orderFromFilename($filename)),
            'body' => $body,
        ];
    }

    private function render(array $tree): string
    {
        $seen = [];
        $this->anchor($tree['title'], $seen);
        $this->anchor('Table of Contents', $seen);

        $lines = ['# '.$tree['title'], ''];

        if ($tree['description'] !== '') {
            $lines[] = '> '.$tree['description'];
            $lines[] = '';
        }

        $lines[] = '## Table of Contents';
        $lines[] = '';
        foreach ($this->buildToc($tree['children'], 0, $seen) as $tocLine) {
            $lines[] = $tocLine;
        }
        $lines[] = '';

        foreach ($tree['children'] as $child) {
            $this->renderNode($child, 2, $lines);
        }

        return rtrim(implode("\n", $lines))."\n";
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     * @param  array<string, bool>  $seen
     * @return array<int, string>
     */
    private function buildToc(array $nodes, int $depth, array &$seen): array
    {
        $lines = [];

        foreach ($nodes as $node) {
            $indent = str_repeat('  ', $depth);
            $anchor = $this->anchor($node['title'], $seen);
            $lines[] = $indent.'- ['.$node['title'].'](#'.$anchor.')';

            if (! empty($node['children'])) {
                $lines = array_merge($lines, $this->buildToc($node['children'], $depth + 1, $seen));
            }
        }

        return $lines;
    }

    /**
     * @param  array<int, string>  $lines
     */
    private function renderNode(array $node, int $level, array &$lines): void
    {
        $heading = str_repeat('#', min($level, self::MAX_HEADING_LEVEL));

        $lines[] = $heading.' '.$node['title'];
        $lines[] = '';

        if (($node['description'] ?? '') !== '') {
            $lines[] = '*'.$node['description'].'*';
            $lines[] = '';
        }

        if ($node['type'] === 'document') {
            $body = $this->prepareBody($node['body'], $level);
            if ($body !== '') {
                $lines[] = $body;
                $lines[] = '';
            }

            return;
        }

        foreach ($node['children'] as $child) {
            $this->renderNode($child, $level + 1, $lines);
        }
    }

    private function prepareBody(string $body, int $level): string
    {
        $body = $this->stripLeadingTitle(trim($body));

        if ($body === '') {
            return '';
        }

        return $this->shiftHeadings($body, $level - 1);
    }

    private function stripLeadingTitle(string $body): string
    {
        $lines = preg_split('/\r?\n/', $body);
        $result = [];
        $seenContent = false;
        $stripped = false;

        foreach ($lines as $line) {
            if (! $seenContent) {
                if (trim($line) === '') {
                    continue;
                }

                $seenContent = true;

                if (! $stripped && preg_match('/^#\s+\S/', $line)) {
                    $stripped = true;

                    continue;
                }
            }

            $result[] = $line;
        }

        return trim(implode("\n", $result));
    }

    private function shiftHeadings(string $markdown, int $shift): string
    {
        if ($shift <= 0) {
            return $markdown;
        }

        $lines = preg_split('/\r?\n/', $markdown);
        $inFence = false;
        $fenceChar = '';
        $fenceLength = 0;

        foreach ($lines as &$line) {
            if (! $inFence) {
                if (preg_match('/^\s*(`{3,}|~{3,})/', $line, $matches)) {
                    $inFence = true;
                    $fenceChar = $matches[1][0];
                    $fenceLength = strlen($matches[1]);

                    continue;
                }

                if (preg_match('/^(#{1,6})(\s+.*)$/', $line, $matches)) {
                    $newLevel = min(strlen($matches[1]) + $shift, self::MAX_HEADING_LEVEL);
                    $line = str_repeat('#', $newLevel).$matches[2];
                }

                continue;
            }

            $closer = '/^\s*('.preg_quote($fenceChar, '/').'{3,})\s*$/';

            if (preg_match($closer, $line, $matches) && strlen($matches[1]) >= $fenceLength) {
                $inFence = false;
                $fenceChar = '';
                $fenceLength = 0;
            }
        }
        unset($line);

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, bool>  $seen
     */
    private function anchor(string $text, array &$seen): string
    {
        $base = $this->slugForAnchor($text);
        $anchor = $base;
        $suffix = 1;

        while (isset($seen[$anchor])) {
            $anchor = $base.'-'.$suffix;
            $suffix++;
        }

        $seen[$anchor] = true;

        return $anchor;
    }

    private function slugForAnchor(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
        $text = preg_replace('/\s+/', '-', (string) $text);

        return (string) $text;
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<int, array<string, mixed>>
     */
    private function sortByOrder(array $nodes): array
    {
        usort($nodes, fn ($a, $b) => ($a['order'] ?? 999) <=> ($b['order'] ?? 999));

        return $nodes;
    }

    private function countDocuments(array $node): int
    {
        $count = ($node['type'] ?? null) === 'document' ? 1 : 0;

        foreach ($node['children'] ?? [] as $child) {
            $count += $this->countDocuments($child);
        }

        return $count;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function readMeta(string $path): ?array
    {
        if (! File::exists($path)) {
            return null;
        }

        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @return array<int, string>
     */
    private function markdownFiles(string $directory): array
    {
        return array_values(array_map(
            fn ($file) => $file->getPathname(),
            array_filter(File::files($directory), fn ($file) => $file->getExtension() === 'md')
        ));
    }

    private function orderFromFilename(string $filename): int
    {
        if (str_starts_with($filename, '_index')) {
            return 1;
        }

        if (preg_match('/^(\d+)[-_]/', $filename, $matches)) {
            return (int) $matches[1];
        }

        return 999;
    }
}
