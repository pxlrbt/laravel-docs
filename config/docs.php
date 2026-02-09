<?php

declare(strict_types=1);

return [
    'default' => [
        'root' => env('DOCS_ROOT', 'docs'),
        'url_prefix' => env('DOCS_URL_PREFIX', 'docs'),
        'layout' => env('DOCS_LAYOUT', 'layouts.app'),
        'requires_auth' => env('DOCS_REQUIRES_AUTH', false),
        'auth_middleware' => ['auth'],
        'can_access_docs' => null,
        'has_navigation' => env('DOCS_HAS_NAVIGATION', true),
        'has_sidebar' => env('DOCS_HAS_SIDEBAR', true),
        'has_pagination' => env('DOCS_HAS_PAGINATION', true),
    ],
];
