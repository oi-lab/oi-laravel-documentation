<?php

namespace OiLab\LaravelDocumentation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use OiLab\LaravelDocumentation\Services\DocumentationMergeService;

class MergeDocs extends Command
{
    protected $signature = 'doc:merge
        {--path= : Documentation source directory, relative to the application base path}
        {--output= : Destination markdown file (absolute, or relative to the base path)}';

    protected $description = 'Merge every documentation page into a single ordered markdown file with shifted headings and a table of contents';

    public function __construct(private DocumentationMergeService $merger)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $docsPath = $this->option('path')
            ? base_path($this->option('path'))
            : base_path(config('oi-laravel-documentation.docs_path', 'resources/markdown/docs'));

        if (! File::isDirectory($docsPath)) {
            $this->error("Documentation directory not found: {$docsPath}");
            $this->line('Run `php artisan doc:install` first.');

            return self::FAILURE;
        }

        $this->info("Merging documentation from {$docsPath}…");

        $result = $this->merger->merge($docsPath);

        $outputPath = $this->resolveOutputPath($result['slug']);

        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $result['markdown']);

        $this->info("✅ Merged {$result['documentCount']} document(s) into a single file.");
        $this->line("📄 {$result['title']}");
        $this->line("📁 Output: {$outputPath}");

        return self::SUCCESS;
    }

    private function resolveOutputPath(string $slug): string
    {
        $output = $this->option('output');

        if (! $output) {
            $directory = config('oi-laravel-documentation.merge_output_directory', 'storage/app/private/docs');

            return base_path($directory.'/'.$slug.'.md');
        }

        return str_starts_with($output, '/') ? $output : base_path($output);
    }
}
