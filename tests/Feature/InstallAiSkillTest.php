<?php

beforeEach(function () {
    $this->base = sys_get_temp_dir().'/oi-docs-skill-'.uniqid();
    mkdir($this->base, 0755, true);
    app()->setBasePath($this->base);
});

afterEach(function () {
    if (! is_dir($this->base)) {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($this->base, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }

    rmdir($this->base);
});

it('installs the multi-file skill for Claude and Junie in the project', function () {
    $this->artisan('doc:install-ai-skill', ['--project' => true])->assertSuccessful();

    $claudeSkill = $this->base.'/.claude/skills/oilab-laravel-docs';
    $junieSkill = $this->base.'/.junie/skills/oilab-laravel-docs';

    expect(file_exists($claudeSkill.'/SKILL.md'))->toBeTrue()
        ->and(file_exists($claudeSkill.'/references/structure.md'))->toBeTrue()
        ->and(file_exists($claudeSkill.'/references/frontmatter.md'))->toBeTrue()
        ->and(file_exists($claudeSkill.'/assets/page-template.md'))->toBeTrue()
        ->and(file_exists($junieSkill.'/SKILL.md'))->toBeTrue()
        ->and(file_get_contents($claudeSkill.'/SKILL.md'))->toContain('Authoring OI Laravel Documentation');
});

it('creates a CLAUDE.md with the rules section when none exists', function () {
    $this->artisan('doc:install-ai-skill', ['--project' => true])->assertSuccessful();

    $claude = file_get_contents($this->base.'/CLAUDE.md');

    expect($claude)->toContain('=== oi-lab/oi-laravel-documentation rules ===')
        ->and($claude)->toContain('Activate `oilab-laravel-docs`');
});

it('appends the rules section to an existing CLAUDE.md without losing content', function () {
    file_put_contents($this->base.'/CLAUDE.md', "# Existing project rules\n");

    $this->artisan('doc:install-ai-skill', ['--project' => true])->assertSuccessful();

    $claude = file_get_contents($this->base.'/CLAUDE.md');

    expect($claude)->toContain('# Existing project rules')
        ->and($claude)->toContain('=== oi-lab/oi-laravel-documentation rules ===');
});

it('does not duplicate the rules section when run twice', function () {
    $this->artisan('doc:install-ai-skill', ['--project' => true])->assertSuccessful();
    $this->artisan('doc:install-ai-skill', ['--project' => true])->assertSuccessful();

    $claude = file_get_contents($this->base.'/CLAUDE.md');

    expect(substr_count($claude, '=== oi-lab/oi-laravel-documentation rules ==='))->toBe(1);
});

it('installs the skill into the user profile with --global', function () {
    $originalHome = getenv('HOME');
    $fakeHome = $this->base.'/home';
    mkdir($fakeHome, 0755, true);
    putenv('HOME='.$fakeHome);
    $_SERVER['HOME'] = $fakeHome;

    $this->artisan('doc:install-ai-skill', ['--global' => true])->assertSuccessful();

    expect(file_exists($fakeHome.'/.claude/skills/oilab-laravel-docs/SKILL.md'))->toBeTrue()
        ->and(file_get_contents($fakeHome.'/.claude/CLAUDE.md'))
        ->toContain('=== oi-lab/oi-laravel-documentation rules ===');

    $originalHome === false ? putenv('HOME') : putenv('HOME='.$originalHome);
    $_SERVER['HOME'] = $originalHome ?: '';
});
