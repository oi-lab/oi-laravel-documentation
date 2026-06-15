<?php

namespace OiLab\LaravelDocumentation\Tests;

use Illuminate\Foundation\Application;
use OiLab\LaravelDocumentation\OiLaravelDocumentationServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            OiLaravelDocumentationServiceProvider::class,
        ];
    }
}
