<?php

declare(strict_types=1);

namespace pxlrbt\LaravelDocs;

use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DocsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-docs')
            ->hasConfigFile('docs')
            ->hasViews('docs')
            ->hasAssets();
    }

    public function packageBooted(): void
    {
        /** @var array<string, array<string, mixed>> $instances */
        $instances = config('docs', []);

        foreach ($instances as $key => $instanceConfig) {
            $instance = DocsInstance::fromConfig((string) $key, $instanceConfig);

            $middleware = ['web'];
            if ($instance->requiresAuth) {
                $middleware = array_merge($middleware, $instance->authMiddleware);
            }

            Route::middleware($middleware)
                ->group(function () use ($instance): void {
                    $route = Route::get("/{$instance->urlPrefix}/{slug?}", [DocsController::class, 'show'])
                        ->name($instance->routeName())
                        ->where('slug', '[a-z0-9\-]+');

                    $route->setAction(array_merge($route->getAction(), ['docsInstance' => $instance->key]));
                });
        }
    }
}
