<?php

use Illuminate\Support\Facades\File;
use OiLab\LaravelDocumentation\Console\Commands\InstallDocumentation;

beforeEach(function () {
    $this->base = sys_get_temp_dir().'/oi-docs-install-'.uniqid();
    File::ensureDirectoryExists($this->base);
    app()->setBasePath($this->base);

    // Pre-seed everything except the "components" step so the wizard only
    // prompts to proceed, then publishes the React components — this avoids
    // the "config"/"routes" steps, whose `vendor:publish` target path is
    // fixed at the application's original (pre-test) base path by Testbench.
    File::ensureDirectoryExists($this->base.'/config');
    File::copy(
        __DIR__.'/../../config/oi-laravel-documentation.php',
        $this->base.'/config/oi-laravel-documentation.php'
    );
    File::ensureDirectoryExists($this->base.'/resources/markdown/docs');
    File::ensureDirectoryExists($this->base.'/routes');
    File::put($this->base.'/routes/documentation.php', '<?php');
    File::ensureDirectoryExists($this->base.'/resources/js/components/ui');
    File::put($this->base.'/resources/js/components/ui/sonner.tsx', 'export default function Sonner() {}');
});

afterEach(function () {
    File::deleteDirectory($this->base);
});

function setTypesetConfig(string $base, bool $typeset): void
{
    $path = $base.'/config/oi-laravel-documentation.php';
    $contents = File::get($path);

    $contents = preg_replace('/\'typeset\' => (?:true|false),/', "'typeset' => ".($typeset ? 'true' : 'false').',', $contents, 1);

    File::put($path, $contents);

    config()->set('oi-laravel-documentation.rendering.typeset', $typeset);
}

it('always publishes both content components and lets show.tsx switch on document.html', function () {
    setTypesetConfig($this->base, false);

    $this->artisan('doc:install')
        ->expectsConfirmation('Do you want to proceed?', 'yes')
        ->assertSuccessful();

    $componentsRoot = $this->base.'/resources/js/components/documentation';
    $pagesRoot = $this->base.'/resources/js/pages/documentation';

    expect(File::exists($componentsRoot.'/documentation-markdown-content.tsx'))->toBeTrue()
        ->and(File::exists($componentsRoot.'/documentation-html-content.tsx'))->toBeTrue();

    $show = File::get($pagesRoot.'/show.tsx');
    expect($show)->toContain('DocumentationMarkdownContent')
        ->and($show)->toContain('DocumentationHtmlContent')
        ->and($show)->toContain('document.html !== undefined')
        ->and($show)->toContain('Show.layout = ')
        ->and($show)->not->toContain('DocumentationLayout');

    $index = File::get($pagesRoot.'/index.tsx');
    expect($index)->toContain('Index.layout = ')
        ->and($index)->toContain("import dashboard from '@/routes/dashboard';");
});

it('sets the shared typography constant to "typography" by default', function () {
    setTypesetConfig($this->base, false);

    $this->artisan('doc:install')
        ->expectsConfirmation('Do you want to proceed?', 'yes')
        ->assertSuccessful();

    $lib = $this->base.'/resources/js/lib/documentation-typography.ts';

    expect(File::get($lib))->toContain("DOCUMENTATION_TYPOGRAPHY_CLASS = 'typography'");

    $markdownContent = File::get($this->base.'/resources/js/components/documentation/documentation-markdown-content.tsx');
    $htmlContent = File::get($this->base.'/resources/js/components/documentation/documentation-html-content.tsx');

    expect($markdownContent)->toContain('DOCUMENTATION_TYPOGRAPHY_CLASS')
        ->and($htmlContent)->toContain('DOCUMENTATION_TYPOGRAPHY_CLASS');
});

it('sets the shared typography constant to "typeset" when configured', function () {
    setTypesetConfig($this->base, true);

    $this->artisan('doc:install')
        ->expectsConfirmation('Do you want to proceed?', 'yes')
        ->assertSuccessful();

    $lib = $this->base.'/resources/js/lib/documentation-typography.ts';

    expect(File::get($lib))->toContain("DOCUMENTATION_TYPOGRAPHY_CLASS = 'typeset'");
});

it('persists rendering settings into the config file via updateConfigValue', function () {
    $command = app(InstallDocumentation::class);
    $configPath = $this->base.'/config/oi-laravel-documentation.php';

    $method = new ReflectionMethod($command, 'updateConfigValue');
    $method->setAccessible(true);
    $method->invoke($command, $configPath, 'markdown_engine', "'markdown_engine' => 'server',");
    $method->invoke($command, $configPath, 'ssr', "'ssr' => true,");
    $method->invoke($command, $configPath, 'typeset', "'typeset' => true,");

    $contents = File::get($configPath);

    expect($contents)->toContain("'markdown_engine' => 'server',")
        ->and($contents)->toContain("'ssr' => true,")
        ->and($contents)->toContain("'typeset' => true,");
});
