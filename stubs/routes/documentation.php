<?php

use Illuminate\Support\Facades\Route;
use OiLab\LaravelDocumentation\Http\Controllers\DocumentationController;

$prefix = config('oi-laravel-documentation.route.prefix', 'documentation');
$middleware = config('oi-laravel-documentation.route.middleware', ['web']);

Route::middleware($middleware)->prefix($prefix)->group(function () {
    Route::get('/', [DocumentationController::class, 'index'])
        ->name('documentation.index');

    Route::get('/search', [DocumentationController::class, 'search'])
        ->name('documentation.search');

    Route::get('/{slug}', [DocumentationController::class, 'show'])
        ->where('slug', '.+')
        ->name('documentation.show');
});
