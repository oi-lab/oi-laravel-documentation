<?php

/**
 * Sync the canonical skill in resources/skills/oilab-laravel-docs to the
 * package's own AI assistant skill directories (for dogfooding).
 * Run via: composer sync-ai-skills
 */
$root = dirname(__DIR__);
$source = $root.'/resources/skills/oilab-laravel-docs';

$targets = [
    $root.'/.claude/skills/oilab-laravel-docs',
    $root.'/.junie/skills/oilab-laravel-docs',
];

if (! is_dir($source)) {
    fwrite(STDERR, "Skill source not found: {$source}".PHP_EOL);
    exit(1);
}

function copy_dir(string $source, string $target): void
{
    if (! is_dir($target)) {
        mkdir($target, 0755, true);
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($items as $item) {
        $destination = $target.DIRECTORY_SEPARATOR.$items->getSubPathName();

        if ($item->isDir()) {
            if (! is_dir($destination)) {
                mkdir($destination, 0755, true);
            }
        } else {
            copy($item->getPathname(), $destination);
        }
    }
}

foreach ($targets as $target) {
    copy_dir($source, $target);
    echo 'Synced: '.str_replace($root.'/', '', $target).'/'.PHP_EOL;
}
