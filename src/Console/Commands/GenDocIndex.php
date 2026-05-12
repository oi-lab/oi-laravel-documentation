<?php

namespace OiLab\LaravelDocumentation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use OiLab\LaravelDocumentation\Services\DocumentationService;

class GenDocIndex extends Command
{
    protected $signature = 'doc:gen-index';

    protected $description = 'Generate searchable index for documentation files';

    public function __construct(
        public DocumentationService $documentationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Generating documentation search index...');

        $navigation = $this->documentationService->getNavigation();
        $index = [];

        $index = $this->indexSections($navigation['sections']);

        $indexPath = base_path(config('oi-laravel-documentation.docs_path', 'resources/markdown/docs').'/'.config('oi-laravel-documentation.search_index_file', 'search-index.json'));
        File::put($indexPath, json_encode($index, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info('✅ Index generated successfully with '.count($index).' documents');
        $this->info("📁 Saved to: {$indexPath}");

        return self::SUCCESS;
    }

    private function indexSections(array $sections, string $parentSection = ''): array
    {
        $index = [];

        foreach ($sections as $section) {
            $sectionPath = $parentSection ? "{$parentSection} > {$section['title']}" : $section['title'];

            foreach ($section['items'] ?? [] as $item) {
                $document = $this->documentationService->getDocument($item['slug']);

                if (! $document) {
                    $this->warn("Skipping {$item['slug']} - document not found");
                    continue;
                }

                $index[] = [
                    'id' => $item['slug'],
                    'title' => $item['title'],
                    'description' => $item['description'] ?? '',
                    'section' => $sectionPath,
                    'content' => strip_tags($document['markdown']),
                    'headings' => array_map(
                        fn ($h) => $h['title'],
                        $document['tableOfContents']
                    ),
                ];

                $this->line("✓ Indexed: {$item['title']} ({$sectionPath})");
            }

            if (! empty($section['subsections'])) {
                $subsectionIndex = $this->indexSections($section['subsections'], $sectionPath);
                $index = array_merge($index, $subsectionIndex);
            }
        }

        return $index;
    }
}
