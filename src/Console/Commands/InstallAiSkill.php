<?php

namespace OiLab\LaravelDocumentation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallAiSkill extends Command
{
    protected $signature = 'doc:install-ai-skill
        {--global : Install into the Claude Code user profile (~/.claude) instead of the project}
        {--project : Install into the current project (.claude and .junie)}';

    protected $description = '[Deprecated] Use `oi:skills` instead. Install the oilab-laravel-docs AI assistant skill for Claude Code';

    private const SKILL_NAME = 'oilab-laravel-docs';

    public function handle(): int
    {
        $this->warn('`'.$this->getName().'` is deprecated. Use `php artisan oi:skills` (from oi-lab/oi-laravel-development) instead.');

        $scope = $this->resolveScope();

        if ($this->getApplication()->has('oi:skills')) {
            return $this->call('oi:skills', [
                'skills' => [self::SKILL_NAME],
                '--'.$scope => true,
            ]);
        }

        if ($scope === 'global') {
            if (! $this->installGlobally()) {
                return self::FAILURE;
            }
        } else {
            $this->installInProject();
        }

        $this->newLine();
        $this->info('✅ AI skill installed. Restart Claude Code or run /doctor if it does not appear.');

        return self::SUCCESS;
    }

    private function resolveScope(): string
    {
        if ($this->option('global')) {
            return 'global';
        }

        if ($this->option('project')) {
            return 'project';
        }

        $choice = $this->choice(
            'Where should the oilab-laravel-docs skill be installed?',
            [
                'project' => 'This project (.claude/skills and .junie/skills)',
                'global' => 'Claude Code user profile (~/.claude/skills — available in all projects)',
            ],
            'project'
        );

        return $choice === 'global' ? 'global' : 'project';
    }

    private function installInProject(): void
    {
        $skillDirs = [
            '.claude/skills/'.self::SKILL_NAME,
            '.junie/skills/'.self::SKILL_NAME,
        ];

        foreach ($skillDirs as $dir) {
            $this->copySkill(base_path($dir));
            $this->info("Installed: {$dir}/");
        }

        $this->addRulesToClaudeMd(base_path('CLAUDE.md'));
    }

    private function installGlobally(): bool
    {
        $home = $this->homeDirectory();

        if ($home === null) {
            $this->error('Could not determine the home directory. Use --project instead.');

            return false;
        }

        $target = $home.'/.claude/skills/'.self::SKILL_NAME;
        $this->copySkill($target);
        $this->info("Installed: {$target}/");

        $this->addRulesToClaudeMd($home.'/.claude/CLAUDE.md');

        return true;
    }

    private function copySkill(string $target): void
    {
        $source = __DIR__.'/../../../resources/skills/'.self::SKILL_NAME;

        if (! File::isDirectory($target)) {
            File::makeDirectory($target, 0755, true);
        }

        File::copyDirectory($source, $target);
    }

    private function addRulesToClaudeMd(string $claudeMdPath): void
    {
        $sectionHeader = '=== oi-lab/oi-laravel-documentation rules ===';
        $body = File::get(__DIR__.'/../../../resources/stubs/claude-rules.md');
        $newSection = $sectionHeader."\n\n".trim($body)."\n";

        if (! File::exists($claudeMdPath)) {
            File::ensureDirectoryExists(dirname($claudeMdPath));
            File::put($claudeMdPath, $newSection."\n");
            $this->info('Created '.basename($claudeMdPath).' with oi-laravel-documentation rules.');

            return;
        }

        $content = File::get($claudeMdPath);

        if (! str_contains($content, $sectionHeader)) {
            $separator = str_ends_with($content, "\n") ? "\n" : "\n\n";
            File::put($claudeMdPath, $content.$separator.$newSection."\n");
            $this->info('Added oi-laravel-documentation rules section to '.basename($claudeMdPath).'.');

            return;
        }

        $escaped = preg_quote($sectionHeader, '#');
        $updated = preg_replace(
            '#'.$escaped.'.*?(?=\n===|\z)#s',
            $newSection,
            $content
        );

        File::put($claudeMdPath, $updated);
        $this->info('Updated oi-laravel-documentation rules section in '.basename($claudeMdPath).'.');
    }

    private function homeDirectory(): ?string
    {
        $home = getenv('HOME') ?: ($_SERVER['HOME'] ?? null);

        if ($home === null && isset($_SERVER['HOMEDRIVE'], $_SERVER['HOMEPATH'])) {
            $home = $_SERVER['HOMEDRIVE'].$_SERVER['HOMEPATH'];
        }

        return $home ? rtrim($home, '/\\') : null;
    }
}
