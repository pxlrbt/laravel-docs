<?php

declare(strict_types=1);

namespace pxlrbt\LaravelDocs\Tests;

use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as BaseTestCase;
use pxlrbt\LaravelDocs\DocsServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            DocsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('view.paths', [__DIR__.'/fixtures/views']);

        $app['config']->set('docs.default', [
            'root' => __DIR__.'/fixtures/docs',
            'url_prefix' => 'docs',
            'layout' => null,
            'requires_auth' => false,
            'auth_middleware' => ['auth'],
            'can_access_docs' => null,
            'has_navigation' => true,
            'has_sidebar' => true,
            'has_pagination' => true,
        ]);
    }

    protected function defineRoutes($router): void
    {
        Route::get('/login', fn () => 'login')->name('login');
    }
}
