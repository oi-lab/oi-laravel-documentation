<?php

use Illuminate\Support\Facades\File;
use OiLab\LaravelDocumentation\Services\DocumentationMergeService;

beforeEach(function () {
    $this->base = sys_get_temp_dir().'/oi-docs-merge-'.uniqid();
    $this->docs = $this->base.'/resources/markdown/docs';

    File::ensureDirectoryExists($this->docs);
    app()->setBasePath($this->base);
    config()->set('oi-laravel-documentation.docs_path', 'resources/markdown/docs');

    writeMeta($this->docs.'/meta.json', [
        'type' => 'package',
        'name' => 'oi-lab/demo',
        'title' => 'Demo Project',
        'description' => 'A demo package',
        'order' => 1,
    ]);

    writeMeta($this->docs.'/getting-started/meta.json', [
        'type' => 'section',
        'title' => 'Getting Started',
        'description' => 'Start here',
        'order' => 1,
    ]);

    writeDoc($this->docs.'/getting-started/installation.md', [
        'title' => 'Installation',
        'description' => 'How to install',
        'order' => 1,
    ], "# Installation\n\n## Requirements\n\nNeed PHP.");

    writeMeta($this->docs.'/advanced/meta.json', [
        'type' => 'section',
        'title' => 'Advanced',
        'order' => 2,
    ]);

    writeDoc($this->docs.'/advanced/usage.md', [
        'title' => 'Usage',
        'order' => 1,
    ], "# Usage\n\nText.\n\n### Deep\n\nMore.");
});

afterEach(function () {
    File::deleteDirectory($this->base);
});

function writeMeta(string $path, array $data): void
{
    File::ensureDirectoryExists(dirname($path));
    File::put($path, json_encode($data, JSON_PRETTY_PRINT));
}

function writeDoc(string $path, array $frontmatter, string $body): void
{
    File::ensureDirectoryExists(dirname($path));

    $yaml = '';
    foreach ($frontmatter as $key => $value) {
        $yaml .= "{$key}: {$value}\n";
    }

    File::put($path, "---\n{$yaml}---\n\n{$body}\n");
}

it('names the output after the project and counts documents', function () {
    $result = app(DocumentationMergeService::class)->merge();

    expect($result['slug'])->toBe('demo')
        ->and($result['title'])->toBe('Demo Project')
        ->and($result['documentCount'])->toBe(2);
});

it('renders the project title, accroche and a table of contents', function () {
    $markdown = app(DocumentationMergeService::class)->merge()['markdown'];

    expect($markdown)->toStartWith('# Demo Project')
        ->and($markdown)->toContain('> A demo package')
        ->and($markdown)->toContain('## Table of Contents')
        ->and($markdown)->toContain('- [Getting Started](#getting-started)')
        ->and($markdown)->toContain('  - [Installation](#installation)');
});

it('orders sections by their meta order', function () {
    $markdown = app(DocumentationMergeService::class)->merge()['markdown'];

    expect(strpos($markdown, '## Getting Started'))
        ->toBeLessThan(strpos($markdown, '## Advanced'));
});

it('places sections at H2 and document titles at H3', function () {
    $markdown = app(DocumentationMergeService::class)->merge()['markdown'];

    expect($markdown)->toContain('## Getting Started')
        ->and($markdown)->toContain('### Installation')
        ->and($markdown)->toContain('*How to install*');
});

it('shifts document body headings and drops the duplicate title heading', function () {
    $markdown = app(DocumentationMergeService::class)->merge()['markdown'];

    // "### Installation" (doc title, level 3) → body "## Requirements" shifts by 2 → "#### Requirements"
    expect($markdown)->toContain('#### Requirements')
        // body "### Deep" inside "Usage" shifts by 2 → "##### Deep"
        ->and($markdown)->toContain('##### Deep')
        // the body's own "# Installation" duplicate is removed; only the H3 title remains
        ->and(substr_count($markdown, 'Installation'))->toBe(2); // TOC link + H3 heading
});

it('never shifts headings inside fenced code blocks', function () {
    writeDoc($this->docs.'/getting-started/snippets.md', [
        'title' => 'Snippets',
        'order' => 2,
    ], "# Snippets\n\nExample:\n\n```php\n// ## not a heading\n# also not a heading\n```\n\n## Real Heading");

    $markdown = app(DocumentationMergeService::class)->merge()['markdown'];

    expect($markdown)->toContain('// ## not a heading')
        ->and($markdown)->toContain('# also not a heading')
        // the "## Real Heading" outside the fence shifts (doc at level 3 → +2 → ####)
        ->and($markdown)->toContain('#### Real Heading');
});

it('does not leak frontmatter into the merged output', function () {
    $markdown = app(DocumentationMergeService::class)->merge()['markdown'];

    expect($markdown)->not->toContain('order:')
        ->and($markdown)->not->toContain('title: Installation');
});

it('writes the merged file to the private storage directory by default', function () {
    $this->artisan('doc:merge')->assertSuccessful();

    $expected = $this->base.'/storage/app/private/docs/demo.md';

    expect(File::exists($expected))->toBeTrue()
        ->and(File::get($expected))->toStartWith('# Demo Project');
});

it('honours a custom --output path', function () {
    $this->artisan('doc:merge', ['--output' => 'public/exported.md'])->assertSuccessful();

    expect(File::exists($this->base.'/public/exported.md'))->toBeTrue();
});
