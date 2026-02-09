<?php

declare(strict_types=1);

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use pxlrbt\LaravelDocs\DocsController;

beforeEach(function () {
    Cache::forget('docs_sidebar_default');
});

it('renders the docs index page', function () {
    $response = $this->get('/docs');

    $response->assertOk();
    $response->assertSee('Welcome');
});

it('renders a specific docs page by slug', function () {
    $response = $this->get('/docs/getting-started');

    $response->assertOk();
    $response->assertSee('Getting Started');
});

it('returns 404 for a missing docs page', function () {
    $response = $this->get('/docs/nonexistent');

    $response->assertNotFound();
});

it('blocks path traversal attempts', function () {
    $response = $this->get('/docs/../.env');

    $response->assertNotFound();
});

it('displays sidebar groups', function () {
    config(['docs.default.has_navigation' => true]);

    $response = $this->get('/docs');

    $response->assertOk();
    $response->assertSee('General');
    $response->assertSee('First Steps');
    $response->assertSee('Usage');
});

it('displays pagination links', function () {
    $response = $this->get('/docs');

    $response->assertOk();
    $response->assertSee('docs-pagination', false);
    $response->assertSee('&rarr;', false);
});

it('displays mobile select navigation', function () {
    config(['docs.default.has_navigation' => true]);

    $response = $this->get('/docs');

    $response->assertOk();
    $response->assertSee('docs-mobile-nav', false);
    $response->assertSee('<select', false);
    $response->assertSee('<optgroup', false);
});

it('renders standalone HTML when layout is null', function () {
    config(['docs.default.layout' => null]);

    $response = $this->get('/docs');

    $response->assertOk();
    $response->assertSee('<!DOCTYPE html>', false);
    $response->assertSee('class="docs-standalone"', false);
});

it('does not render standalone HTML when layout is set', function () {
    config(['docs.default.layout' => 'layouts.page']);

    $response = $this->get('/docs');

    $response->assertOk();
    $response->assertDontSee('class="docs-standalone"', false);
});

it('redirects unauthenticated users when auth is required', function () {
    Route::middleware(['web', 'auth'])
        ->group(function (): void {
            $route = Route::get('/docs-auth/{slug?}', [DocsController::class, 'show'])
                ->name('docs.default.auth.show')
                ->where('slug', '[a-z0-9\-]+');
            $route->setAction(array_merge($route->getAction(), ['docsInstance' => 'default']));
        });

    $response = $this->get('/docs-auth');

    $response->assertRedirect(route('login'));
});

it('allows authenticated users when auth is required', function () {
    config(['docs.default.layout' => null]);

    Route::middleware(['web', 'auth'])
        ->group(function (): void {
            $route = Route::get('/docs-auth2/{slug?}', [DocsController::class, 'show'])
                ->name('docs.default.auth2.show')
                ->where('slug', '[a-z0-9\-]+');
            $route->setAction(array_merge($route->getAction(), ['docsInstance' => 'default']));
        });

    $user = (new User)->forceFill(['id' => 1, 'email' => 'test@example.com']);

    $response = $this->actingAs($user)->get('/docs-auth2');

    $response->assertOk();
    $response->assertSee('Welcome');
});

it('returns 403 when can_access_docs callback denies access', function () {
    config(['docs.default.can_access_docs' => fn () => false]);

    $response = $this->get('/docs');

    $response->assertForbidden();
});

it('hides navigation when has_navigation is false', function () {
    config(['docs.default.has_navigation' => false]);

    $response = $this->get('/docs');

    $response->assertOk();
    $response->assertDontSee('docs-sidebar', false);
    $response->assertDontSee('docs-mobile-nav', false);
});

it('adds no-nav grid modifier when navigation is disabled', function () {
    config(['docs.default.has_navigation' => false]);

    $response = $this->get('/docs');

    $response->assertOk();
    $response->assertSee('docs-layout--no-nav', false);
});

it('adds no-toc grid modifier when sidebar is disabled', function () {
    config(['docs.default.has_sidebar' => false]);

    $response = $this->get('/docs');

    $response->assertOk();
    $response->assertSee('docs-layout--no-toc', false);
});

it('hides toc sidebar when has_sidebar is false', function () {
    config(['docs.default.has_sidebar' => false]);

    $response = $this->get('/docs');

    $response->assertOk();
    $response->assertDontSee('docs-toc-aside', false);
});

it('hides pagination when has_pagination is false', function () {
    config(['docs.default.has_pagination' => false]);

    $response = $this->get('/docs');

    $response->assertOk();
    $response->assertDontSee('docs-pagination', false);
});
