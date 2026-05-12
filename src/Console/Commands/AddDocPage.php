<?php

namespace OiLab\LaravelDocumentation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AddDocPage extends Command
{
    private const CREATE_NEW_FOLDER = '➕ Create a new folder…';

    private const ROOT_LABEL = 'Documentation root';

    protected $signature = 'doc:add-page {--regenerate : Regenerate the navigation and search index after creating the page}';

    protected $description = 'Create a new documentation page in a chosen (or new) folder';

    public function handle(): int
    {
        $docsPath = base_path(config('oi-laravel-documentation.docs_path', 'resources/markdown/docs'));

        if (! File::isDirectory($docsPath)) {
            $this->error("Documentation directory not found: {$docsPath}");
            $this->line('Run `php artisan doc:install` first.');

            return self::FAILURE;
        }

        $folder = $this->selectFolder($docsPath);

        if ($folder === null) {
            return self::FAILURE;
        }

        $title = trim((string) $this->ask('Page title'));

        if ($title === '') {
            $this->error('A page title is required.');

            return self::FAILURE;
        }

        $description = trim((string) $this->ask('Page description (optional)', ''));

        $defaultFilename = Str::slug($title) ?: 'page';
        $filename = Str::slug((string) $this->ask('File name (without .md extension)', $defaultFilename)) ?: $defaultFilename;

        $targetDir = $folder === '' ? $docsPath : $docsPath.'/'.$folder;
        $targetPath = $targetDir.'/'.$filename.'.md';

        if (File::exists($targetPath)) {
            $this->error('A page already exists at: '.$this->relativePath($targetPath));

            return self::FAILURE;
        }

        $orderInput = $this->ask('Order (optional, leave blank for default)');
        $order = is_numeric($orderInput) ? (int) $orderInput : null;

        File::ensureDirectoryExists($targetDir);
        File::put($targetPath, $this->buildPageContents($title, $description, $folder, $order));

        $this->info('✓ Created page: '.$this->relativePath($targetPath));

        if ($this->option('regenerate') || $this->confirm('Regenerate navigation and search index now?', true)) {
            $this->newLine();
            $this->call('doc:gen-nav');
            $this->call('doc:gen-index');
        } else {
            $this->newLine();
            $this->line('Run `php artisan doc:gen-nav` then `php artisan doc:gen-index` to update the navigation.');
        }

        return self::SUCCESS;
    }

    private function selectFolder(string $docsPath): ?string
    {
        $folders = $this->discoverFolders($docsPath);

        $choices = [self::ROOT_LABEL];
        foreach ($folders as $relative) {
            $choices[] = $relative;
        }
        $choices[] = self::CREATE_NEW_FOLDER;

        $choice = $this->choice('In which folder should the new page be created?', $choices, self::ROOT_LABEL);

        return match ($choice) {
            self::ROOT_LABEL => '',
            self::CREATE_NEW_FOLDER => $this->createFolder($docsPath, $folders),
            default => $choice,
        };
    }

    /**
     * @return list<string>
     */
    private function discoverFolders(string $docsPath): array
    {
        $folders = [];

        foreach (File::directories($docsPath) as $directory) {
            $folders[] = basename($directory);

            foreach (File::directories($directory) as $subdirectory) {
                $folders[] = basename($directory).'/'.basename($subdirectory);
            }
        }

        sort($folders);

        return $folders;
    }

    /**
     * @param  list<string>  $folders
     */
    private function createFolder(string $docsPath, array $folders): ?string
    {
        $topLevelFolders = array_values(array_filter($folders, fn (string $folder): bool => ! str_contains($folder, '/')));

        $parent = '';

        if ($topLevelFolders !== []) {
            $choice = $this->choice(
                'Where should the new folder be created?',
                [self::ROOT_LABEL, ...$topLevelFolders],
                self::ROOT_LABEL
            );

            $parent = $choice === self::ROOT_LABEL ? '' : $choice;
        }

        $slug = Str::slug((string) $this->ask('New folder name'));

        if ($slug === '') {
            $this->error('A folder name is required.');

            return null;
        }

        $relativeFolder = $parent === '' ? $slug : $parent.'/'.$slug;
        $folderPath = $docsPath.'/'.$relativeFolder;

        if (File::isDirectory($folderPath)) {
            $this->warn('Folder already exists, using it: '.$relativeFolder);

            return $relativeFolder;
        }

        $title = trim((string) $this->ask('Folder title', Str::headline($slug)));
        $folderDescription = trim((string) $this->ask('Folder description (optional)', ''));

        File::ensureDirectoryExists($folderPath);

        $meta = ['title' => $title !== '' ? $title : Str::headline($slug)];

        if ($folderDescription !== '') {
            $meta['description'] = $folderDescription;
        }

        $meta['order'] = $this->nextFolderOrder($parent === '' ? $docsPath : $docsPath.'/'.$parent);

        if ($parent === '') {
            $meta['type'] = 'section';
        }

        File::put(
            $folderPath.'/meta.json',
            json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        $this->info('✓ Created folder: '.$this->relativePath($folderPath));

        return $relativeFolder;
    }

    private function nextFolderOrder(string $parentPath): int
    {
        $maxOrder = 0;

        foreach (File::directories($parentPath) as $directory) {
            $metaPath = $directory.'/meta.json';

            if (! File::exists($metaPath)) {
                continue;
            }

            $meta = json_decode(File::get($metaPath), true);

            if (is_array($meta) && isset($meta['order']) && is_numeric($meta['order'])) {
                $maxOrder = max($maxOrder, (int) $meta['order']);
            }
        }

        return $maxOrder + 1;
    }

    private function buildPageContents(string $title, string $description, string $folder, ?int $order): string
    {
        $frontmatter = ['title' => $this->yamlValue($title)];

        if ($description !== '') {
            $frontmatter['description'] = $this->yamlValue($description);
        }

        if ($folder !== '') {
            $frontmatter['section'] = $this->yamlValue(basename($folder));
        }

        if ($order !== null) {
            $frontmatter['order'] = (string) $order;
        }

        $lines = ['---'];

        foreach ($frontmatter as $key => $value) {
            $lines[] = "{$key}: {$value}";
        }

        $lines[] = '---';
        $lines[] = '';
        $lines[] = "# {$title}";
        $lines[] = '';
        $lines[] = $description !== '' ? $description : 'Write your content here.';
        $lines[] = '';

        return implode("\n", $lines);
    }

    private function yamlValue(string $value): string
    {
        if ($value === trim($value) && ! preg_match('/[:#\-?{}\[\]&*!|>\'"%@`,]/', $value)) {
            return $value;
        }

        return "'".str_replace("'", "''", $value)."'";
    }

    private function relativePath(string $path): string
    {
        return ltrim(str_replace(base_path(), '', $path), '/\\');
    }
}
