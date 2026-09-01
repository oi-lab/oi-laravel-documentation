<?php

use Illuminate\Support\Facades\File;
use OiLab\LaravelDocumentation\Services\DocumentationService;

beforeEach(function () {
    $this->base = sys_get_temp_dir().'/oi-docs-render-'.uniqid();
    $this->docs = $this->base.'/resources/markdown/docs';

    File::ensureDirectoryExists($this->docs);
    app()->setBasePath($this->base);
    config()->set('oi-laravel-documentation.docs_path', 'resources/markdown/docs');

    File::put($this->docs.'/introduction.md', <<<'MD'
---
title: Introduction
description: Welcome
---

# Introduction

Some **bold** text and a [link](https://example.com).
MD);

    File::put($this->docs.'/navigation.json', json_encode([
        'sections' => [
            [
                'title' => 'Getting Started',
                'slug' => 'getting-started',
                'items' => [
                    [
                        'title' => 'Introduction',
                        'slug' => 'introduction',
                        'path' => 'introduction.md',
                        'description' => 'Welcome',
                    ],
                ],
            ],
        ],
    ]));
});

afterEach(function () {
    File::deleteDirectory($this->base);
});

it('omits the html field when the markdown engine is client-side', function () {
    config()->set('oi-laravel-documentation.rendering.markdown_engine', 'client');

    $document = app(DocumentationService::class)->getDocument('introduction');

    expect($document)->not->toBeNull()
        ->and($document['markdown'])->toContain('Some **bold** text')
        ->and($document)->not->toHaveKey('html');
});

it('converts markdown to html when the markdown engine is server-side', function () {
    config()->set('oi-laravel-documentation.rendering.markdown_engine', 'server');

    $document = app(DocumentationService::class)->getDocument('introduction');

    expect($document)->not->toBeNull()
        ->and($document)->toHaveKey('html')
        ->and($document['html'])->toContain('<strong>bold</strong>')
        ->and($document['html'])->toContain('<a href="https://example.com">link</a>')
        ->and($document['markdown'])->toContain('Some **bold** text');
});
